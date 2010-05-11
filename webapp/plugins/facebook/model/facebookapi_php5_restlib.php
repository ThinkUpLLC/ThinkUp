<?php
// Copyright 2004-2009 Facebook. All Rights Reserved.
//
// +---------------------------------------------------------------------------+
// | Facebook Platform PHP5 client                                             |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007-2009 Facebook, Inc.                                    |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | 1. Redistributions of source code must retain the above copyright         |
// |    notice, this list of conditions and the following disclaimer.          |
// | 2. Redistributions in binary form must reproduce the above copyright      |
// |    notice, this list of conditions and the following disclaimer in the    |
// |    documentation and/or other materials provided with the distribution.   |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR      |
// | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES |
// | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.   |
// | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT  |
// | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF  |
// | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.         |
// +---------------------------------------------------------------------------+
// | For help with this library, contact developers-help@facebook.com          |
// +---------------------------------------------------------------------------+
//

include_once 'jsonwrapper/jsonwrapper.php';

class FacebookRestClient {
  public $secret;
  public $session_key;
  public $api_key;
  // to save making the friends.get api call, this will get prepopulated on
  // canvas pages
  public $friends_list;
  public $user;
  // to save making the pages.isAppAdded api call, this will get prepopulated
  // on canvas pages
  public $added;
  public $is_user;
  // we don't pass friends list to iframes, but we want to make
  // friends_get really simple in the canvas_user (non-logged in) case.
  // So we use the canvas_user as default arg to friends_get
  public $canvas_user;
  public $batch_mode;
  private $batch_queue;
  private $pending_batch;
  private $pending_batch_is_read_only;
  private $call_as_apikey;
  private $use_curl_if_available;
  private $format = null;
  private $using_session_secret = false;
  private $rawData = null;

  const BATCH_MODE_DEFAULT = 0;
  const BATCH_MODE_SERVER_PARALLEL = 0;
  const BATCH_MODE_SERIAL_ONLY = 2;

  /**
   * Create the client.
   * @param string $session_key if you haven't gotten a session key yet, leave
   *                            this as null and then set it later by just
   *                            directly accessing the $session_key member
   *                            variable.
   */
  public function __construct($api_key, $secret, $session_key=null) {
    $this->secret       = $secret;
    $this->session_key  = $session_key;
    $this->api_key      = $api_key;
    $this->batch_mode = FacebookRestClient::BATCH_MODE_DEFAULT;
    $this->last_call_id = 0;
    $this->call_as_apikey = '';
    $this->use_curl_if_available = true;
    $this->server_addr =
      Facebook::get_facebook_url('api') . '/restserver.php';
    $this->photo_server_addr =
      Facebook::get_facebook_url('api-photo') . '/restserver.php';
    $this->read_server_addr =
      Facebook::get_facebook_url('api-read') . '/restserver.php';

    if (!empty($GLOBALS['facebook_config']['debug'])) {
      $this->cur_id = 0;
      ?>
<script type="text/javascript">
var types = ['params', 'xml', 'php', 'sxml'];
function getStyle(elem, style) {
  if (elem.getStyle) {
    return elem.getStyle(style);
  } else {
    return elem.style[style];
  }
}
function setStyle(elem, style, value) {
  if (elem.setStyle) {
    elem.setStyle(style, value);
  } else {
    elem.style[style] = value;
  }
}
function toggleDisplay(id, type) {
  for (var i = 0; i < types.length; i++) {
    var t = types[i];
    var pre = document.getElementById(t + id);
    if (pre) {
      if (t != type || getStyle(pre, 'display') == 'block') {
        setStyle(pre, 'display', 'none');
      } else {
        setStyle(pre, 'display', 'block');
      }
    }
  }
  return false;
}
</script>
<?php
    }
  }

  /**
   * Set the default user id for methods that allow the caller
   * to pass an uid parameter to identify the target user
   * instead of a session key. This currently applies to
   * the user preferences methods.
   *
   * @param $uid int the user id
   */
  public function set_user($uid) {
    $this->user = $uid;
  }


  /**
   * Switch to use the session secret instead of the app secret,
   * for desktop and unsecured environment
   */
  public function use_session_secret($session_secret) {
    $this->secret = $session_secret;
    $this->using_session_secret = true;
  }

  /**
   * Normally, if the cURL library/PHP extension is available, it is used for
   * HTTP transactions.  This allows that behavior to be overridden, falling
   * back to a vanilla-PHP implementation even if cURL is installed.
   *
   * @param $use_curl_if_available bool whether or not to use cURL if available
   */
  public function set_use_curl_if_available($use_curl_if_available) {
    $this->use_curl_if_available = $use_curl_if_available;
  }

  /**
   * Start a batch operation.
   */
  public function begin_batch() {
    if ($this->pending_batch()) {
      $code = FacebookAPIErrorCodes::API_EC_BATCH_ALREADY_STARTED;
      $description = FacebookAPIErrorCodes::$api_error_descriptions[$code];
      throw new FacebookRestClientException($description, $code);
    }

    $this->batch_queue = array();
    $this->pending_batch = true;
    $this->pending_batch_is_read_only = true;
  }

  /*
   * End current batch operation
   */
  public function end_batch() {
    if (!$this->pending_batch()) {
      $code = FacebookAPIErrorCodes::API_EC_BATCH_NOT_STARTED;
      $description = FacebookAPIErrorCodes::$api_error_descriptions[$code];
      throw new FacebookRestClientException($description, $code);
    }

    $read_only = $this->pending_batch_is_read_only;
    $this->pending_batch = false;
    $this->pending_batch_is_read_only = false;

    $this->execute_server_side_batch($read_only);
    $this->batch_queue = null;
  }

  /**
   * are we currently queueing up calls for a batch?
   */
  public function pending_batch() {
    return $this->pending_batch;
  }

  private function execute_server_side_batch($read_only) {
    $item_count = count($this->batch_queue);
    $method_feed = array();
    foreach ($this->batch_queue as $batch_item) {
      $method = $batch_item['m'];
      $params = $batch_item['p'];
      list($get, $post) = $this->finalize_params($method, $params);
      $method_feed[] = $this->create_url_string(array_merge($post, $get));
    }

    $serial_only =
      ($this->batch_mode == FacebookRestClient::BATCH_MODE_SERIAL_ONLY);

    $params = array('method_feed' => json_encode($method_feed),
                    'serial_only' => $serial_only,
                    'format' => $this->format);
    $result = $this->call_method('facebook.batch.run', $params, $read_only);

    if (is_array($result) && isset($result['error_code'])) {
      throw new FacebookRestClientException($result['error_msg'],
                                            $result['error_code']);
    }

    for ($i = 0; $i < $item_count; $i++) {
      $batch_item = $this->batch_queue[$i];
      $batch_item['p']['format'] = $this->format;
      $batch_item_result = $this->convert_result($result[$i],
                                                 $batch_item['m'],
                                                 $batch_item['p']);

      if (is_array($batch_item_result) &&
          isset($batch_item_result['error_code'])) {
        throw new FacebookRestClientException($batch_item_result['error_msg'],
                                              $batch_item_result['error_code']);
      }
      $batch_item['r'] = $batch_item_result;
    }
  }

  public function begin_permissions_mode($permissions_apikey) {
    $this->call_as_apikey = $permissions_apikey;
  }

  public function end_permissions_mode() {
    $this->call_as_apikey = '';
  }


  /*
   * If a page is loaded via HTTPS, then all images and static
   * resources need to be printed with HTTPS urls to avoid
   * mixed content warnings. If your page loads with an HTTPS
   * url, then call set_use_ssl_resources to retrieve the correct
   * urls.
   */
  public function set_use_ssl_resources($is_ssl = true) {
    $this->use_ssl_resources = $is_ssl;
  }

  /**
   * Returns public information for an application (as shown in the application
   * directory) by either application ID, API key, or canvas page name.
   *
   * @param int $application_id              (Optional) app id
   * @param string $application_api_key      (Optional) api key
   * @param string $application_canvas_name  (Optional) canvas name
   *
   * Exactly one argument must be specified, otherwise it is an error.
   *
   * @return array  An array of public information about the application.
   */
  public function application_getPublicInfo($application_id=null,
                                            $application_api_key=null,
                                            $application_canvas_name=null) {
    return $this->call_method('facebook.application.getPublicInfo',
        array('application_id' => $application_id,
              'application_api_key' => $application_api_key,
              'application_canvas_name' => $application_canvas_name));
  }

  /**
   * Creates an authentication token to be used as part of the desktop login
   * flow.  For more information, please see
   * http://wiki.developers.facebook.com/index.php/Auth.createToken.
   *
   * @return string  An authentication token.
   */
  public function auth_createToken() {
    return $this->call_method('facebook.auth.createToken');
  }

  /**
   * Returns the session information available after current user logs in.
   *
   * @param string $auth_token the token returned by auth_createToken or
   *               passed back to your callback_url.
   * @param bool $generate_session_secret whether the session returned should
   *             include a session secret
   * @param string $host_url the connect site URL for which the session is
   *               being generated.  This parameter is optional, unless
   *               you want Facebook to determine which of several base domains
   *               to choose from.  If this third argument isn't provided but
   *               there are several base domains, the first base domain is
   *               chosen.
   *
   * @return array  An assoc array containing session_key, uid
   */
  public function auth_getSession($auth_token,
                                  $generate_session_secret = false,
                                  $host_url = null) {
    if (!$this->pending_batch()) {
      $result = $this->call_method(
        'facebook.auth.getSession',
        array('auth_token' => $auth_token,
              'generate_session_secret' => $generate_session_secret,
              'host_url' => $host_url));
      $this->session_key = $result['session_key'];

      if (!empty($result['secret']) && !$generate_session_secret) {
        // desktop apps have a special secret
        $this->secret = $result['secret'];
      }

      return $result;
    }
  }

  /**
   * Generates a session-specific secret. This is for integration with
   * client-side API calls, such as the JS library.
   *
   * @return array  A session secret for the current promoted session
   *
   * @error API_EC_PARAM_SESSION_KEY
   *        API_EC_PARAM_UNKNOWN
   */
  public function auth_promoteSession() {
      return $this->call_method('facebook.auth.promoteSession');
  }

  /**
   * Expires the session that is currently being used.  If this call is
   * successful, no further calls to the API (which require a session) can be
   * made until a valid session is created.
   *
   * @return bool  true if session expiration was successful, false otherwise
   */
  public function auth_expireSession() {
      return $this->call_method('facebook.auth.expireSession');
  }

  /**
   *  Revokes the given extended permission that the user granted at some
   *  prior time (for instance, offline_access or email).  If no user is
   *  provided, it will be revoked for the user of the current session.
   *
   *  @param  string  $perm  The permission to revoke
   *  @param  int     $uid   The user for whom to revoke the permission.
   */
  public function auth_revokeExtendedPermission($perm, $uid=null) {
    return $this->call_method('facebook.auth.revokeExtendedPermission',
        array('perm' => $perm, 'uid' => $uid));
  }

  /**
   * Revokes the user's agreement to the Facebook Terms of Service for your
   * application.  If you call this method for one of your users, you will no
   * longer be able to make API requests on their behalf until they again
   * authorize your application.  Use with care.  Note that if this method is
   * called without a user parameter, then it will revoke access for the
   * current session's user.
   *
   * @param int $uid  (Optional) User to revoke
   *
   * @return bool  true if revocation succeeds, false otherwise
   */
  public function auth_revokeAuthorization($uid=null) {
      return $this->call_method('facebook.auth.revokeAuthorization',
          array('uid' => $uid));
  }

  /**
   * Get public key that is needed to verify digital signature
   * an app may pass to other apps. The public key is only used by
   * other apps for verification purposes.
   * @param  string  API key of an app
   * @return string  The public key for the app.
   */
  public function auth_getAppPublicKey($target_app_key) {
    return $this->call_method('facebook.auth.getAppPublicKey',
          array('target_app_key' => $target_app_key));
  }

  /**
   * Get a structure that can be passed to another app
   * as proof of session. The other app can verify it using public
   * key of this app.
   *
   * @return signed public session data structure.
   */
  public function auth_getSignedPublicSessionData() {
    return $this->call_method('facebook.auth.getSignedPublicSessionData',
                              array());
  }

  /**
   * Returns the number of unconnected friends that exist in this application.
   * This number is determined based on the accounts registered through
   * connect.registerUsers() (see below).
   */
  public function connect_getUnconnectedFriendsCount() {
    return $this->call_method('facebook.connect.getUnconnectedFriendsCount',
        array());
  }

 /**
  * This method is used to create an association between an external user
  * account and a Facebook user account, as per Facebook Connect.
  *
  * This method takes an array of account data, including a required email_hash
  * and optional account data. For each connected account, if the user exists,
  * the information is added to the set of the user's connected accounts.
  * If the user has already authorized the site, the connected account is added
  * in the confirmed state. If the user has not yet authorized the site, the
  * connected account is added in the pending state.
  *
  * This is designed to help Facebook Connect recognize when two Facebook
  * friends are both members of a external site, but perhaps are not aware of
  * it.  The Connect dialog (see fb:connect-form) is used when friends can be
  * identified through these email hashes. See the following url for details:
  *
  *   http://wiki.developers.facebook.com/index.php/Connect.registerUsers
  *
  * @param mixed $accounts A (JSON-encoded) array of arrays, where each array
  *                        has three properties:
  *                        'email_hash'  (req) - public email hash of account
  *                        'account_id'  (opt) - remote account id;
  *                        'account_url' (opt) - url to remote account;
  *
  * @return array  The list of email hashes for the successfully registered
  *                accounts.
  */
  public function connect_registerUsers($accounts) {
    return $this->call_method('facebook.connect.registerUsers',
        array('accounts' => $accounts));
  }

 /**
  * Unregisters a set of accounts registered using connect.registerUsers.
  *
  * @param array $email_hashes  The (JSON-encoded) list of email hashes to be
  *                             unregistered.
  *
  * @return array  The list of email hashes which have been successfully
  *                unregistered.
  */
  public function connect_unregisterUsers($email_hashes) {
    return $this->call_method('facebook.connect.unregisterUsers',
        array('email_hashes' => $email_hashes));
  }

  /**
   * Returns events according to the filters specified.
   *
   * @param int $uid            (Optional) User associated with events. A null
   *                            parameter will default to the session user.
   * @param array/string $eids  (Optional) Filter by these event
   *                            ids. A null parameter will get all events for
   *                            the user. (A csv list will work but is deprecated)
   * @param int $start_time     (Optional) Filter with this unix time as lower
   *                            bound.  A null or zero parameter indicates no
   *                            lower bound.
   * @param int $end_time       (Optional) Filter with this UTC as upper bound.
   *                            A null or zero parameter indicates no upper
   *                            bound.
   * @param string $rsvp_status (Optional) Only show events where the given uid
   *                            has this rsvp status.  This only works if you
   *                            have specified a value for $uid.  Values are as
   *                            in events.getMembers.  Null indicates to ignore
   *                            rsvp status when filtering.
   *
   * @return array  The events matching the query.
   */
  public function &events_get($uid=null,
                              $eids=null,
                              $start_time=null,
                              $end_time=null,
                              $rsvp_status=null) {
    return $this->call_method('facebook.events.get',
        array('uid' => $uid,
              'eids' => $eids,
              'start_time' => $start_time,
              'end_time' => $end_time,
              'rsvp_status' => $rsvp_status));
  }

  /**
   * Returns membership list data associated with an event.
   *
   * @param int $eid  event id
   *
   * @return array  An assoc array of four membership lists, with keys
   *                'attending', 'unsure', 'declined', and 'not_replied'
   */
  public function &events_getMembers($eid) {
    return $this->call_method('facebook.events.getMembers',
      array('eid' => $eid));
  }

  /**
   * RSVPs the current user to this event.
   *
   * @param int $eid             event id
   * @param string $rsvp_status  'attending', 'unsure', or 'declined'
   *
   * @return bool  true if successful
   */
  public function &events_rsvp($eid, $rsvp_status) {
    return $this->call_method('facebook.events.rsvp',
        array(
        'eid' => $eid,
        'rsvp_status' => $rsvp_status));
  }

  /**
   * Cancels an event. Only works for events where application is the admin.
   *
   * @param int $eid                event id
   * @param string $cancel_message  (Optional) message to send to members of
   *                                the event about why it is cancelled
   *
   * @return bool  true if successful
   */
  public function &events_cancel($eid, $cancel_message='') {
    return $this->call_method('facebook.events.cancel',
        array('eid' => $eid,
              'cancel_message' => $cancel_message));
  }

  /**
   * Creates an event on behalf of the user is there is a session, otherwise on
   * behalf of app.  Successful creation guarantees app will be admin.
   *
   * @param assoc array $event_info  json encoded event information
   * @param string $file             (Optional) filename of picture to set
   *
   * @return int  event id
   */
  public function events_create($event_info, $file = null) {
    if ($file) {
      return $this->call_upload_method('facebook.events.create',
        array('event_info' => $event_info),
        $file,
        $this->photo_server_addr);
    } else {
      return $this->call_method('facebook.events.create',
        array('event_info' => $event_info));
    }
  }

  /**
   * Invites users to an event. If a session user exists, the session user
   * must have permissions to invite friends to the event and $uids must contain
   * a list of friend ids. Otherwise, the event must have been
   * created by the app and $uids must contain users of the app.
   * This method requires the 'create_event' extended permission to
   * invite people on behalf of a user.
   *
   * @param $eid   the event id
   * @param $uids  an array of users to invite
   * @param $personal_message  a string containing the user's message
   *                           (text only)
   *
   */
  public function events_invite($eid, $uids, $personal_message) {
    return $this->call_method('facebook.events.invite',
                              array('eid' => $eid,
                                    'uids' => $uids,
                                    'personal_message' => $personal_message));
  }

  /**
   * Edits an existing event. Only works for events where application is admin.
   *
   * @param int $eid                 event id
   * @param assoc array $event_info  json encoded event information
   * @param string $file             (Optional) filename of new picture to set
   *
   * @return bool  true if successful
   */
  public function events_edit($eid, $event_info, $file = null) {
    if ($file) {
      return $this->call_upload_method('facebook.events.edit',
        array('eid' => $eid, 'event_info' => $event_info),
        $file,
        $this->photo_server_addr);
    } else {
      return $this->call_method('facebook.events.edit',
        array('eid' => $eid,
        'event_info' => $event_info));
    }
  }

  /**
   * Fetches and re-caches the image stored at the given URL, for use in images
   * published to non-canvas pages via the API (for example, to user profiles
   * via profile.setFBML, or to News Feed via feed.publishUserAction).
   *
   * @param string $url  The absolute URL from which to refresh the image.
   *
   * @return bool  true on success
   */
  public function &fbml_refreshImgSrc($url) {
    return $this->call_method('facebook.fbml.refreshImgSrc',
        array('url' => $url));
  }

  /**
   * Fetches and re-caches the content stored at the given URL, for use in an
   * fb:ref FBML tag.
   *
   * @param string $url  The absolute URL from which to fetch content. This URL
   *                     should be used in a fb:ref FBML tag.
   *
   * @return bool  true on success
   */
  public function &fbml_refreshRefUrl($url) {
    return $this->call_method('facebook.fbml.refreshRefUrl',
        array('url' => $url));
  }

 /**
   * Associates a given "handle" with FBML markup so that the handle can be
   * used within the fb:ref FBML tag. A handle is unique within an application
   * and allows an application to publish identical FBML to many user profiles
   * and do subsequent updates without having to republish FBML on behalf of
   * each user.
   *
   * @param string $handle  The handle to associate with the given FBML.
   * @param string $fbml    The FBML to associate with the given handle.
   *
   * @return bool  true on success
   */
  public function &fbml_setRefHandle($handle, $fbml) {
    return $this->call_method('facebook.fbml.setRefHandle',
        array('handle' => $handle, 'fbml' => $fbml));
  }

  /**
   * Register custom tags for the application. Custom tags can be used
   * to extend the set of tags available to applications in FBML
   * markup.
   *
   * Before you call this function,
   * make sure you read the full documentation at
   *
   * http://wiki.developers.facebook.com/index.php/Fbml.RegisterCustomTags
   *
   * IMPORTANT: This function overwrites the values of
   * existing tags if the names match. Use this function with care because
   * it may break the FBML of any application that is using the
   * existing version of the tags.
   *
   * @param mixed $tags an array of tag objects (the full description is on the
   *   wiki page)
   *
   * @return int  the number of tags that were registered
   */
  public function &fbml_registerCustomTags($tags) {
    $tags = json_encode($tags);
    return $this->call_method('facebook.fbml.registerCustomTags',
                              array('tags' => $tags));
  }

  /**
   * Get the custom tags for an application. If $app_id
   * is not specified, the calling app's tags are returned.
   * If $app_id is different from the id of the calling app,
   * only the app's public tags are returned.
   * The return value is an array of the same type as
   * the $tags parameter of fbml_registerCustomTags().
   *
   * @param int $app_id the application's id (optional)
   *
   * @return mixed  an array containing the custom tag  objects
   */
  public function &fbml_getCustomTags($app_id = null) {
    return $this->call_method('facebook.fbml.getCustomTags',
                              array('app_id' => $app_id));
  }


  /**
   * Delete custom tags the application has registered. If
   * $tag_names is null, all the application's custom tags will be
   * deleted.
   *
   * IMPORTANT: If your application has registered public tags
   * that other applications may be using, don't delete those tags!
   * Doing so can break the FBML ofapplications that are using them.
   *
   * @param array $tag_names the names of the tags to delete (optinal)
   * @return bool true on success
   */
  public function &fbml_deleteCustomTags($tag_names = null) {
    return $this->call_method('facebook.fbml.deleteCustomTags',
                              array('tag_names' => json_encode($tag_names)));
  }

  /**
   * Gets the best translations for native strings submitted by an application
   * for translation. If $locale is not specified, only native strings and their
   * descriptions are returned. If $all is true, then unapproved translations
   * are returned as well, otherwise only approved translations are returned.
   *
   * A mapping of locale codes -> language names is available at
   * http://wiki.developers.facebook.com/index.php/Facebook_Locales
   *
   * @param string $locale the locale to get translations for, or 'all' for all
   *                       locales, or 'en_US' for native strings
   * @param bool   $all    whether to return all or only approved translations
   *
   * @return array (locale, array(native_strings, array('best translation
   *                available given enough votes or manual approval', approval
   *                                                                  status)))
   * @error API_EC_PARAM
   * @error API_EC_PARAM_BAD_LOCALE
   */
  public function &intl_getTranslations($locale = 'en_US', $all = false) {
    return $this->call_method('facebook.intl.getTranslations',
                              array('locale' => $locale,
                                    'all'    => $all));
  }

  /**
   * Lets you insert text strings in their native language into the Facebook
   * Translations database so they can be translated.
   *
   * @param array $native_strings  An array of maps, where each map has a 'text'
   *                               field and a 'description' field.
   *
   * @return int  Number of strings uploaded.
   */
  public function &intl_uploadNativeStrings($native_strings) {
    return $this->call_method('facebook.intl.uploadNativeStrings',
        array('native_strings' => json_encode($native_strings)));
  }

  /**
   * This method is deprecated for calls made on behalf of users. This method
   * works only for publishing stories on a Facebook Page that has installed
   * your application. To publish stories to a user's profile, use
   * feed.publishUserAction instead.
   *
   * For more details on this call, please visit the wiki page:
   *
   * http://wiki.developers.facebook.com/index.php/Feed.publishTemplatizedAction
   */
  public function &feed_publishTemplatizedAction($title_template,
                                                 $title_data,
                                                 $body_template,
                                                 $body_data,
                                                 $body_general,
                                                 $image_1=null,
                                                 $image_1_link=null,
                                                 $image_2=null,
                                                 $image_2_link=null,
                                                 $image_3=null,
                                                 $image_3_link=null,
                                                 $image_4=null,
                                                 $image_4_link=null,
                                                 $target_ids='',
                                                 $page_actor_id=null) {
    return $this->call_method('facebook.feed.publishTemplatizedAction',
      array('title_template' => $title_template,
            'title_data' => $title_data,
            'body_template' => $body_template,
            'body_data' => $body_data,
            'body_general' => $body_general,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link,
            'target_ids' => $target_ids,
            'page_actor_id' => $page_actor_id));
  }

  /**
   * Registers a template bundle.  Template bundles are somewhat involved, so
   * it's recommended you check out the wiki for more details:
   *
   *  http://wiki.developers.facebook.com/index.php/Feed.registerTemplateBundle
   *
   * @return string  A template bundle id
   */
  public function &feed_registerTemplateBundle($one_line_story_templates,
                                               $short_story_templates = array(),
                                               $full_story_template = null,
                                               $action_links = array()) {

    $one_line_story_templates = json_encode($one_line_story_templates);

    if (!empty($short_story_templates)) {
      $short_story_templates = json_encode($short_story_templates);
    }

    if (isset($full_story_template)) {
      $full_story_template = json_encode($full_story_template);
    }

    if (isset($action_links)) {
      $action_links = json_encode($action_links);
    }

    return $this->call_method('facebook.feed.registerTemplateBundle',
        array('one_line_story_templates' => $one_line_story_templates,
              'short_story_templates' => $short_story_templates,
              'full_story_template' => $full_story_template,
              'action_links' => $action_links));
  }

  /**
   * Retrieves the full list of active template bundles registered by the
   * requesting application.
   *
   * @return array  An array of template bundles
   */
  public function &feed_getRegisteredTemplateBundles() {
    return $this->call_method('facebook.feed.getRegisteredTemplateBundles',
        array());
  }

  /**
   * Retrieves information about a specified template bundle previously
   * registered by the requesting application.
   *
   * @param string $template_bundle_id  The template bundle id
   *
   * @return array  Template bundle
   */
  public function &feed_getRegisteredTemplateBundleByID($template_bundle_id) {
    return $this->call_method('facebook.feed.getRegisteredTemplateBundleByID',
        array('template_bundle_id' => $template_bundle_id));
  }

  /**
   * Deactivates a previously registered template bundle.
   *
   * @param string $template_bundle_id  The template bundle id
   *
   * @return bool  true on success
   */
  public function &feed_deactivateTemplateBundleByID($template_bundle_id) {
    return $this->call_method('facebook.feed.deactivateTemplateBundleByID',
        array('template_bundle_id' => $template_bundle_id));
  }

  const STORY_SIZE_ONE_LINE = 1;
  const STORY_SIZE_SHORT = 2;
  const STORY_SIZE_FULL = 4;

  /**
   * Publishes a story on behalf of the user owning the session, using the
   * specified template bundle. This method requires an active session key in
   * order to be called.
   *
   * The parameters to this method ($templata_data in particular) are somewhat
   * involved.  It's recommended you visit the wiki for details:
   *
   *  http://wiki.developers.facebook.com/index.php/Feed.publishUserAction
   *
   * @param int $template_bundle_id  A template bundle id previously registered
   * @param array $template_data     See wiki article for syntax
   * @param array $target_ids        (Optional) An array of friend uids of the
   *                                 user who shared in this action.
   * @param string $body_general     (Optional) Additional markup that extends
   *                                 the body of a short story.
   * @param int $story_size          (Optional) A story size (see above)
   * @param string $user_message     (Optional) A user message for a short
   *                                 story.
   *
   * @return bool  true on success
   */
  public function &feed_publishUserAction(
      $template_bundle_id, $template_data, $target_ids='', $body_general='',
      $story_size=FacebookRestClient::STORY_SIZE_ONE_LINE,
      $user_message='') {

    if (is_array($template_data)) {
      $template_data = json_encode($template_data);
    } // allow client to either pass in JSON or an assoc that we JSON for them

    if (is_array($target_ids)) {
      $target_ids = json_encode($target_ids);
      $target_ids = trim($target_ids, "[]"); // we don't want square brackets
    }

    return $this->call_method('facebook.feed.publishUserAction',
        array('template_bundle_id' => $template_bundle_id,
              'template_data' => $template_data,
              'target_ids' => $target_ids,
              'body_general' => $body_general,
              'story_size' => $story_size,
              'user_message' => $user_message));
  }


  /**
   * Publish a post to the user's stream.
   *
   * @param $message        the user's message
   * @param $attachment     the post's attachment (optional)
   * @param $action links   the post's action links (optional)
   * @param $target_id      the user on whose wall the post will be posted
   *                        (optional)
   * @param $uid            the actor (defaults to session user)
   * @return string the post id
   */
  public function stream_publish(
    $message, $attachment = null, $action_links = null, $target_id = null,
    $uid = null) {

    return $this->call_method(
      'facebook.stream.publish',
      array('message' => $message,
            'attachment' => $attachment,
            'action_links' => $action_links,
            'target_id' => $target_id,
            'uid' => $this->get_uid($uid)));
  }

  /**
   * Remove a post from the user's stream.
   * Currently, you may only remove stories you application created.
   *
   * @param $post_id  the post id
   * @param $uid      the actor (defaults to session user)
   * @return bool
   */
  public function stream_remove($post_id, $uid = null) {
    return $this->call_method(
      'facebook.stream.remove',
      array('post_id' => $post_id,
            'uid' => $this->get_uid($uid)));
  }

  /**
   * Add a comment to a stream post
   *
   * @param $post_id  the post id
   * @param $comment  the comment text
   * @param $uid      the actor (defaults to session user)
   * @return string the id of the created comment
   */
  public function stream_addComment($post_id, $comment, $uid = null) {
    return $this->call_method(
      'facebook.stream.addComment',
      array('post_id' => $post_id,
            'comment' => $comment,
            'uid' => $this->get_uid($uid)));
  }


  /**
   * Remove a comment from a stream post
   *
   * @param $comment_id  the comment id
   * @param $uid      the actor (defaults to session user)
   * @return bool
   */
  public function stream_removeComment($comment_id, $uid = null) {
    return $this->call_method(
      'facebook.stream.removeComment',
      array('comment_id' => $comment_id,
            'uid' => $this->get_uid($uid)));
  }

  /**
   * Add a like to a stream post
   *
   * @param $post_id  the post id
   * @param $uid      the actor (defaults to session user)
   * @return bool
   */
  public function stream_addLike($post_id, $uid = null) {
    return $this->call_method(
      'facebook.stream.addLike',
      array('post_id' => $post_id,
            'uid' => $this->get_uid($uid)));
  }

  /**
   * Remove a like from a stream post
   *
   * @param $post_id  the post id
   * @param $uid      the actor (defaults to session user)
   * @return bool
   */
  public function stream_removeLike($post_id, $uid = null) {
    return $this->call_method(
      'facebook.stream.removeLike',
      array('post_id' => $post_id,
            'uid' => $this->get_uid($uid)));
  }

  /**
   * For the current user, retrieves stories generated by the user's friends
   * while using this application.  This can be used to easily create a
   * "News Feed" like experience.
   *
   * @return array  An array of feed story objects.
   */
  public function &feed_getAppFriendStories() {
    return $this->call_method('facebook.feed.getAppFriendStories');
  }

  /**
   * Makes an FQL query.  This is a generalized way of accessing all the data
   * in the API, as an alternative to most of the other method calls.  More
   * info at http://wiki.developers.facebook.com/index.php/FQL
   *
   * @param string $query  the query to evaluate
   *
   * @return array  generalized array representing the results
   */
  public function &fql_query($query) {
    return $this->call_method('facebook.fql.query',
      array('query' => $query));
  }

  /**
   * Makes a set of FQL queries in parallel.  This method takes a dictionary
   * of FQL queries where the keys are names for the queries.  Results from
   * one query can be used within another query to fetch additional data.  More
   * info about FQL queries at http://wiki.developers.facebook.com/index.php/FQL
   *
   * @param string $queries  JSON-encoded dictionary of queries to evaluate
   *
   * @return array  generalized array representing the results
   */
  public function &fql_multiquery($queries) {
    return $this->call_method('facebook.fql.multiquery',
      array('queries' => $queries));
  }

  /**
   * Returns whether or not pairs of users are friends.
   * Note that the Facebook friend relationship is symmetric.
   *
   * @param array/string $uids1  list of ids (id_1, id_2,...)
   *                       of some length X (csv is deprecated)
   * @param array/string $uids2  list of ids (id_A, id_B,...)
   *                       of SAME length X (csv is deprecated)
   *
   * @return array  An array with uid1, uid2, and bool if friends, e.g.:
   *   array(0 => array('uid1' => id_1, 'uid2' => id_A, 'are_friends' => 1),
   *         1 => array('uid1' => id_2, 'uid2' => id_B, 'are_friends' => 0)
   *         ...)
   * @error
   *    API_EC_PARAM_USER_ID_LIST
   */
  public function &friends_areFriends($uids1, $uids2) {
    return $this->call_method('facebook.friends.areFriends',
                 array('uids1' => $uids1,
                       'uids2' => $uids2));
  }

  /**
   * Returns the friends of the current session user.
   *
   * @param int $flid  (Optional) Only return friends on this friend list.
   * @param int $uid   (Optional) Return friends for this user.
   *
   * @return array  An array of friends
   */
  public function &friends_get($flid=null, $uid = null) {
    if (isset($this->friends_list)) {
      return $this->friends_list;
    }
    $params = array();
    if (!$uid && isset($this->canvas_user)) {
      $uid = $this->canvas_user;
    }
    if ($uid) {
      $params['uid'] = $uid;
    }
    if ($flid) {
      $params['flid'] = $flid;
    }
    return $this->call_method('facebook.friends.get', $params);

  }

  /**
   * Returns the mutual friends between the target uid and a source uid or
   * the current session user.
   *
   * @param int $target_uid Target uid for which mutual friends will be found.
   * @param int $source_uid (optional) Source uid for which mutual friends will
   *                                   be found. If no source_uid is specified,
   *                                   source_id will default to the session
   *                                   user.
   * @return array  An array of friend uids
   */
  public function &friends_getMutualFriends($target_uid, $source_uid = null) {
    return $this->call_method('facebook.friends.getMutualFriends',
                              array("target_uid" => $target_uid,
                                    "source_uid" => $source_uid));
  }

  /**
   * Returns the set of friend lists for the current session user.
   *
   * @return array  An array of friend list objects
   */
  public function &friends_getLists() {
    return $this->call_method('facebook.friends.getLists');
  }

  /**
   * Returns the friends of the session user, who are also users
   * of the calling application.
   *
   * @return array  An array of friends also using the app
   */
  public function &friends_getAppUsers() {
    return $this->call_method('facebook.friends.getAppUsers');
  }

  /**
   * Returns groups according to the filters specified.
   *
   * @param int $uid     (Optional) User associated with groups.  A null
   *                     parameter will default to the session user.
   * @param array/string $gids (Optional) Array of group ids to query. A null
   *                     parameter will get all groups for the user.
   *                     (csv is deprecated)
   *
   * @return array  An array of group objects
   */
  public function &groups_get($uid, $gids) {
    return $this->call_method('facebook.groups.get',
        array('uid' => $uid,
              'gids' => $gids));
  }

  /**
   * Returns the membership list of a group.
   *
   * @param int $gid  Group id
   *
   * @return array  An array with four membership lists, with keys 'members',
   *                'admins', 'officers', and 'not_replied'
   */
  public function &groups_getMembers($gid) {
    return $this->call_method('facebook.groups.getMembers',
      array('gid' => $gid));
  }

  /**
   * Returns cookies according to the filters specified.
   *
   * @param int $uid     User for which the cookies are needed.
   * @param string $name (Optional) A null parameter will get all cookies
   *                     for the user.
   *
   * @return array  Cookies!  Nom nom nom nom nom.
   */
  public function data_getCookies($uid, $name) {
    return $this->call_method('facebook.data.getCookies',
        array('uid' => $uid,
              'name' => $name));
  }

  /**
   * Sets cookies according to the params specified.
   *
   * @param int $uid       User for which the cookies are needed.
   * @param string $name   Name of the cookie
   * @param string $value  (Optional) if expires specified and is in the past
   * @param int $expires   (Optional) Expiry time
   * @param string $path   (Optional) Url path to associate with (default is /)
   *
   * @return bool  true on success
   */
  public function data_setCookie($uid, $name, $value, $expires, $path) {
    return $this->call_method('facebook.data.setCookie',
        array('uid' => $uid,
              'name' => $name,
              'value' => $value,
              'expires' => $expires,
              'path' => $path));
  }

  /**
   * Retrieves links posted by the given user.
   *
   * @param int    $uid      The user whose links you wish to retrieve
   * @param int    $limit    The maximimum number of links to retrieve
   * @param array $link_ids (Optional) Array of specific link
   *                          IDs to retrieve by this user
   *
   * @return array  An array of links.
   */
  public function &links_get($uid, $limit, $link_ids = null) {
    return $this->call_method('facebook.links.get',
        array('uid' => $uid,
              'limit' => $limit,
              'link_ids' => $link_ids));
  }

  /**
   * Posts a link on Facebook.
   *
   * @param string $url     URL/link you wish to post
   * @param string $comment (Optional) A comment about this link
   * @param int    $uid     (Optional) User ID that is posting this link;
   *                        defaults to current session user
   *
   * @return bool
   */
  public function &links_post($url, $comment='', $uid = null) {
    return $this->call_method('facebook.links.post',
        array('uid' => $uid,
              'url' => $url,
              'comment' => $comment));
  }

  /**
   * Permissions API
   */

  /**
   * Checks API-access granted by self to the specified application.
   *
   * @param string $permissions_apikey  Other application key
   *
   * @return array  API methods/namespaces which are allowed access
   */
  public function permissions_checkGrantedApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.checkGrantedApiAccess',
        array('permissions_apikey' => $permissions_apikey));
  }

  /**
   * Checks API-access granted to self by the specified application.
   *
   * @param string $permissions_apikey  Other application key
   *
   * @return array  API methods/namespaces which are allowed access
   */
  public function permissions_checkAvailableApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.checkAvailableApiAccess',
        array('permissions_apikey' => $permissions_apikey));
  }

  /**
   * Grant API-access to the specified methods/namespaces to the specified
   * application.
   *
   * @param string $permissions_apikey  Other application key
   * @param array(string) $method_arr   (Optional) API methods/namespaces
   *                                    allowed
   *
   * @return array  API methods/namespaces which are allowed access
   */
  public function permissions_grantApiAccess($permissions_apikey, $method_arr) {
    return $this->call_method('facebook.permissions.grantApiAccess',
        array('permissions_apikey' => $permissions_apikey,
              'method_arr' => $method_arr));
  }

  /**
   * Revoke API-access granted to the specified application.
   *
   * @param string $permissions_apikey  Other application key
   *
   * @return bool  true on success
   */
  public function permissions_revokeApiAccess($permissions_apikey) {
    return $this->call_method('facebook.permissions.revokeApiAccess',
        array('permissions_apikey' => $permissions_apikey));
  }

  /**
   * Payments Order API
   */

  /**
   * Set Payments properties for an app.
   *
   * @param  properties  a map from property names to  values
   * @return             true on success
   */
  public function payments_setProperties($properties) {
    return $this->call_method ('facebook.payments.setProperties',
        array('properties' => json_encode($properties)));
  }

  public function payments_getOrderDetails($order_id) {
    return json_decode($this->call_method(
        'facebook.payments.getOrderDetails',
        array('order_id' => $order_id)), true);
  }

  public function payments_updateOrder($order_id, $status,
                                         $params) {
    return $this->call_method('facebook.payments.updateOrder',
        array('order_id' => $order_id,
              'status' => $status,
              'params' => json_encode($params)));
  }

  public function payments_getOrders($status, $start_time,
                                       $end_time, $test_mode=false) {
    return json_decode($this->call_method('facebook.payments.getOrders',
        array('status' => $status,
              'start_time' => $start_time,
              'end_time' => $end_time,
              'test_mode' => $test_mode)), true);
  }

  /**
   * Gifts API
   */

  /**
   * Get Gifts associated with an app
   *
   * @return             array of gifts
   */
  public function gifts_get() {
    return json_decode(
        $this->call_method('facebook.gifts.get',
                           array()),
        true
        );
  }

  /*
   * Update gifts stored by an app
   *
   * @param array containing gift_id => gift_data to be updated
   * @return array containing gift_id => true/false indicating success
   *                                     in updating that gift
   */
  public function gifts_update($update_array) {
    return json_decode(
      $this->call_method('facebook.gifts.update',
                         array('update_str' => json_encode($update_array))
                        ),
      true
    );
  }


  /**
   * Creates a note with the specified title and content.
   *
   * @param string $title   Title of the note.
   * @param string $content Content of the note.
   * @param int    $uid     (Optional) The user for whom you are creating a
   *                        note; defaults to current session user
   *
   * @return int   The ID of the note that was just created.
   */
  public function &notes_create($title, $content, $uid = null) {
    return $this->call_method('facebook.notes.create',
        array('uid' => $uid,
              'title' => $title,
              'content' => $content));
  }

  /**
   * Deletes the specified note.
   *
   * @param int $note_id  ID of the note you wish to delete
   * @param int $uid      (Optional) Owner of the note you wish to delete;
   *                      defaults to current session user
   *
   * @return bool
   */
  public function &notes_delete($note_id, $uid = null) {
    return $this->call_method('facebook.notes.delete',
        array('uid' => $uid,
              'note_id' => $note_id));
  }

  /**
   * Edits a note, replacing its title and contents with the title
   * and contents specified.
   *
   * @param int    $note_id  ID of the note you wish to edit
   * @param string $title    Replacement title for the note
   * @param string $content  Replacement content for the note
   * @param int    $uid      (Optional) Owner of the note you wish to edit;
   *                         defaults to current session user
   *
   * @return bool
   */
  public function &notes_edit($note_id, $title, $content, $uid = null) {
    return $this->call_method('facebook.notes.edit',
        array('uid' => $uid,
              'note_id' => $note_id,
              'title' => $title,
              'content' => $content));
  }

  /**
   * Retrieves all notes by a user. If note_ids are specified,
   * retrieves only those specific notes by that user.
   *
   * @param int    $uid      User whose notes you wish to retrieve
   * @param array  $note_ids (Optional) List of specific note
   *                         IDs by this user to retrieve
   *
   * @return array A list of all of the given user's notes, or an empty list
   *               if the viewer lacks permissions or if there are no visible
   *               notes.
   */
  public function &notes_get($uid, $note_ids = null) {
    return $this->call_method('facebook.notes.get',
        array('uid' => $uid,
              'note_ids' => $note_ids));
  }


  /**
   * Returns the outstanding notifications for the session user.
   *
   * @return array An assoc array of notification count objects for
   *               'messages', 'pokes' and 'shares', a uid list of
   *               'friend_requests', a gid list of 'group_invites',
   *               and an eid list of 'event_invites'
   */
  public function &notifications_get() {
    return $this->call_method('facebook.notifications.get');
  }

  /**
   * Sends a notification to the specified users.
   *
   * @return A comma separated list of successful recipients
   * @error
   *    API_EC_PARAM_USER_ID_LIST
   */
  public function &notifications_send($to_ids, $notification, $type) {
    return $this->call_method('facebook.notifications.send',
        array('to_ids' => $to_ids,
              'notification' => $notification,
              'type' => $type));
  }

  /**
   * Sends an email to the specified user of the application.
   *
   * @param array/string $recipients array of ids of the recipients (csv is deprecated)
   * @param string $subject    subject of the email
   * @param string $text       (plain text) body of the email
   * @param string $fbml       fbml markup for an html version of the email
   *
   * @return string  A comma separated list of successful recipients
   * @error
   *    API_EC_PARAM_USER_ID_LIST
   */
  public function &notifications_sendEmail($recipients,
                                           $subject,
                                           $text,
                                           $fbml) {
    return $this->call_method('facebook.notifications.sendEmail',
        array('recipients' => $recipients,
              'subject' => $subject,
              'text' => $text,
              'fbml' => $fbml));
  }

  /**
   * Returns the requested info fields for the requested set of pages.
   *
   * @param array/string $page_ids  an array of page ids (csv is deprecated)
   * @param array/string  $fields    an array of strings describing the
   *                           info fields desired (csv is deprecated)
   * @param int    $uid       (Optional) limit results to pages of which this
   *                          user is a fan.
   * @param string type       limits results to a particular type of page.
   *
   * @return array  An array of pages
   */
  public function &pages_getInfo($page_ids, $fields, $uid, $type) {
    return $this->call_method('facebook.pages.getInfo',
        array('page_ids' => $page_ids,
              'fields' => $fields,
              'uid' => $uid,
              'type' => $type));
  }

  /**
   * Returns true if the given user is an admin for the passed page.
   *
   * @param int $page_id  target page id
   * @param int $uid      (Optional) user id (defaults to the logged-in user)
   *
   * @return bool  true on success
   */
  public function &pages_isAdmin($page_id, $uid = null) {
    return $this->call_method('facebook.pages.isAdmin',
        array('page_id' => $page_id,
              'uid' => $uid));
  }

  /**
   * Returns whether or not the given page has added the application.
   *
   * @param int $page_id  target page id
   *
   * @return bool  true on success
   */
  public function &pages_isAppAdded($page_id) {
    return $this->call_method('facebook.pages.isAppAdded',
        array('page_id' => $page_id));
  }

  /**
   * Returns true if logged in user is a fan for the passed page.
   *
   * @param int $page_id target page id
   * @param int $uid user to compare.  If empty, the logged in user.
   *
   * @return bool  true on success
   */
  public function &pages_isFan($page_id, $uid = null) {
    return $this->call_method('facebook.pages.isFan',
        array('page_id' => $page_id,
              'uid' => $uid));
  }

  /**
   * Adds a tag with the given information to a photo. See the wiki for details:
   *
   *  http://wiki.developers.facebook.com/index.php/Photos.addTag
   *
   * @param int $pid          The ID of the photo to be tagged
   * @param int $tag_uid      The ID of the user being tagged. You must specify
   *                          either the $tag_uid or the $tag_text parameter
   *                          (unless $tags is specified).
   * @param string $tag_text  Some text identifying the person being tagged.
   *                          You must specify either the $tag_uid or $tag_text
   *                          parameter (unless $tags is specified).
   * @param float $x          The horizontal position of the tag, as a
   *                          percentage from 0 to 100, from the left of the
   *                          photo.
   * @param float $y          The vertical position of the tag, as a percentage
   *                          from 0 to 100, from the top of the photo.
   * @param array $tags       (Optional) An array of maps, where each map
   *                          can contain the tag_uid, tag_text, x, and y
   *                          parameters defined above.  If specified, the
   *                          individual arguments are ignored.
   * @param int $owner_uid    (Optional)  The user ID of the user whose photo
   *                          you are tagging. If this parameter is not
   *                          specified, then it defaults to the session user.
   *
   * @return bool  true on success
   */
  public function &photos_addTag($pid,
                                 $tag_uid,
                                 $tag_text,
                                 $x,
                                 $y,
                                 $tags,
                                 $owner_uid=0) {
    return $this->call_method('facebook.photos.addTag',
        array('pid' => $pid,
              'tag_uid' => $tag_uid,
              'tag_text' => $tag_text,
              'x' => $x,
              'y' => $y,
              'tags' => (is_array($tags)) ? json_encode($tags) : null,
              'owner_uid' => $this->get_uid($owner_uid)));
  }

  /**
   * Creates and returns a new album owned by the specified user or the current
   * session user.
   *
   * @param string $name         The name of the album.
   * @param string $description  (Optional) A description of the album.
   * @param string $location     (Optional) A description of the location.
   * @param string $visible      (Optional) A privacy setting for the album.
   *                             One of 'friends', 'friends-of-friends',
   *                             'networks', or 'everyone'.  Default 'everyone'.
   * @param int $uid             (Optional) User id for creating the album; if
   *                             not specified, the session user is used.
   *
   * @return array  An album object
   */
  public function &photos_createAlbum($name,
                                      $description='',
                                      $location='',
                                      $visible='',
                                      $uid=0) {
    return $this->call_method('facebook.photos.createAlbum',
        array('name' => $name,
              'description' => $description,
              'location' => $location,
              'visible' => $visible,
              'uid' => $this->get_uid($uid)));
  }

  /**
   * Returns photos according to the filters specified.
   *
   * @param int $subj_id  (Optional) Filter by uid of user tagged in the photos.
   * @param int $aid      (Optional) Filter by an album, as returned by
   *                      photos_getAlbums.
   * @param array/string $pids   (Optional) Restrict to an array of pids
   *                             (csv is deprecated)
   *
   * Note that at least one of these parameters needs to be specified, or an
   * error is returned.
   *
   * @return array  An array of photo objects.
   */
  public function &photos_get($subj_id, $aid, $pids) {
    return $this->call_method('facebook.photos.get',
      array('subj_id' => $subj_id, 'aid' => $aid, 'pids' => $pids));
  }

  /**
   * Returns the albums created by the given user.
   *
   * @param int $uid      (Optional) The uid of the user whose albums you want.
   *                       A null will return the albums of the session user.
   * @param string $aids  (Optional) An array of aids to restrict
   *                       the query. (csv is deprecated)
   *
   * Note that at least one of the (uid, aids) parameters must be specified.
   *
   * @returns an array of album objects.
   */
  public function &photos_getAlbums($uid, $aids) {
    return $this->call_method('facebook.photos.getAlbums',
      array('uid' => $uid,
            'aids' => $aids));
  }

  /**
   * Returns the tags on all photos specified.
   *
   * @param string $pids  A list of pids to query
   *
   * @return array  An array of photo tag objects, which include pid,
   *                subject uid, and two floating-point numbers (xcoord, ycoord)
   *                for tag pixel location.
   */
  public function &photos_getTags($pids) {
    return $this->call_method('facebook.photos.getTags',
      array('pids' => $pids));
  }

  /**
   * Uploads a photo.
   *
   * @param string $file     The location of the photo on the local filesystem.
   * @param int $aid         (Optional) The album into which to upload the
   *                         photo.
   * @param string $caption  (Optional) A caption for the photo.
   * @param int uid          (Optional) The user ID of the user whose photo you
   *                         are uploading
   *
   * @return array  An array of user objects
   */
  public function photos_upload($file, $aid=null, $caption=null, $uid=null) {
    return $this->call_upload_method('facebook.photos.upload',
                                     array('aid' => $aid,
                                           'caption' => $caption,
                                           'uid' => $uid),
                                     $file);
  }


  /**
   * Uploads a video.
   *
   * @param  string $file        The location of the video on the local filesystem.
   * @param  string $title       (Optional) A title for the video. Titles over 65 characters in length will be truncated.
   * @param  string $description (Optional) A description for the video.
   *
   * @return array  An array with the video's ID, title, description, and a link to view it on Facebook.
   */
  public function video_upload($file, $title=null, $description=null) {
    return $this->call_upload_method('facebook.video.upload',
                                     array('title' => $title,
                                           'description' => $description),
                                     $file,
                                     Facebook::get_facebook_url('api-video') . '/restserver.php');
  }

  /**
   * Returns an array with the video limitations imposed on the current session's
   * associated user. Maximum length is measured in seconds; maximum size is
   * measured in bytes.
   *
   * @return array  Array with "length" and "size" keys
   */
  public function &video_getUploadLimits() {
    return $this->call_method('facebook.video.getUploadLimits');
  }

  /**
   * Returns the requested info fields for the requested set of users.
   *
   * @param array/string $uids    An array of user ids (csv is deprecated)
   * @param array/string $fields  An array of info field names desired (csv is deprecated)
   *
   * @return array  An array of user objects
   */
  public function &users_getInfo($uids, $fields) {
    return $this->call_method('facebook.users.getInfo',
                  array('uids' => $uids,
                        'fields' => $fields));
  }

  /**
   * Returns the requested info fields for the requested set of users. A
   * session key must not be specified. Only data about users that have
   * authorized your application will be returned.
   *
   * Check the wiki for fields that can be queried through this API call.
   * Data returned from here should not be used for rendering to application
   * users, use users.getInfo instead, so that proper privacy rules will be
   * applied.
   *
   * @param array/string $uids    An array of user ids (csv is deprecated)
   * @param array/string $fields  An array of info field names desired (csv is deprecated)
   *
   * @return array  An array of user objects
   */
  public function &users_getStandardInfo($uids, $fields) {
    return $this->call_method('facebook.users.getStandardInfo',
                              array('uids' => $uids,
                                    'fields' => $fields));
  }

  /**
   * Returns the user corresponding to the current session object.
   *
   * @return integer  User id
   */
  public function &users_getLoggedInUser() {
    return $this->call_method('facebook.users.getLoggedInUser');
  }

  /**
   * Returns 1 if the user has the specified permission, 0 otherwise.
   * http://wiki.developers.facebook.com/index.php/Users.hasAppPermission
   *
   * @return integer  1 or 0
   */
  public function &users_hasAppPermission($ext_perm, $uid=null) {
    return $this->call_method('facebook.users.hasAppPermission',
        array('ext_perm' => $ext_perm, 'uid' => $uid));
  }

  /**
   * Returns whether or not the user corresponding to the current
   * session object has the give the app basic authorization.
   *
   * @return boolean  true if the user has authorized the app
   */
  public function &users_isAppUser($uid=null) {
    if ($uid === null && isset($this->is_user)) {
      return $this->is_user;
    }

    return $this->call_method('facebook.users.isAppUser', array('uid' => $uid));
  }

  /**
   * Returns whether or not the user corresponding to the current
   * session object is verified by Facebook. See the documentation
   * for Users.isVerified for details.
   *
   * @return boolean  true if the user is verified
   */
  public function &users_isVerified() {
    return $this->call_method('facebook.users.isVerified');
  }

  /**
   * Sets the users' current status message. Message does NOT contain the
   * word "is" , so make sure to include a verb.
   *
   * Example: setStatus("is loving the API!")
   * will produce the status "Luke is loving the API!"
   *
   * @param string $status                text-only message to set
   * @param int    $uid                   user to set for (defaults to the
   *                                      logged-in user)
   * @param bool   $clear                 whether or not to clear the status,
   *                                      instead of setting it
   * @param bool   $status_includes_verb  if true, the word "is" will *not* be
   *                                      prepended to the status message
   *
   * @return boolean
   */
  public function &users_setStatus($status,
                                   $uid = null,
                                   $clear = false,
                                   $status_includes_verb = true) {
    $args = array(
      'status' => $status,
      'uid' => $uid,
      'clear' => $clear,
      'status_includes_verb' => $status_includes_verb,
    );
    return $this->call_method('facebook.users.setStatus', $args);
  }

  /**
   * Gets the comments for a particular xid. This is essentially a wrapper
   * around the comment FQL table.
   *
   * @param string $xid external id associated with the comments
   *
   * @return array of comment objects
   */
  public function &comments_get($xid) {
    $args = array('xid' => $xid);
    return $this->call_method('facebook.comments.get', $args);
  }

  /**
   * Add a comment to a particular xid on behalf of a user. If called
   * without an app_secret (with session secret), this will only work
   * for the session user.
   *
   * @param string $xid   external id associated with the comments
   * @param string $text  text of the comment
   * @param int    $uid   user adding the comment (def: session user)
   * @param string $title optional title for the stream story
   * @param string $url   optional url for the stream story
   * @param bool   $publish_to_stream publish a feed story about this comment?
   *                      a link will be generated to title/url in the story
   *
   * @return string comment_id associated with the comment
   */
  public function &comments_add($xid, $text, $uid=0, $title='', $url='',
                                $publish_to_stream=false) {
    $args = array(
      'xid'               => $xid,
      'uid'               => $this->get_uid($uid),
      'text'              => $text,
      'title'             => $title,
      'url'               => $url,
      'publish_to_stream' => $publish_to_stream);

    return $this->call_method('facebook.comments.add', $args);
  }

  /**
   * Remove a particular comment.
   *
   * @param string $xid        the external id associated with the comments
   * @param string $comment_id id of the comment to remove (returned by
   *                           comments.add and comments.get)
   *
   * @return boolean
   */
  public function &comments_remove($xid, $comment_id) {
    $args = array(
      'xid'        => $xid,
      'comment_id' => $comment_id);
    return $this->call_method('facebook.comments.remove', $args);
  }

  /**
   * Gets the stream on behalf of a user using a set of users. This
   * call will return the latest $limit queries between $start_time
   * and $end_time.
   *
   * @param int    $viewer_id  user making the call (def: session)
   * @param array  $source_ids users/pages to look at (def: all connections)
   * @param int    $start_time start time to look for stories (def: 1 day ago)
   * @param int    $end_time   end time to look for stories (def: now)
   * @param int    $limit      number of stories to attempt to fetch (def: 30)
   * @param string $filter_key key returned by stream.getFilters to fetch
   * @param array  $metadata   metadata to include with the return, allows
   *                           requested metadata to be returned, such as
   *                           profiles, albums, photo_tags
   *
   * @return array(
   *           'posts'      => array of posts,
   *           // if requested, the following data may be returned
   *           'profiles'   => array of profile metadata of users/pages in posts
   *           'albums'     => array of album metadata in posts
   *           'photo_tags' => array of photo_tags for photos in posts
   *         )
   */
  public function &stream_get($viewer_id = null,
                              $source_ids = null,
                              $start_time = 0,
                              $end_time = 0,
                              $limit = 30,
                              $filter_key = '',
                              $exportable_only = false,
                              $metadata = null,
                              $post_ids = null,
                              $query = null,
                              $everyone_stream = false) {
    $args = array(
      'viewer_id'  => $viewer_id,
      'source_ids' => $source_ids,
      'start_time' => $start_time,
      'end_time'   => $end_time,
      'limit'      => $limit,
      'filter_key' => $filter_key,
      'exportable_only' => $exportable_only,
      'metadata' => $metadata,
      'post_ids' => $post_ids,
      'query' => $query,
      'everyone_stream' => $everyone_stream);
    return $this->call_method('facebook.stream.get', $args);
  }

  /**
   * Gets the filters (with relevant filter keys for stream.get) for a
   * particular user. These filters are typical things like news feed,
   * friend lists, networks. They can be used to filter the stream
   * without complex queries to determine which ids belong in which groups.
   *
   * @param int $uid user to get filters for
   *
   * @return array of stream filter objects
   */
  public function &stream_getFilters($uid = null) {
    $args = array('uid' => $uid);
    return $this->call_method('facebook.stream.getFilters', $args);
  }

  /**
   * Gets the full comments given a post_id from stream.get or the
   * stream FQL table. Initially, only a set of preview comments are
   * returned because some posts can have many comments.
   *
   * @param string $post_id id of the post to get comments for
   *
   * @return array of comment objects
   */
  public function &stream_getComments($post_id) {
    $args = array('post_id' => $post_id);
    return $this->call_method('facebook.stream.getComments', $args);
  }

  /**
   * Sets the FBML for the profile of the user attached to this session.
   *
   * @param   string   $markup           The FBML that describes the profile
   *                                     presence of this app for the user
   * @param   int      $uid              The user
   * @param   string   $profile          Profile FBML
   * @param   string   $profile_action   Profile action FBML (deprecated)
   * @param   string   $mobile_profile   Mobile profile FBML
   * @param   string   $profile_main     Main Tab profile FBML
   *
   * @return  array  A list of strings describing any compile errors for the
   *                 submitted FBML
   */
  public function profile_setFBML($markup,
                           $uid=null,
                           $profile='',
                           $profile_action='',
                           $mobile_profile='',
                           $profile_main='') {
    return $this->call_method('facebook.profile.setFBML',
        array('markup' => $markup,
              'uid' => $uid,
              'profile' => $profile,
              'profile_action' => $profile_action,
              'mobile_profile' => $mobile_profile,
              'profile_main' => $profile_main));
  }

  /**
   * Gets the FBML for the profile box that is currently set for a user's
   * profile (your application set the FBML previously by calling the
   * profile.setFBML method).
   *
   * @param int $uid   (Optional) User id to lookup; defaults to session.
   * @param int $type  (Optional) 1 for original style, 2 for profile_main boxes
   *
   * @return string  The FBML
   */
  public function &profile_getFBML($uid=null, $type=null) {
    return $this->call_method('facebook.profile.getFBML',
        array('uid' => $uid,
              'type' => $type));
  }

  /**
   * Returns the specified user's application info section for the calling
   * application. These info sections have either been set via a previous
   * profile.setInfo call or by the user editing them directly.
   *
   * @param int $uid  (Optional) User id to lookup; defaults to session.
   *
   * @return array  Info fields for the current user.  See wiki for structure:
   *
   *  http://wiki.developers.facebook.com/index.php/Profile.getInfo
   *
   */
  public function &profile_getInfo($uid=null) {
    return $this->call_method('facebook.profile.getInfo',
        array('uid' => $uid));
  }

  /**
   * Returns the options associated with the specified info field for an
   * application info section.
   *
   * @param string $field  The title of the field
   *
   * @return array  An array of info options.
   */
  public function &profile_getInfoOptions($field) {
    return $this->call_method('facebook.profile.getInfoOptions',
        array('field' => $field));
  }

  /**
   * Configures an application info section that the specified user can install
   * on the Info tab of her profile.  For details on the structure of an info
   * field, please see:
   *
   *  http://wiki.developers.facebook.com/index.php/Profile.setInfo
   *
   * @param string $title       Title / header of the info section
   * @param int $type           1 for text-only, 5 for thumbnail views
   * @param array $info_fields  An array of info fields. See wiki for details.
   * @param int $uid            (Optional)
   *
   * @return bool  true on success
   */
  public function &profile_setInfo($title, $type, $info_fields, $uid=null) {
    return $this->call_method('facebook.profile.setInfo',
        array('uid' => $uid,
              'type' => $type,
              'title'   => $title,
              'info_fields' => json_encode($info_fields)));
  }

  /**
   * Specifies the objects for a field for an application info section. These
   * options populate the typeahead for a thumbnail.
   *
   * @param string $field   The title of the field
   * @param array $options  An array of items for a thumbnail, including
   *                        'label', 'link', and optionally 'image',
   *                        'description' and 'sublabel'
   *
   * @return bool  true on success
   */
  public function profile_setInfoOptions($field, $options) {
    return $this->call_method('facebook.profile.setInfoOptions',
        array('field'   => $field,
              'options' => json_encode($options)));
  }

  /////////////////////////////////////////////////////////////////////////////
  // Data Store API

  /**
   * Set a user preference.
   *
   * @param  pref_id    preference identifier (0-200)
   * @param  value      preferece's value
   * @param  uid        the user id (defaults to current session user)
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   *    API_EC_PERMISSION_OTHER_USER
   */
  public function &data_setUserPreference($pref_id, $value, $uid = null) {
    return $this->call_method('facebook.data.setUserPreference',
       array('pref_id' => $pref_id,
             'value' => $value,
             'uid' => $this->get_uid($uid)));
  }

  /**
   * Set a user's all preferences for this application.
   *
   * @param  values     preferece values in an associative arrays
   * @param  replace    whether to replace all existing preferences or
   *                    merge into them.
   * @param  uid        the user id (defaults to current session user)
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   *    API_EC_PERMISSION_OTHER_USER
   */
  public function &data_setUserPreferences($values,
                                           $replace = false,
                                           $uid = null) {
    return $this->call_method('facebook.data.setUserPreferences',
       array('values' => json_encode($values),
             'replace' => $replace,
             'uid' => $this->get_uid($uid)));
  }

  /**
   * Get a user preference.
   *
   * @param  pref_id    preference identifier (0-200)
   * @param  uid        the user id (defaults to current session user)
   * @return            preference's value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   *    API_EC_PERMISSION_OTHER_USER
   */
  public function &data_getUserPreference($pref_id, $uid = null) {
    return $this->call_method('facebook.data.getUserPreference',
       array('pref_id' => $pref_id,
             'uid' => $this->get_uid($uid)));
  }

  /**
   * Get a user preference.
   *
   * @param  uid        the user id (defaults to current session user)
   * @return            preference values
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   *    API_EC_PERMISSION_OTHER_USER
   */
  public function &data_getUserPreferences($uid = null) {
    return $this->call_method('facebook.data.getUserPreferences',
       array('uid' => $this->get_uid($uid)));
  }

  /**
   * Create a new object type.
   *
   * @param  name       object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_createObjectType($name) {
    return $this->call_method('facebook.data.createObjectType',
       array('name' => $name));
  }

  /**
   * Delete an object type.
   *
   * @param  obj_type       object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_dropObjectType($obj_type) {
    return $this->call_method('facebook.data.dropObjectType',
       array('obj_type' => $obj_type));
  }

  /**
   * Rename an object type.
   *
   * @param  obj_type       object type's name
   * @param  new_name       new object type's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_renameObjectType($obj_type, $new_name) {
    return $this->call_method('facebook.data.renameObjectType',
       array('obj_type' => $obj_type,
             'new_name' => $new_name));
  }

  /**
   * Add a new property to an object type.
   *
   * @param  obj_type       object type's name
   * @param  prop_name      name of the property to add
   * @param  prop_type      1: integer; 2: string; 3: text blob
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_defineObjectProperty($obj_type,
                                             $prop_name,
                                             $prop_type) {
    return $this->call_method('facebook.data.defineObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name,
             'prop_type' => $prop_type));
  }

  /**
   * Remove a previously defined property from an object type.
   *
   * @param  obj_type      object type's name
   * @param  prop_name     name of the property to remove
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_undefineObjectProperty($obj_type, $prop_name) {
    return $this->call_method('facebook.data.undefineObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name));
  }

  /**
   * Rename a previously defined property of an object type.
   *
   * @param  obj_type      object type's name
   * @param  prop_name     name of the property to rename
   * @param  new_name      new name to use
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_renameObjectProperty($obj_type, $prop_name,
                                            $new_name) {
    return $this->call_method('facebook.data.renameObjectProperty',
       array('obj_type' => $obj_type,
             'prop_name' => $prop_name,
             'new_name' => $new_name));
  }

  /**
   * Retrieve a list of all object types that have defined for the application.
   *
   * @return               a list of object type names
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getObjectTypes() {
    return $this->call_method('facebook.data.getObjectTypes');
  }

  /**
   * Get definitions of all properties of an object type.
   *
   * @param obj_type       object type's name
   * @return               pairs of property name and property types
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getObjectType($obj_type) {
    return $this->call_method('facebook.data.getObjectType',
       array('obj_type' => $obj_type));
  }

  /**
   * Create a new object.
   *
   * @param  obj_type      object type's name
   * @param  properties    (optional) properties to set initially
   * @return               newly created object's id
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_createObject($obj_type, $properties = null) {
    return $this->call_method('facebook.data.createObject',
       array('obj_type' => $obj_type,
             'properties' => json_encode($properties)));
  }

  /**
   * Update an existing object.
   *
   * @param  obj_id        object's id
   * @param  properties    new properties
   * @param  replace       true for replacing existing properties;
   *                       false for merging
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_updateObject($obj_id, $properties, $replace = false) {
    return $this->call_method('facebook.data.updateObject',
       array('obj_id' => $obj_id,
             'properties' => json_encode($properties),
             'replace' => $replace));
  }

  /**
   * Delete an existing object.
   *
   * @param  obj_id        object's id
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_deleteObject($obj_id) {
    return $this->call_method('facebook.data.deleteObject',
       array('obj_id' => $obj_id));
  }

  /**
   * Delete a list of objects.
   *
   * @param  obj_ids       objects to delete
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_deleteObjects($obj_ids) {
    return $this->call_method('facebook.data.deleteObjects',
       array('obj_ids' => json_encode($obj_ids)));
  }

  /**
   * Get a single property value of an object.
   *
   * @param  obj_id        object's id
   * @param  prop_name     individual property's name
   * @return               individual property's value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getObjectProperty($obj_id, $prop_name) {
    return $this->call_method('facebook.data.getObjectProperty',
       array('obj_id' => $obj_id,
             'prop_name' => $prop_name));
  }

  /**
   * Get properties of an object.
   *
   * @param  obj_id      object's id
   * @param  prop_names  (optional) properties to return; null for all.
   * @return             specified properties of an object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getObject($obj_id, $prop_names = null) {
    return $this->call_method('facebook.data.getObject',
       array('obj_id' => $obj_id,
             'prop_names' => json_encode($prop_names)));
  }

  /**
   * Get properties of a list of objects.
   *
   * @param  obj_ids     object ids
   * @param  prop_names  (optional) properties to return; null for all.
   * @return             specified properties of an object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getObjects($obj_ids, $prop_names = null) {
    return $this->call_method('facebook.data.getObjects',
       array('obj_ids' => json_encode($obj_ids),
             'prop_names' => json_encode($prop_names)));
  }

  /**
   * Set a single property value of an object.
   *
   * @param  obj_id        object's id
   * @param  prop_name     individual property's name
   * @param  prop_value    new value to set
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_setObjectProperty($obj_id, $prop_name,
                                         $prop_value) {
    return $this->call_method('facebook.data.setObjectProperty',
       array('obj_id' => $obj_id,
             'prop_name' => $prop_name,
             'prop_value' => $prop_value));
  }

  /**
   * Read hash value by key.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  prop_name     (optional) individual property's name
   * @return               hash value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getHashValue($obj_type, $key, $prop_name = null) {
    return $this->call_method('facebook.data.getHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'prop_name' => $prop_name));
  }

  /**
   * Write hash value by key.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  value         hash value
   * @param  prop_name     (optional) individual property's name
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_setHashValue($obj_type,
                                     $key,
                                     $value,
                                     $prop_name = null) {
    return $this->call_method('facebook.data.setHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'value' => $value,
             'prop_name' => $prop_name));
  }

  /**
   * Increase a hash value by specified increment atomically.
   *
   * @param  obj_type      object type's name
   * @param  key           hash key
   * @param  prop_name     individual property's name
   * @param  increment     (optional) default is 1
   * @return               incremented hash value
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_incHashValue($obj_type,
                                     $key,
                                     $prop_name,
                                     $increment = 1) {
    return $this->call_method('facebook.data.incHashValue',
       array('obj_type' => $obj_type,
             'key' => $key,
             'prop_name' => $prop_name,
             'increment' => $increment));
  }

  /**
   * Remove a hash key and its values.
   *
   * @param  obj_type    object type's name
   * @param  key         hash key
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_removeHashKey($obj_type, $key) {
    return $this->call_method('facebook.data.removeHashKey',
       array('obj_type' => $obj_type,
             'key' => $key));
  }

  /**
   * Remove hash keys and their values.
   *
   * @param  obj_type    object type's name
   * @param  keys        hash keys
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_removeHashKeys($obj_type, $keys) {
    return $this->call_method('facebook.data.removeHashKeys',
       array('obj_type' => $obj_type,
             'keys' => json_encode($keys)));
  }

  /**
   * Define an object association.
   *
   * @param  name        name of this association
   * @param  assoc_type  1: one-way 2: two-way symmetric 3: two-way asymmetric
   * @param  assoc_info1 needed info about first object type
   * @param  assoc_info2 needed info about second object type
   * @param  inverse     (optional) name of reverse association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_defineAssociation($name, $assoc_type, $assoc_info1,
                                         $assoc_info2, $inverse = null) {
    return $this->call_method('facebook.data.defineAssociation',
       array('name' => $name,
             'assoc_type' => $assoc_type,
             'assoc_info1' => json_encode($assoc_info1),
             'assoc_info2' => json_encode($assoc_info2),
             'inverse' => $inverse));
  }

  /**
   * Undefine an object association.
   *
   * @param  name        name of this association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_undefineAssociation($name) {
    return $this->call_method('facebook.data.undefineAssociation',
       array('name' => $name));
  }

  /**
   * Rename an object association or aliases.
   *
   * @param  name        name of this association
   * @param  new_name    (optional) new name of this association
   * @param  new_alias1  (optional) new alias for object type 1
   * @param  new_alias2  (optional) new alias for object type 2
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_ALREADY_EXISTS
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_renameAssociation($name, $new_name, $new_alias1 = null,
                                         $new_alias2 = null) {
    return $this->call_method('facebook.data.renameAssociation',
       array('name' => $name,
             'new_name' => $new_name,
             'new_alias1' => $new_alias1,
             'new_alias2' => $new_alias2));
  }

  /**
   * Get definition of an object association.
   *
   * @param  name        name of this association
   * @return             specified association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociationDefinition($name) {
    return $this->call_method('facebook.data.getAssociationDefinition',
       array('name' => $name));
  }

  /**
   * Get definition of all associations.
   *
   * @return             all defined associations
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociationDefinitions() {
    return $this->call_method('facebook.data.getAssociationDefinitions',
       array());
  }

  /**
   * Create or modify an association between two objects.
   *
   * @param  name        name of association
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @param  data        (optional) extra string data to store
   * @param  assoc_time  (optional) extra time data; default to creation time
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_setAssociation($name, $obj_id1, $obj_id2, $data = null,
                                      $assoc_time = null) {
    return $this->call_method('facebook.data.setAssociation',
       array('name' => $name,
             'obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2,
             'data' => $data,
             'assoc_time' => $assoc_time));
  }

  /**
   * Create or modify associations between objects.
   *
   * @param  assocs      associations to set
   * @param  name        (optional) name of association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_setAssociations($assocs, $name = null) {
    return $this->call_method('facebook.data.setAssociations',
       array('assocs' => json_encode($assocs),
             'name' => $name));
  }

  /**
   * Remove an association between two objects.
   *
   * @param  name        name of association
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_removeAssociation($name, $obj_id1, $obj_id2) {
    return $this->call_method('facebook.data.removeAssociation',
       array('name' => $name,
             'obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2));
  }

  /**
   * Remove associations between objects by specifying pairs of object ids.
   *
   * @param  assocs      associations to remove
   * @param  name        (optional) name of association
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_removeAssociations($assocs, $name = null) {
    return $this->call_method('facebook.data.removeAssociations',
       array('assocs' => json_encode($assocs),
             'name' => $name));
  }

  /**
   * Remove associations between objects by specifying one object id.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to remove
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_removeAssociatedObjects($name, $obj_id) {
    return $this->call_method('facebook.data.removeAssociatedObjects',
       array('name' => $name,
             'obj_id' => $obj_id));
  }

  /**
   * Retrieve a list of associated objects.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to retrieve
   * @param  no_data     only return object ids
   * @return             associated objects
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociatedObjects($name, $obj_id, $no_data = true) {
    return $this->call_method('facebook.data.getAssociatedObjects',
       array('name' => $name,
             'obj_id' => $obj_id,
             'no_data' => $no_data));
  }

  /**
   * Count associated objects.
   *
   * @param  name        name of association
   * @param  obj_id      who's association to retrieve
   * @return             associated object's count
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociatedObjectCount($name, $obj_id) {
    return $this->call_method('facebook.data.getAssociatedObjectCount',
       array('name' => $name,
             'obj_id' => $obj_id));
  }

  /**
   * Get a list of associated object counts.
   *
   * @param  name        name of association
   * @param  obj_ids     whose association to retrieve
   * @return             associated object counts
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_DATA_OBJECT_NOT_FOUND
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_INVALID_OPERATION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociatedObjectCounts($name, $obj_ids) {
    return $this->call_method('facebook.data.getAssociatedObjectCounts',
       array('name' => $name,
             'obj_ids' => json_encode($obj_ids)));
  }

  /**
   * Find all associations between two objects.
   *
   * @param  obj_id1     id of first object
   * @param  obj_id2     id of second object
   * @param  no_data     only return association names without data
   * @return             all associations between objects
   * @error
   *    API_EC_DATA_DATABASE_ERROR
   *    API_EC_PARAM
   *    API_EC_PERMISSION
   *    API_EC_DATA_QUOTA_EXCEEDED
   *    API_EC_DATA_UNKNOWN_ERROR
   */
  public function &data_getAssociations($obj_id1, $obj_id2, $no_data = true) {
    return $this->call_method('facebook.data.getAssociations',
       array('obj_id1' => $obj_id1,
             'obj_id2' => $obj_id2,
             'no_data' => $no_data));
  }

  /**
   * Get the properties that you have set for an app.
   *
   * @param properties  List of properties names to fetch
   *
   * @return array  A map from property name to value
   */
  public function admin_getAppProperties($properties) {
    return json_decode(
        $this->call_method('facebook.admin.getAppProperties',
            array('properties' => json_encode($properties))), true);
  }

  /**
   * Set properties for an app.
   *
   * @param properties  A map from property names to values
   *
   * @return bool  true on success
   */
  public function admin_setAppProperties($properties) {
    return $this->call_method('facebook.admin.setAppProperties',
       array('properties' => json_encode($properties)));
  }

  /**
   * Sets href and text for a Live Stream Box xid's via link
   *
   * @param  string  $xid       xid of the Live Stream
   * @param  string  $via_href  Href for the via link
   * @param  string  $via_text  Text for the via link
   *
   * @return boolWhether the set was successful
   */
  public function admin_setLiveStreamViaLink($xid, $via_href, $via_text) {
    return $this->call_method('facebook.admin.setLiveStreamViaLink',
                              array('xid'      => $xid,
                                    'via_href' => $via_href,
                                    'via_text' => $via_text));
  }

  /**
   * Gets href and text for a Live Stream Box xid's via link
   *
   * @param  string  $xid  xid of the Live Stream
   *
   * @return Array  Associative array with keys 'via_href' and 'via_text'
   *                False if there was an error.
   */
  public function admin_getLiveStreamViaLink($xid) {
    return $this->call_method('facebook.admin.getLiveStreamViaLink',
                              array('xid' => $xid));
  }

  /**
   * Returns the allocation limit value for a specified integration point name
   * Integration point names are defined in lib/api/karma/constants.php in the
   * limit_map.
   *
   * @param string $integration_point_name  Name of an integration point
   *                                        (see developer wiki for list).
   * @param int    $uid                     Specific user to check the limit.
   *
   * @return int  Integration point allocation value
   */
  public function &admin_getAllocation($integration_point_name, $uid=null) {
    return $this->call_method('facebook.admin.getAllocation',
        array('integration_point_name' => $integration_point_name,
              'uid' => $uid));
  }

  /**
   * Returns values for the specified metrics for the current application, in
   * the given time range.  The metrics are collected for fixed-length periods,
   * and the times represent midnight at the end of each period.
   *
   * @param start_time  unix time for the start of the range
   * @param end_time    unix time for the end of the range
   * @param period      number of seconds in the desired period
   * @param metrics     list of metrics to look up
   *
   * @return array  A map of the names and values for those metrics
   */
  public function &admin_getMetrics($start_time, $end_time, $period, $metrics) {
    return $this->call_method('facebook.admin.getMetrics',
        array('start_time' => $start_time,
              'end_time' => $end_time,
              'period' => $period,
              'metrics' => json_encode($metrics)));
  }

  /**
   * Sets application restriction info.
   *
   * Applications can restrict themselves to only a limited user demographic
   * based on users' age and/or location or based on static predefined types
   * specified by facebook for specifying diff age restriction for diff
   * locations.
   *
   * @param array $restriction_info  The age restriction settings to set.
   *
   * @return bool  true on success
   */
  public function admin_setRestrictionInfo($restriction_info = null) {
    $restriction_str = null;
    if (!empty($restriction_info)) {
      $restriction_str = json_encode($restriction_info);
    }
    return $this->call_method('facebook.admin.setRestrictionInfo',
        array('restriction_str' => $restriction_str));
  }

  /**
   * Gets application restriction info.
   *
   * Applications can restrict themselves to only a limited user demographic
   * based on users' age and/or location or based on static predefined types
   * specified by facebook for specifying diff age restriction for diff
   * locations.
   *
   * @return array  The age restriction settings for this application.
   */
  public function admin_getRestrictionInfo() {
    return json_decode(
        $this->call_method('facebook.admin.getRestrictionInfo'),
        true);
  }


  /**
   * Bans a list of users from the app. Banned users can't
   * access the app's canvas page and forums.
   *
   * @param array $uids an array of user ids
   * @return bool true on success
   */
  public function admin_banUsers($uids) {
    return $this->call_method(
      'facebook.admin.banUsers', array('uids' => json_encode($uids)));
  }

  /**
   * Unban users that have been previously banned with
   * admin_banUsers().
   *
   * @param array $uids an array of user ids
   * @return bool true on success
   */
  public function admin_unbanUsers($uids) {
    return $this->call_method(
      'facebook.admin.unbanUsers', array('uids' => json_encode($uids)));
  }

  /**
   * Gets the list of users that have been banned from the application.
   * $uids is an optional parameter that filters the result with the list
   * of provided user ids. If $uids is provided,
   * only banned user ids that are contained in $uids are returned.
   *
   * @param array $uids an array of user ids to filter by
   * @return bool true on success
   */
  public function admin_getBannedUsers($uids = null) {
    return $this->call_method(
      'facebook.admin.getBannedUsers',
      array('uids' => $uids ? json_encode($uids) : null));
  }

  /**
   * Add global news for an app.
   * App Secret only.
   *
   * @param  news   Array of news items [{message, action_link => {href, text}}]
   * @param  image  Valid image url // Optional
   *
   * @return fbid   ID of newly created news bundle
   */
  public function dashboard_addGlobalNews($news, $image = null) {
    return $this->call_method('facebook.dashboard.addGlobalNews',
      array('news'  => $news,
            'image' => $image));
  }

  /**
   * Add news for a specific user.
   *
   * @param  news   Array of news items [{message, action_link => {href, text}}]
   * @param  image  Valid image url // Optional
   * @param  uid    The user ID of the user // Optional if session provided
   *
   * @return fbid   ID of newly created news bundle
   */
  public function dashboard_addNews($news, $image = null, $uid = null) {
    return $this->call_method('facebook.dashboard.addNews',
      array('uid'   => $uid,
            'news'  => $news,
            'image' => $image));
  }

  /**
   * Remove global news for an app.
   * App Secret only.
   *
   * @param  news_ids  Array of fbids of news bundles // All if empty
   *
   * @return results   Array where key => news_id
   *                             value => successfully cleared
   */
  public function dashboard_clearGlobalNews($news_ids = null) {
    return $this->call_method('facebook.dashboard.clearGlobalNews',
      array('news_ids' => $news_ids));
  }

  /**
   * Clear the news for a specific user.
   *
   * @param  news_ids  Array of fbids of news bundles // All if empty
   * @param  uid       The user ID of the user // Optional if session provided
   *
   * @return results   Array where key => news_id
   *                             value => successfully cleared
   */
  public function dashboard_clearNews($news_ids, $uid = null) {
    return $this->call_method('facebook.dashboard.clearNews',
      array('uid'      => $uid,
            'news_ids' => $news_ids));
  }

  /**
   * Decrement the count for a specific user.
   *
   * @param  uid   The user ID of the user // Optional if session provided
   *
   * @return bool  Success // If the count is already 0, decrementing fails.
   */
  public function dashboard_decrementCount($uid = null) {
    return $this->call_method('facebook.dashboard.decrementCount',
      array('uid' => $uid));
  }

  /**
   * Get a user's activity.
   *
   * @param  activity_ids    Array of fbids of activity bundles // All if empty
   * @param  uid             The user ID of the user // Optional if session key
   *
   * @return activities      Array of activities, including 'time' and 'fbid'
   *                         [{message, time, fbid,
   *                           action_link => {text, href}}]
   */
  public function dashboard_getActivity($activity_ids, $uid = null) {
    return $this->call_method('facebook.dashboard.getActivity',
      array('uid' => $uid,
            'activity_ids' => $activity_ids));
  }

  /**
   * Get the count for a specific user.
   *
   * @param  uid    The user ID of the user // Optional if session provided
   *
   * @return count  The user's count
   */
  public function dashboard_getCount($uid = null) {
    return $this->call_method('facebook.dashboard.getCount',
      array('uid' => $uid));
  }

  /**
   * Get the global news for an app.
   * App Secret only.
   *
   * @param  news_ids  Array of fbids of news bundles // All if empty
   *
   * @return news      Array of news [{image,
   *                                   news => [{message,
   *                                             action_link => {text, href}}]}]
   */
  public function dashboard_getGlobalNews($news_ids = null) {
    return $this->call_method('facebook.dashboard.getGlobalNews',
      array('news_ids' => $news_ids));
  }

  /**
   * Get the news for a specific user.
   *
   * @param  news_ids  Array of fbids of news bundles // All if empty
   * @param  uid       The user ID of the user // Optional if session provided
   *
   * @return news      Array of news [{image,
   *                                   news => [{message,
   *                                             action_link => {text, href}}]}]
   */
  public function dashboard_getNews($news_ids = null, $uid = null) {
    return $this->call_method('facebook.dashboard.getNews',
      array('uid'      => $uid,
            'news_ids' => $news_ids));
  }

  /**
   * Increment the count for a specific user.
   *
   * @param  uid   The user ID of the user // Optional if session provided
   *
   * @return bool  Success
   */
  public function dashboard_incrementCount($uid = null) {
    return $this->call_method('facebook.dashboard.incrementCount',
      array('uid' => $uid));
  }

  /**
   * Add news for a series of users
   * App Secret only.
   *
   * @param  uids   User ids
   * @param  news   Array of news items [{message, action_link => {href, text}}]
   * @param  image  Valid image url
   *
   * @return ids    Associative array.  key => uid, value => fbid or false
   */
  public function dashboard_multiAddNews($uids, $news, $image = null) {
    return $this->call_method('facebook.dashboard.multiAddNews',
      array('uids'  => $uids,
            'news'  => $news,
            'image' => $image));
  }

  /**
   * Clear the news for a series of users
   * App Secret only.
   *
   * @param  ids   Associative array.
   *                 Key   => uid,
   *                 Value => array(news_ids) // All if empty
   *
   * @return ids   Associative array.  key => uid, value => true or false
   */
  public function dashboard_multiClearNews($ids) {
    return $this->call_method('facebook.dashboard.multiClearNews',
      array('ids' => $ids));
  }

  /**
   * Decrement the count for a series of users
   * App Secret only.
   *
   * @param  uids  Array of uids
   *
   * @return array  Key => uid
   *                Value => count for uid was decremented successfully.
   *                // If a count was already at 0, then decrementing fails.
   */
  public function dashboard_multiDecrementCount($uids) {
    return $this->call_method('facebook.dashboard.multiDecrementCount',
      array('uids' => $uids));
  }

  /**
   * Get the count for a series of users.
   * App Secret only.
   *
   * @param  uids    Array of uids
   *
   * @return counts  Associative array.
   *                   Key => uid,
   *                   Value => count
   */
  public function dashboard_multiGetCount($uids) {
    return $this->call_method('facebook.dashboard.multiGetCount',
      array('uids' => $uids));
  }

  /**
   * Get the news for a series of users.
   * App Secret only.
   *
   * @param  ids   Associative array.
   *                 Key   => uid,
   *                 Value => array(news_ids) // All if empty
   *
   * @return news  Associative array.
   *                 Key   => uid,
   *                 Value => [{image, news => [{message,
   *                                            action_link => {text,
   *                                                            href}}]}]
   */
  public function dashboard_multiGetNews($ids) {
    return $this->call_method('facebook.dashboard.multiGetNews',
      array('ids' => $ids));
  }

  /**
   * Increment the count for a series of users
   * App Secret only.
   *
   * @param  uids  Array of uids
   *
   * @return array  Key => uid
   *                Value => count for uid was incremented successfully.
   */
  public function dashboard_multiIncrementCount($uids) {
    return $this->call_method('facebook.dashboard.multiIncrementCount',
      array('uids' => $uids));
  }

  /**
   * Set the count for a series of users.
   * App Secret only.
   *
   * @param  ids   Associative array.
   *                 Key   => uid,
   *                 Value => count
   *
   * @return array  Key => uid
   *                Value => count for uid was set successfully.
   */
  public function dashboard_multiSetCount($ids) {
    return $this->call_method('facebook.dashboard.multiSetCount',
      array('ids' => $ids));
  }

  /**
   * Publish activity for a user.
   * Session Key only.
   *
   * @param  activity   one activity {message, action_link => {href, text}}
   *
   * @return fbid       ID of newly created activity
   */
  public function dashboard_publishActivity($activity) {
    return $this->call_method('facebook.dashboard.publishActivity',
      array('activity' => $activity));
  }

  /**
   * Remove the activity for a specific user.
   * @param  activity_ids  Array of fbids of bundles // Cannot be empty
   * @param  uid           The user ID of the user // Optional w/ session
   *
   * @return results   Array where key => news_id
   *                             value => successfully cleared
   */
  public function dashboard_removeActivity($activity_ids, $uid = null) {
    return $this->call_method('facebook.dashboard.removeActivity',
      array('uid' => $uid,
            'activity_ids' => $activity_ids));
  }

  /**
   * Set the count for a specific user.
   *
   * @param  count  The new count
    * @param  uid    The user ID of the user // Optional if session provided
   *
   * @return bool   Count was set successfly.
   */
  public function dashboard_setCount($count, $uid = null) {
    return $this->call_method('facebook.dashboard.setCount',
      array('uid' => $uid,
            'count' => $count));
  }

  /* UTILITY FUNCTIONS */

  /**
   * Calls the specified normal POST method with the specified parameters.
   *
   * @param string $method  Name of the Facebook method to invoke
   * @param array $params   A map of param names => param values
   * @param bool $force_read_only force the read-only endpoint
   *
   * @return mixed  Result of method call; this returns a reference to support
   *                'delayed returns' when in a batch context.
   *     See: http://wiki.developers.facebook.com/index.php/Using_batching_API
   */
  public function &call_method($method,
                               $params = array(),
                               $force_read_only = false) {
    if ($this->format) {
      $params['format'] = $this->format;
    }
    if (!$this->pending_batch()) {
      if ($this->call_as_apikey) {
        $params['call_as_apikey'] = $this->call_as_apikey;
      }
      $read_only = $force_read_only || $this->methodIsReadOnly($method);
      $server_addr = ($read_only) ? $this->read_server_addr
                                  : $this->server_addr;
      $data = $this->post_request($method, $params, $server_addr);
      $this->rawData = $data;
      $result = $this->convert_result($data, $method, $params);
      if (is_array($result) && isset($result['error_code'])) {
        throw new FacebookRestClientException($result['error_msg'],
                                              $result['error_code']);
      }
    } else {
      $result = null;
      $batch_item = array('m' => $method, 'p' => $params, 'r' => & $result);
      $this->batch_queue[] = $batch_item;
      if (!$this->methodIsReadOnly($method)) {
        $this->pending_batch_is_read_only = false;
      }
    }

    return $result;
  }

  protected function convert_result($data, $method, $params) {
    $is_xml = (empty($params['format']) ||
               strtolower($params['format']) != 'json');
    return ($is_xml) ? $this->convert_xml_to_result($data, $method, $params)
                     : json_decode($data, true);
  }

  /**
   * Change the response format
   *
   * @param string $format The response format (json, xml)
   */
  public function setFormat($format) {
    $this->format = $format;
    return $this;
  }

  /**
   * get the current response serialization format
   *
   * @return string 'xml', 'json', or null (which means 'xml')
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * Returns the raw JSON or XML output returned by the server in the most
   * recent API call.
   *
   * @return string
   */
   public function getRawData() {
     return $this->rawData;
   }

   /**
    * Change the server address
    *
    * @param string $server_addr New server address
    */
   public function setServerAddress($server_addr) {
      $this->server_addr = $this->photo_server_addr = $server_addr;
   }

  /**
   * Calls the specified file-upload POST method with the specified parameters
   *
   * @param string $method Name of the Facebook method to invoke
   * @param array  $params A map of param names => param values
   * @param string $file   A path to the file to upload (required)
   *
   * @return array A dictionary representing the response.
   */
  public function call_upload_method($method, $params, $file, $server_addr = null) {
    if (!$this->pending_batch()) {
      if (!file_exists($file)) {
        $code =
          FacebookAPIErrorCodes::API_EC_PARAM;
        $description = FacebookAPIErrorCodes::$api_error_descriptions[$code];
        throw new FacebookRestClientException($description, $code);
      }

      if ($this->format) {
        $params['format'] = $this->format;
      }
      $data = $this->post_upload_request($method,
                                         $params,
                                         $file,
                                         $server_addr);
      $result = $this->convert_result($data, $method, $params);

      if (is_array($result) && isset($result['error_code'])) {
        throw new FacebookRestClientException($result['error_msg'],
                                              $result['error_code']);
      }
    }
    else {
      $code =
        FacebookAPIErrorCodes::API_EC_BATCH_METHOD_NOT_ALLOWED_IN_BATCH_MODE;
      $description = FacebookAPIErrorCodes::$api_error_descriptions[$code];
      throw new FacebookRestClientException($description, $code);
    }

    return $result;
  }

  protected function convert_xml_to_result($xml, $method, $params) {
    $sxml = simplexml_load_string($xml);
    $result = self::convert_simplexml_to_array($sxml);

    if (!empty($GLOBALS['facebook_config']['debug'])) {
      // output the raw xml and its corresponding php object, for debugging:
      print '<div style="margin: 10px 30px; padding: 5px; border: 2px solid black; background: gray; color: white; font-size: 12px; font-weight: bold;">';
      $this->cur_id++;
      print $this->cur_id . ': Called ' . $method . ', show ' .
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'params\');">Params</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'xml\');">XML</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'sxml\');">SXML</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'php\');">PHP</a>';
      print '<pre id="params'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($params, true).'</pre>';
      print '<pre id="xml'.$this->cur_id.'" style="display: none; overflow: auto;">'.htmlspecialchars($xml).'</pre>';
      print '<pre id="php'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($result, true).'</pre>';
      print '<pre id="sxml'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($sxml, true).'</pre>';
      print '</div>';
    }
    return $result;
  }

  protected function finalize_params($method, $params) {
    list($get, $post) = $this->add_standard_params($method, $params);
    // we need to do this before signing the params
    $this->convert_array_values_to_json($post);
    $post['sig'] = Facebook::generate_sig(array_merge($get, $post),
                                          $this->secret);
    return array($get, $post);
  }

  private function convert_array_values_to_json(&$params) {
    foreach ($params as $key => &$val) {
      if (is_array($val)) {
        $val = json_encode($val);
      }
    }
  }

  /**
   * Add the generally required params to our request.
   * Params method, api_key, and v should be sent over as get.
   */
  private function add_standard_params($method, $params) {
    $post = $params;
    $get = array();
    if ($this->call_as_apikey) {
      $get['call_as_apikey'] = $this->call_as_apikey;
    }
    if ($this->using_session_secret) {
      $get['ss'] = '1';
    }

    $get['method'] = $method;
    $get['session_key'] = $this->session_key;
    $get['api_key'] = $this->api_key;
    $post['call_id'] = microtime(true);
    if ($post['call_id'] <= $this->last_call_id) {
      $post['call_id'] = $this->last_call_id + 0.001;
    }
    $this->last_call_id = $post['call_id'];
    if (isset($post['v'])) {
      $get['v'] = $post['v'];
      unset($post['v']);
    } else {
      $get['v'] = '1.0';
    }
    if (isset($this->use_ssl_resources)) {
      $post['return_ssl_resources'] = (bool) $this->use_ssl_resources;
    }
    return array($get, $post);
  }

  private function create_url_string($params) {
    $post_params = array();
    foreach ($params as $key => &$val) {
      $post_params[] = $key.'='.urlencode($val);
    }
    return implode('&', $post_params);
  }

  private function run_multipart_http_transaction($method, $params, $file, $server_addr) {

    // the format of this message is specified in RFC1867/RFC1341.
    // we add twenty pseudo-random digits to the end of the boundary string.
    $boundary = '--------------------------FbMuLtIpArT' .
                sprintf("%010d", mt_rand()) .
                sprintf("%010d", mt_rand());
    $content_type = 'multipart/form-data; boundary=' . $boundary;
    // within the message, we prepend two extra hyphens.
    $delimiter = '--' . $boundary;
    $close_delimiter = $delimiter . '--';
    $content_lines = array();
    foreach ($params as $key => &$val) {
      $content_lines[] = $delimiter;
      $content_lines[] = 'Content-Disposition: form-data; name="' . $key . '"';
      $content_lines[] = '';
      $content_lines[] = $val;
    }
    // now add the file data
    $content_lines[] = $delimiter;
    $content_lines[] =
      'Content-Disposition: form-data; filename="' . $file . '"';
    $content_lines[] = 'Content-Type: application/octet-stream';
    $content_lines[] = '';
    $content_lines[] = file_get_contents($file);
    $content_lines[] = $close_delimiter;
    $content_lines[] = '';
    $content = implode("\r\n", $content_lines);
    return $this->run_http_post_transaction($content_type, $content, $server_addr);
  }

  public function post_request($method, $params, $server_addr) {
    list($get, $post) = $this->finalize_params($method, $params);
    $post_string = $this->create_url_string($post);
    $get_string = $this->create_url_string($get);
    $url_with_get = $server_addr . '?' . $get_string;
    if ($this->use_curl_if_available && function_exists('curl_init')) {
      $useragent = 'Facebook API PHP5 Client 1.1 (curl) ' . phpversion();
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url_with_get);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      $result = $this->curl_exec($ch);
      curl_close($ch);
    } else {
      $content_type = 'application/x-www-form-urlencoded';
      $content = $post_string;
      $result = $this->run_http_post_transaction($content_type,
                                                 $content,
                                                 $url_with_get);
    }
    return $result;
  }

  /**
   * execute a curl transaction -- this exists mostly so subclasses can add
   * extra options and/or process the response, if they wish.
   *
   * @param resource $ch a curl handle
   */
  protected function curl_exec($ch) {
      $result = curl_exec($ch);
      return $result;
  }

  protected function post_upload_request($method, $params, $file, $server_addr = null) {
    $server_addr = $server_addr ? $server_addr : $this->server_addr;
    list($get, $post) = $this->finalize_params($method, $params);
    $get_string = $this->create_url_string($get);
    $url_with_get = $server_addr . '?' . $get_string;
    if ($this->use_curl_if_available && function_exists('curl_init')) {
      // prepending '@' causes cURL to upload the file; the key is ignored.
      $post['_file'] = '@' . $file;
      $useragent = 'Facebook API PHP5 Client 1.1 (curl) ' . phpversion();
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url_with_get);
      // this has to come before the POSTFIELDS set!
      curl_setopt($ch, CURLOPT_POST, 1);
      // passing an array gets curl to use the multipart/form-data content type
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
      $result = $this->curl_exec($ch);
      curl_close($ch);
    } else {
      $result = $this->run_multipart_http_transaction($method, $post,
                                                      $file, $url_with_get);
    }
    return $result;
  }

  private function run_http_post_transaction($content_type, $content, $server_addr) {

    $user_agent = 'Facebook API PHP5 Client 1.1 (non-curl) ' . phpversion();
    $content_length = strlen($content);
    $context =
      array('http' =>
              array('method' => 'POST',
                    'user_agent' => $user_agent,
                    'header' => 'Content-Type: ' . $content_type . "\r\n" .
                                'Content-Length: ' . $content_length,
                    'content' => $content));
    $context_id = stream_context_create($context);
    $sock = fopen($server_addr, 'r', false, $context_id);

    $result = '';
    if ($sock) {
      while (!feof($sock)) {
        $result .= fgets($sock, 4096);
      }
      fclose($sock);
    }
    return $result;
  }

  public static function convert_simplexml_to_array($sxml) {
    $arr = array();
    if ($sxml) {
      foreach ($sxml as $k => $v) {
        if ($sxml['list']) {
          if (isset($v['key'])) {
            $arr[(string)$v['key']] = self::convert_simplexml_to_array($v);
          } else {
            $arr[] = self::convert_simplexml_to_array($v);
          }
        } else {
          $arr[$k] = self::convert_simplexml_to_array($v);
        }
      }
    }
    if (sizeof($arr) > 0) {
      return $arr;
    } else {
      return (string)$sxml;
    }
  }

  protected function get_uid($uid) {
    return $uid ? $uid : $this->user;
  }

  static public function methodIsReadOnly($method) {
    // Until this is fully deployed, fail fast:
    return false;

    static $READ_ONLY_CALLS =
      array('admin_getallocation' => 1,
            'admin_getappproperties' => 1,
            'admin_getbannedusers' => 1,
            'admin_getlivestreamvialink' => 1,
            'admin_getmetrics' => 1,
            'admin_getrestrictioninfo' => 1,
            'application_getpublicinfo' => 1,
            'auth_getapppublickey' => 1,
            'auth_getsession' => 1,
            'auth_getsignedpublicsessiondata' => 1,
            'comments_get' => 1,
            'connect_getunconnectedfriendscount' => 1,
            'dashboard_getactivity' => 1,
            'dashboard_getcount' => 1,
            'dashboard_getglobalnews' => 1,
            'dashboard_getnews' => 1,
            'dashboard_multigetcount' => 1,
            'dashboard_multigetnews' => 1,
            'data_getcookies' => 1,
            'events_get' => 1,
            'events_getmembers' => 1,
            'fbml_getcustomtags' => 1,
            'feed_getappfriendstories' => 1,
            'feed_getregisteredtemplatebundlebyid' => 1,
            'feed_getregisteredtemplatebundles' => 1,
            'fql_multiquery' => 1,
            'fql_query' => 1,
            'friends_arefriends' => 1,
            'friends_get' => 1,
            'friends_getappusers' => 1,
            'friends_getlists' => 1,
            'friends_getmutualfriends' => 1,
            'gifts_get' => 1,
            'groups_get' => 1,
            'groups_getmembers' => 1,
            'intl_gettranslations' => 1,
            'links_get' => 1,
            'notes_get' => 1,
            'notifications_get' => 1,
            'pages_getinfo' => 1,
            'pages_isadmin' => 1,
            'pages_isappadded' => 1,
            'pages_isfan' => 1,
            'permissions_checkavailableapiaccess' => 1,
            'permissions_checkgrantedapiaccess' => 1,
            'photos_get' => 1,
            'photos_getalbums' => 1,
            'photos_gettags' => 1,
            'profile_getinfo' => 1,
            'profile_getinfooptions' => 1,
            'stream_getcomments' => 1,
            'stream_getfilters' => 1,
            'users_getinfo' => 1,
            'users_getloggedinuser' => 1,
            'users_getstandardinfo' => 1,
            'users_hasapppermission' => 1,
            'users_isappuser' => 1,
            'users_isverified' => 1,
            'video_getuploadlimits' => 1);

    if (substr($method, 0, 9) == 'facebook.') {
      $method = substr($method, 9);
    }
    $method = strtolower(str_replace('.', '_', $method));
    return isset($READ_ONLY_CALLS[$method]);
  }
}


class FacebookRestClientException extends Exception {
}

// Supporting methods and values------

/**
 * Error codes and descriptions for the Facebook API.
 */

class FacebookAPIErrorCodes {

  const API_EC_SUCCESS = 0;

  /*
   * GENERAL ERRORS
   */
  const API_EC_UNKNOWN = 1;
  const API_EC_SERVICE = 2;
  const API_EC_METHOD = 3;
  const API_EC_TOO_MANY_CALLS = 4;
  const API_EC_BAD_IP = 5;
  const API_EC_HOST_API = 6;
  const API_EC_HOST_UP = 7;
  const API_EC_SECURE = 8;
  const API_EC_RATE = 9;
  const API_EC_PERMISSION_DENIED = 10;
  const API_EC_DEPRECATED = 11;
  const API_EC_VERSION = 12;
  const API_EC_INTERNAL_FQL_ERROR = 13;
  const API_EC_HOST_PUP = 14;
  const API_EC_SESSION_SECRET_NOT_ALLOWED = 15;
  const API_EC_HOST_READONLY = 16;

  /*
   * PARAMETER ERRORS
   */
  const API_EC_PARAM = 100;
  const API_EC_PARAM_API_KEY = 101;
  const API_EC_PARAM_SESSION_KEY = 102;
  const API_EC_PARAM_CALL_ID = 103;
  const API_EC_PARAM_SIGNATURE = 104;
  const API_EC_PARAM_TOO_MANY = 105;
  const API_EC_PARAM_USER_ID = 110;
  const API_EC_PARAM_USER_FIELD = 111;
  const API_EC_PARAM_SOCIAL_FIELD = 112;
  const API_EC_PARAM_EMAIL = 113;
  const API_EC_PARAM_USER_ID_LIST = 114;
  const API_EC_PARAM_FIELD_LIST = 115;
  const API_EC_PARAM_ALBUM_ID = 120;
  const API_EC_PARAM_PHOTO_ID = 121;
  const API_EC_PARAM_FEED_PRIORITY = 130;
  const API_EC_PARAM_CATEGORY = 140;
  const API_EC_PARAM_SUBCATEGORY = 141;
  const API_EC_PARAM_TITLE = 142;
  const API_EC_PARAM_DESCRIPTION = 143;
  const API_EC_PARAM_BAD_JSON = 144;
  const API_EC_PARAM_BAD_EID = 150;
  const API_EC_PARAM_UNKNOWN_CITY = 151;
  const API_EC_PARAM_BAD_PAGE_TYPE = 152;
  const API_EC_PARAM_BAD_LOCALE = 170;
  const API_EC_PARAM_BLOCKED_NOTIFICATION = 180;

  /*
   * USER PERMISSIONS ERRORS
   */
  const API_EC_PERMISSION = 200;
  const API_EC_PERMISSION_USER = 210;
  const API_EC_PERMISSION_NO_DEVELOPERS = 211;
  const API_EC_PERMISSION_OFFLINE_ACCESS = 212;
  const API_EC_PERMISSION_ALBUM = 220;
  const API_EC_PERMISSION_PHOTO = 221;
  const API_EC_PERMISSION_MESSAGE = 230;
  const API_EC_PERMISSION_OTHER_USER = 240;
  const API_EC_PERMISSION_STATUS_UPDATE = 250;
  const API_EC_PERMISSION_PHOTO_UPLOAD = 260;
  const API_EC_PERMISSION_VIDEO_UPLOAD = 261;
  const API_EC_PERMISSION_SMS = 270;
  const API_EC_PERMISSION_CREATE_LISTING = 280;
  const API_EC_PERMISSION_CREATE_NOTE = 281;
  const API_EC_PERMISSION_SHARE_ITEM = 282;
  const API_EC_PERMISSION_EVENT = 290;
  const API_EC_PERMISSION_LARGE_FBML_TEMPLATE = 291;
  const API_EC_PERMISSION_LIVEMESSAGE = 292;
  const API_EC_PERMISSION_CREATE_EVENT = 296;
  const API_EC_PERMISSION_RSVP_EVENT = 299;

  /*
   * DATA EDIT ERRORS
   */
  const API_EC_EDIT = 300;
  const API_EC_EDIT_USER_DATA = 310;
  const API_EC_EDIT_PHOTO = 320;
  const API_EC_EDIT_ALBUM_SIZE = 321;
  const API_EC_EDIT_PHOTO_TAG_SUBJECT = 322;
  const API_EC_EDIT_PHOTO_TAG_PHOTO = 323;
  const API_EC_EDIT_PHOTO_FILE = 324;
  const API_EC_EDIT_PHOTO_PENDING_LIMIT = 325;
  const API_EC_EDIT_PHOTO_TAG_LIMIT = 326;
  const API_EC_EDIT_ALBUM_REORDER_PHOTO_NOT_IN_ALBUM = 327;
  const API_EC_EDIT_ALBUM_REORDER_TOO_FEW_PHOTOS = 328;

  const API_EC_MALFORMED_MARKUP = 329;
  const API_EC_EDIT_MARKUP = 330;

  const API_EC_EDIT_FEED_TOO_MANY_USER_CALLS = 340;
  const API_EC_EDIT_FEED_TOO_MANY_USER_ACTION_CALLS = 341;
  const API_EC_EDIT_FEED_TITLE_LINK = 342;
  const API_EC_EDIT_FEED_TITLE_LENGTH = 343;
  const API_EC_EDIT_FEED_TITLE_NAME = 344;
  const API_EC_EDIT_FEED_TITLE_BLANK = 345;
  const API_EC_EDIT_FEED_BODY_LENGTH = 346;
  const API_EC_EDIT_FEED_PHOTO_SRC = 347;
  const API_EC_EDIT_FEED_PHOTO_LINK = 348;

  const API_EC_EDIT_VIDEO_SIZE = 350;
  const API_EC_EDIT_VIDEO_INVALID_FILE = 351;
  const API_EC_EDIT_VIDEO_INVALID_TYPE = 352;
  const API_EC_EDIT_VIDEO_FILE = 353;

  const API_EC_EDIT_FEED_TITLE_ARRAY = 360;
  const API_EC_EDIT_FEED_TITLE_PARAMS = 361;
  const API_EC_EDIT_FEED_BODY_ARRAY = 362;
  const API_EC_EDIT_FEED_BODY_PARAMS = 363;
  const API_EC_EDIT_FEED_PHOTO = 364;
  const API_EC_EDIT_FEED_TEMPLATE = 365;
  const API_EC_EDIT_FEED_TARGET = 366;
  const API_EC_EDIT_FEED_MARKUP = 367;

  /**
   * SESSION ERRORS
   */
  const API_EC_SESSION_TIMED_OUT = 450;
  const API_EC_SESSION_METHOD = 451;
  const API_EC_SESSION_INVALID = 452;
  const API_EC_SESSION_REQUIRED = 453;
  const API_EC_SESSION_REQUIRED_FOR_SECRET = 454;
  const API_EC_SESSION_CANNOT_USE_SESSION_SECRET = 455;


  /**
   * FQL ERRORS
   */
  const FQL_EC_UNKNOWN_ERROR = 600;
  const FQL_EC_PARSER = 601; // backwards compatibility
  const FQL_EC_PARSER_ERROR = 601;
  const FQL_EC_UNKNOWN_FIELD = 602;
  const FQL_EC_UNKNOWN_TABLE = 603;
  const FQL_EC_NOT_INDEXABLE = 604; // backwards compatibility
  const FQL_EC_NO_INDEX = 604;
  const FQL_EC_UNKNOWN_FUNCTION = 605;
  const FQL_EC_INVALID_PARAM = 606;
  const FQL_EC_INVALID_FIELD = 607;
  const FQL_EC_INVALID_SESSION = 608;
  const FQL_EC_UNSUPPORTED_APP_TYPE = 609;
  const FQL_EC_SESSION_SECRET_NOT_ALLOWED = 610;
  const FQL_EC_DEPRECATED_TABLE = 611;
  const FQL_EC_EXTENDED_PERMISSION = 612;
  const FQL_EC_RATE_LIMIT_EXCEEDED = 613;
  const FQL_EC_UNRESOLVED_DEPENDENCY = 614;
  const FQL_EC_INVALID_SEARCH = 615;
  const FQL_EC_CONTAINS_ERROR = 616;

  const API_EC_REF_SET_FAILED = 700;

  /**
   * DATA STORE API ERRORS
   */
  const API_EC_DATA_UNKNOWN_ERROR = 800;
  const API_EC_DATA_INVALID_OPERATION = 801;
  const API_EC_DATA_QUOTA_EXCEEDED = 802;
  const API_EC_DATA_OBJECT_NOT_FOUND = 803;
  const API_EC_DATA_OBJECT_ALREADY_EXISTS = 804;
  const API_EC_DATA_DATABASE_ERROR = 805;
  const API_EC_DATA_CREATE_TEMPLATE_ERROR = 806;
  const API_EC_DATA_TEMPLATE_EXISTS_ERROR = 807;
  const API_EC_DATA_TEMPLATE_HANDLE_TOO_LONG = 808;
  const API_EC_DATA_TEMPLATE_HANDLE_ALREADY_IN_USE = 809;
  const API_EC_DATA_TOO_MANY_TEMPLATE_BUNDLES = 810;
  const API_EC_DATA_MALFORMED_ACTION_LINK = 811;
  const API_EC_DATA_TEMPLATE_USES_RESERVED_TOKEN = 812;

  /*
   * APPLICATION INFO ERRORS
   */
  const API_EC_NO_SUCH_APP = 900;

  /*
   * BATCH ERRORS
   */
  const API_EC_BATCH_TOO_MANY_ITEMS = 950;
  const API_EC_BATCH_ALREADY_STARTED = 951;
  const API_EC_BATCH_NOT_STARTED = 952;
  const API_EC_BATCH_METHOD_NOT_ALLOWED_IN_BATCH_MODE = 953;

  /*
   * EVENT API ERRORS
   */
  const API_EC_EVENT_INVALID_TIME = 1000;
  const API_EC_EVENT_NAME_LOCKED  = 1001;

  /*
   * INFO BOX ERRORS
   */
  const API_EC_INFO_NO_INFORMATION = 1050;
  const API_EC_INFO_SET_FAILED = 1051;

  /*
   * LIVEMESSAGE API ERRORS
   */
  const API_EC_LIVEMESSAGE_SEND_FAILED = 1100;
  const API_EC_LIVEMESSAGE_EVENT_NAME_TOO_LONG = 1101;
  const API_EC_LIVEMESSAGE_MESSAGE_TOO_LONG = 1102;

  /*
   * PAYMENTS API ERRORS
   */
  const API_EC_PAYMENTS_UNKNOWN = 1150;
  const API_EC_PAYMENTS_APP_INVALID = 1151;
  const API_EC_PAYMENTS_DATABASE = 1152;
  const API_EC_PAYMENTS_PERMISSION_DENIED = 1153;
  const API_EC_PAYMENTS_APP_NO_RESPONSE = 1154;
  const API_EC_PAYMENTS_APP_ERROR_RESPONSE = 1155;
  const API_EC_PAYMENTS_INVALID_ORDER = 1156;
  const API_EC_PAYMENTS_INVALID_PARAM = 1157;
  const API_EC_PAYMENTS_INVALID_OPERATION = 1158;
  const API_EC_PAYMENTS_PAYMENT_FAILED = 1159;
  const API_EC_PAYMENTS_DISABLED = 1160;

  /*
   * CONNECT SESSION ERRORS
   */
  const API_EC_CONNECT_FEED_DISABLED = 1300;

  /*
   * Platform tag bundles errors
   */
  const API_EC_TAG_BUNDLE_QUOTA = 1400;

  /*
   * SHARE
   */
  const API_EC_SHARE_BAD_URL = 1500;

  /*
   * NOTES
   */
  const API_EC_NOTE_CANNOT_MODIFY = 1600;

  /*
   * COMMENTS
   */
  const API_EC_COMMENTS_UNKNOWN = 1700;
  const API_EC_COMMENTS_POST_TOO_LONG = 1701;
  const API_EC_COMMENTS_DB_DOWN = 1702;
  const API_EC_COMMENTS_INVALID_XID = 1703;
  const API_EC_COMMENTS_INVALID_UID = 1704;
  const API_EC_COMMENTS_INVALID_POST = 1705;
  const API_EC_COMMENTS_INVALID_REMOVE = 1706;

  /*
   * GIFTS
   */
  const API_EC_GIFTS_UNKNOWN = 1900;

  /*
   * APPLICATION MORATORIUM ERRORS
   */
  const API_EC_DISABLED_ALL = 2000;
  const API_EC_DISABLED_STATUS = 2001;
  const API_EC_DISABLED_FEED_STORIES = 2002;
  const API_EC_DISABLED_NOTIFICATIONS = 2003;
  const API_EC_DISABLED_REQUESTS = 2004;
  const API_EC_DISABLED_EMAIL = 2005;

  /**
   * This array is no longer maintained; to view the description of an error
   * code, please look at the message element of the API response or visit
   * the developer wiki at http://wiki.developers.facebook.com/.
   */
  public static $api_error_descriptions = array(
      self::API_EC_SUCCESS           => 'Success',
      self::API_EC_UNKNOWN           => 'An unknown error occurred',
      self::API_EC_SERVICE           => 'Service temporarily unavailable',
      self::API_EC_METHOD            => 'Unknown method',
      self::API_EC_TOO_MANY_CALLS    => 'Application request limit reached',
      self::API_EC_BAD_IP            => 'Unauthorized source IP address',
      self::API_EC_PARAM             => 'Invalid parameter',
      self::API_EC_PARAM_API_KEY     => 'Invalid API key',
      self::API_EC_PARAM_SESSION_KEY => 'Session key invalid or no longer valid',
      self::API_EC_PARAM_CALL_ID     => 'Call_id must be greater than previous',
      self::API_EC_PARAM_SIGNATURE   => 'Incorrect signature',
      self::API_EC_PARAM_USER_ID     => 'Invalid user id',
      self::API_EC_PARAM_USER_FIELD  => 'Invalid user info field',
      self::API_EC_PARAM_SOCIAL_FIELD => 'Invalid user field',
      self::API_EC_PARAM_USER_ID_LIST => 'Invalid user id list',
      self::API_EC_PARAM_FIELD_LIST => 'Invalid field list',
      self::API_EC_PARAM_ALBUM_ID    => 'Invalid album id',
      self::API_EC_PARAM_BAD_EID     => 'Invalid eid',
      self::API_EC_PARAM_UNKNOWN_CITY => 'Unknown city',
      self::API_EC_PERMISSION        => 'Permissions error',
      self::API_EC_PERMISSION_USER   => 'User not visible',
      self::API_EC_PERMISSION_NO_DEVELOPERS  => 'Application has no developers',
      self::API_EC_PERMISSION_ALBUM  => 'Album not visible',
      self::API_EC_PERMISSION_PHOTO  => 'Photo not visible',
      self::API_EC_PERMISSION_EVENT  => 'Creating and modifying events required the extended permission create_event',
      self::API_EC_PERMISSION_RSVP_EVENT => 'RSVPing to events required the extended permission rsvp_event',
      self::API_EC_EDIT_ALBUM_SIZE   => 'Album is full',
      self::FQL_EC_PARSER            => 'FQL: Parser Error',
      self::FQL_EC_UNKNOWN_FIELD     => 'FQL: Unknown Field',
      self::FQL_EC_UNKNOWN_TABLE     => 'FQL: Unknown Table',
      self::FQL_EC_NOT_INDEXABLE     => 'FQL: Statement not indexable',
      self::FQL_EC_UNKNOWN_FUNCTION  => 'FQL: Attempted to call unknown function',
      self::FQL_EC_INVALID_PARAM     => 'FQL: Invalid parameter passed in',
      self::API_EC_DATA_UNKNOWN_ERROR => 'Unknown data store API error',
      self::API_EC_DATA_INVALID_OPERATION => 'Invalid operation',
      self::API_EC_DATA_QUOTA_EXCEEDED => 'Data store allowable quota was exceeded',
      self::API_EC_DATA_OBJECT_NOT_FOUND => 'Specified object cannot be found',
      self::API_EC_DATA_OBJECT_ALREADY_EXISTS => 'Specified object already exists',
      self::API_EC_DATA_DATABASE_ERROR => 'A database error occurred. Please try again',
      self::API_EC_BATCH_ALREADY_STARTED => 'begin_batch already called, please make sure to call end_batch first',
      self::API_EC_BATCH_NOT_STARTED => 'end_batch called before begin_batch',
      self::API_EC_BATCH_METHOD_NOT_ALLOWED_IN_BATCH_MODE => 'This method is not allowed in batch mode'
  );
}

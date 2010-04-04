<?php
// Copyright 2004-2009 Facebook. All Rights Reserved.
//
// +---------------------------------------------------------------------------+
// | Facebook Platform PHP5 client                                             |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007 Facebook, Inc.                                         |
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

include_once 'facebookapi_php5_restlib.php';

define('FACEBOOK_API_VALIDATION_ERROR', 1);
class Facebook {
  public $api_client;
  public $api_key;
  public $secret;
  public $generate_session_secret;
  public $session_expires;

  public $fb_params;
  public $user;
  public $profile_user;
  public $canvas_user;
  public $ext_perms = array();
  protected $base_domain;

  /*
   * Create a Facebook client like this:
   *
   * $fb = new Facebook(API_KEY, SECRET);
   *
   * This will automatically pull in any parameters, validate them against the
   * session signature, and chuck them in the public $fb_params member variable.
   *
   * @param api_key                  your Developer API key
   * @param secret                   your Developer API secret
   * @param generate_session_secret  whether to automatically generate a session
   *                                 if the user doesn't have one, but
   *                                 there is an auth token present in the url,
   */
  public function __construct($api_key, $secret, $generate_session_secret=false) {
    $this->api_key                 = $api_key;
    $this->secret                  = $secret;
    $this->generate_session_secret = $generate_session_secret;
    $this->api_client = new FacebookRestClient($api_key, $secret, null);
    $this->validate_fb_params();

    // Set the default user id for methods that allow the caller to
    // pass an explicit uid instead of using a session key.
    $defaultUser = null;
    if ($this->user) {
      $defaultUser = $this->user;
    } else if ($this->profile_user) {
      $defaultUser = $this->profile_user;
    } else if ($this->canvas_user) {
      $defaultUser = $this->canvas_user;
    }

    $this->api_client->set_user($defaultUser);


    if (isset($this->fb_params['friends'])) {
      $this->api_client->friends_list =
        array_filter(explode(',', $this->fb_params['friends']));
    }
    if (isset($this->fb_params['added'])) {
      $this->api_client->added = $this->fb_params['added'];
    }
    if (isset($this->fb_params['canvas_user'])) {
      $this->api_client->canvas_user = $this->fb_params['canvas_user'];
    }
  }

  /*
   * Validates that the parameters passed in were sent from Facebook. It does so
   * by validating that the signature matches one that could only be generated
   * by using your application's secret key.
   *
   * Facebook-provided parameters will come from $_POST, $_GET, or $_COOKIE,
   * in that order. $_POST and $_GET are always more up-to-date than cookies,
   * so we prefer those if they are available.
   *
   * For nitty-gritty details of when each of these is used, check out
   * http://wiki.developers.facebook.com/index.php/Verifying_The_Signature
   *
   * @param bool  resolve_auth_token  convert an auth token into a session
   */
  public function validate_fb_params($resolve_auth_token=true) {
    $this->fb_params = $this->get_valid_fb_params($_POST, 48 * 3600, 'fb_sig');

    // note that with preload FQL, it's possible to receive POST params in
    // addition to GET, so use a different prefix to differentiate them
    if (!$this->fb_params) {
      $fb_params = $this->get_valid_fb_params($_GET, 48 * 3600, 'fb_sig');
      $fb_post_params = $this->get_valid_fb_params($_POST, 48 * 3600, 'fb_post_sig');
      $this->fb_params = array_merge($fb_params, $fb_post_params);
    }

    // Okay, something came in via POST or GET
    if ($this->fb_params) {
      $user               = isset($this->fb_params['user']) ?
                            $this->fb_params['user'] : null;
      $this->profile_user = isset($this->fb_params['profile_user']) ?
                            $this->fb_params['profile_user'] : null;
      $this->canvas_user  = isset($this->fb_params['canvas_user']) ?
                            $this->fb_params['canvas_user'] : null;
      $this->base_domain  = isset($this->fb_params['base_domain']) ?
                            $this->fb_params['base_domain'] : null;
      $this->ext_perms    = isset($this->fb_params['ext_perms']) ?
                            explode(',', $this->fb_params['ext_perms'])
                            : array();

      if (isset($this->fb_params['session_key'])) {
        $session_key =  $this->fb_params['session_key'];
      } else if (isset($this->fb_params['profile_session_key'])) {
        $session_key =  $this->fb_params['profile_session_key'];
      } else {
        $session_key = null;
      }
      $expires     = isset($this->fb_params['expires']) ?
                     $this->fb_params['expires'] : null;
      $this->set_user($user,
                      $session_key,
                      $expires);
    }
    // if no Facebook parameters were found in the GET or POST variables,
    // then fall back to cookies, which may have cached user information
    // Cookies are also used to receive session data via the Javascript API
    else if ($cookies =
             $this->get_valid_fb_params($_COOKIE, null, $this->api_key)) {

      $base_domain_cookie = 'base_domain_' . $this->api_key;
      if (isset($_COOKIE[$base_domain_cookie])) {
        $this->base_domain = $_COOKIE[$base_domain_cookie];
      }

      // use $api_key . '_' as a prefix for the cookies in case there are
      // multiple facebook clients on the same domain.
      $expires = isset($cookies['expires']) ? $cookies['expires'] : null;
      $this->set_user($cookies['user'],
                      $cookies['session_key'],
                      $expires);
    }
    // finally, if we received no parameters, but the 'auth_token' GET var
    // is present, then we are in the middle of auth handshake,
    // so go ahead and create the session
    else if ($resolve_auth_token && isset($_GET['auth_token']) &&
             $session = $this->do_get_session($_GET['auth_token'])) {
      if ($this->generate_session_secret &&
          !empty($session['secret'])) {
        $session_secret = $session['secret'];
      }

      if (isset($session['base_domain'])) {
        $this->base_domain = $session['base_domain'];
      }

      $this->set_user($session['uid'],
                      $session['session_key'],
                      $session['expires'],
                      isset($session_secret) ? $session_secret : null);
    }

    return !empty($this->fb_params);
  }

  // Store a temporary session secret for the current session
  // for use with the JS client library
  public function promote_session() {
    try {
      $session_secret = $this->api_client->auth_promoteSession();
      if (!$this->in_fb_canvas()) {
        $this->set_cookies($this->user, $this->api_client->session_key, $this->session_expires, $session_secret);
      }
      return $session_secret;
    } catch (FacebookRestClientException $e) {
      // API_EC_PARAM means we don't have a logged in user, otherwise who
      // knows what it means, so just throw it.
      if ($e->getCode() != FacebookAPIErrorCodes::API_EC_PARAM) {
        throw $e;
      }
    }
  }

  public function do_get_session($auth_token) {
    try {
      return $this->api_client->auth_getSession($auth_token, $this->generate_session_secret);
    } catch (FacebookRestClientException $e) {
      // API_EC_PARAM means we don't have a logged in user, otherwise who
      // knows what it means, so just throw it.
      if ($e->getCode() != FacebookAPIErrorCodes::API_EC_PARAM) {
        throw $e;
      }
    }
  }

  // Invalidate the session currently being used, and clear any state associated
  // with it. Note that the user will still remain logged into Facebook.
  public function expire_session() {
    try {
      if ($this->api_client->auth_expireSession()) {
        $this->clear_cookie_state();
        return true;
      } else {
        return false;
      }
    } catch (Exception $e) {
      $this->clear_cookie_state();
    }
  }

  /** Logs the user out of all temporary application sessions as well as their
   * Facebook session.  Note this will only work if the user has a valid current
   * session with the application.
   *
   * @param string  $next  URL to redirect to upon logging out
   *
   */
   public function logout($next) {
    $logout_url = $this->get_logout_url($next);

    // Clear any stored state
    $this->clear_cookie_state();

    $this->redirect($logout_url);
  }

  /**
   *  Clears any persistent state stored about the user, including
   *  cookies and information related to the current session in the
   *  client.
   *
   */
  public function clear_cookie_state() {
    if (!$this->in_fb_canvas() && isset($_COOKIE[$this->api_key . '_user'])) {
       $cookies = array('user', 'session_key', 'expires', 'ss');
       foreach ($cookies as $name) {
         setcookie($this->api_key . '_' . $name,
                   false,
                   time() - 3600,
                   '',
                   $this->base_domain);
         unset($_COOKIE[$this->api_key . '_' . $name]);
       }
       setcookie($this->api_key, false, time() - 3600, '', $this->base_domain);
       unset($_COOKIE[$this->api_key]);
     }

     // now, clear the rest of the stored state
     $this->user = 0;
     $this->api_client->session_key = 0;
  }

  public function redirect($url) {
    if ($this->in_fb_canvas()) {
      echo '<fb:redirect url="' . $url . '"/>';
    } else if (preg_match('/^https?:\/\/([^\/]*\.)?facebook\.com(:\d+)?/i', $url)) {
      // make sure facebook.com url's load in the full frame so that we don't
      // get a frame within a frame.
      echo "<script type=\"text/javascript\">\ntop.location.href = \"$url\";\n</script>";
    } else {
      header('Location: ' . $url);
    }
    exit;
  }

  public function in_frame() {
    return isset($this->fb_params['in_canvas'])
        || isset($this->fb_params['in_iframe']);
  }
  public function in_fb_canvas() {
    return isset($this->fb_params['in_canvas']);
  }

  public function get_loggedin_user() {
    return $this->user;
  }

  public function get_canvas_user() {
    return $this->canvas_user;
  }

  public function get_profile_user() {
    return $this->profile_user;
  }

  public static function current_url() {
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  // require_add and require_install have been removed.
  // see http://developer.facebook.com/news.php?blog=1&story=116 for more details
  public function require_login($required_permissions = '') {
    $user = $this->get_loggedin_user();
    $has_permissions = true;

    if ($required_permissions) {
      $this->require_frame();
      $permissions = array_map('trim', explode(',', $required_permissions));
      foreach ($permissions as $permission) {
        if (!in_array($permission, $this->ext_perms)) {
          $has_permissions = false;
          break;
        }
      }
    }

    if ($user && $has_permissions) {
      return $user;
    }

    $this->redirect(
      $this->get_login_url(self::current_url(), $this->in_frame(),
                           $required_permissions));
  }

  public function require_frame() {
    if (!$this->in_frame()) {
      $this->redirect($this->get_login_url(self::current_url(), true));
    }
  }

  public static function get_facebook_url($subdomain='www') {
    return 'http://' . $subdomain . '.facebook.com';
  }

  public function get_install_url($next=null) {
    // this was renamed, keeping for compatibility's sake
    return $this->get_add_url($next);
  }

  public function get_add_url($next=null) {
    $page = self::get_facebook_url().'/add.php';
    $params = array('api_key' => $this->api_key);

    if ($next) {
      $params['next'] = $next;
    }

    return $page . '?' . http_build_query($params);
  }

  public function get_login_url($next, $canvas, $req_perms = '') {
    $page = self::get_facebook_url().'/login.php';
    $params = array('api_key'   => $this->api_key,
                    'v'         => '1.0',
                    'req_perms' => $req_perms);

    if ($next) {
      $params['next'] = $next;
    }
    if ($canvas) {
      $params['canvas'] = '1';
    }

    return $page . '?' . http_build_query($params);
  }

  public function get_logout_url($next) {
    $page = self::get_facebook_url().'/logout.php';
    $params = array('app_key'     => $this->api_key,
                    'session_key' => $this->api_client->session_key);

    if ($next) {
      $params['connect_next'] = 1;
      $params['next'] = $next;
    }

    return $page . '?' . http_build_query($params);
  }

  public function set_user($user, $session_key, $expires=null, $session_secret=null) {
    if (!$this->in_fb_canvas() && (!isset($_COOKIE[$this->api_key . '_user'])
                                   || $_COOKIE[$this->api_key . '_user'] != $user)) {
      $this->set_cookies($user, $session_key, $expires, $session_secret);
    }
    $this->user = $user;
    $this->api_client->session_key = $session_key;
    $this->session_expires = $expires;
  }

  public function set_cookies($user, $session_key, $expires=null, $session_secret=null) {
    $cookies = array();
    $cookies['user'] = $user;
    $cookies['session_key'] = $session_key;
    if ($expires != null) {
      $cookies['expires'] = $expires;
    }
    if ($session_secret != null) {
      $cookies['ss'] = $session_secret;
    }

    foreach ($cookies as $name => $val) {
      setcookie($this->api_key . '_' . $name, $val, (int)$expires, '', $this->base_domain);
      $_COOKIE[$this->api_key . '_' . $name] = $val;
    }
    $sig = self::generate_sig($cookies, $this->secret);
    setcookie($this->api_key, $sig, (int)$expires, '', $this->base_domain);
    $_COOKIE[$this->api_key] = $sig;

    if ($this->base_domain != null) {
      $base_domain_cookie = 'base_domain_' . $this->api_key;
      setcookie($base_domain_cookie, $this->base_domain, (int)$expires, '', $this->base_domain);
      $_COOKIE[$base_domain_cookie] = $this->base_domain;
    }
  }

  /**
   * Tries to undo the badness of magic quotes as best we can
   * @param     string   $val   Should come directly from $_GET, $_POST, etc.
   * @return    string   val without added slashes
   */
  public static function no_magic_quotes($val) {
    if (get_magic_quotes_gpc()) {
      return stripslashes($val);
    } else {
      return $val;
    }
  }

  /*
   * Get the signed parameters that were sent from Facebook. Validates the set
   * of parameters against the included signature.
   *
   * Since Facebook sends data to your callback URL via unsecured means, the
   * signature is the only way to make sure that the data actually came from
   * Facebook. So if an app receives a request at the callback URL, it should
   * always verify the signature that comes with against your own secret key.
   * Otherwise, it's possible for someone to spoof a request by
   * pretending to be someone else, i.e.:
   *      www.your-callback-url.com/?fb_user=10101
   *
   * This is done automatically by verify_fb_params.
   *
   * @param  assoc  $params     a full array of external parameters.
   *                            presumed $_GET, $_POST, or $_COOKIE
   * @param  int    $timeout    number of seconds that the args are good for.
   *                            Specifically good for forcing cookies to expire.
   * @param  string $namespace  prefix string for the set of parameters we want
   *                            to verify. i.e., fb_sig or fb_post_sig
   *
   * @return  assoc the subset of parameters containing the given prefix,
   *                and also matching the signature associated with them.
   *          OR    an empty array if the params do not validate
   */
  public function get_valid_fb_params($params, $timeout=null, $namespace='fb_sig') {
    $prefix = $namespace . '_';
    $prefix_len = strlen($prefix);
    $fb_params = array();
    if (empty($params)) {
      return array();
    }

    foreach ($params as $name => $val) {
      // pull out only those parameters that match the prefix
      // note that the signature itself ($params[$namespace]) is not in the list
      if (strpos($name, $prefix) === 0) {
        $fb_params[substr($name, $prefix_len)] = self::no_magic_quotes($val);
      }
    }

    // validate that the request hasn't expired. this is most likely
    // for params that come from $_COOKIE
    if ($timeout && (!isset($fb_params['time']) || time() - $fb_params['time'] > $timeout)) {
      return array();
    }

    // validate that the params match the signature
    $signature = isset($params[$namespace]) ? $params[$namespace] : null;
    if (!$signature || (!$this->verify_signature($fb_params, $signature))) {
      return array();
    }
    return $fb_params;
  }

  /**
   *  Validates the account that a user was trying to set up an
   *  independent account through Facebook Connect.
   *
   *  @param  user The user attempting to set up an independent account.
   *  @param  hash The hash passed to the reclamation URL used.
   *  @return bool True if the user is the one that selected the
   *               reclamation link.
   */
  public function verify_account_reclamation($user, $hash) {
    return $hash == md5($user . $this->secret);
  }

  /**
   * Validates that a given set of parameters match their signature.
   * Parameters all match a given input prefix, such as "fb_sig".
   *
   * @param $fb_params     an array of all Facebook-sent parameters,
   *                       not including the signature itself
   * @param $expected_sig  the expected result to check against
   */
  public function verify_signature($fb_params, $expected_sig) {
    return self::generate_sig($fb_params, $this->secret) == $expected_sig;
  }

  /**
   * Validate the given signed public session data structure with
   * public key of the app that
   * the session proof belongs to.
   *
   * @param $signed_data the session info that is passed by another app
   * @param string $public_key Optional public key of the app. If this
   *               is not passed, function will make an API call to get it.
   * return true if the session proof passed verification.
   */
  public function verify_signed_public_session_data($signed_data,
                                                    $public_key = null) {

    // If public key is not already provided, we need to get it through API
    if (!$public_key) {
      $public_key = $this->api_client->auth_getAppPublicKey(
        $signed_data['api_key']);
    }

    // Create data to verify
    $data_to_serialize = $signed_data;
    unset($data_to_serialize['sig']);
    $serialized_data = implode('_', $data_to_serialize);

    // Decode signature
    $signature = base64_decode($signed_data['sig']);
    $result = openssl_verify($serialized_data, $signature, $public_key,
                             OPENSSL_ALGO_SHA1);
    return $result == 1;
  }

  /*
   * Generate a signature using the application secret key.
   *
   * The only two entities that know your secret key are you and Facebook,
   * according to the Terms of Service. Since nobody else can generate
   * the signature, you can rely on it to verify that the information
   * came from Facebook.
   *
   * @param $params_array   an array of all Facebook-sent parameters,
   *                        NOT INCLUDING the signature itself
   * @param $secret         your app's secret key
   *
   * @return a hash to be checked against the signature provided by Facebook
   */
  public static function generate_sig($params_array, $secret) {
    $str = '';

    ksort($params_array);
    // Note: make sure that the signature parameter is not already included in
    //       $params_array.
    foreach ($params_array as $k=>$v) {
      $str .= "$k=$v";
    }
    $str .= $secret;

    return md5($str);
  }

  public function encode_validationError($summary, $message) {
    return json_encode(
               array('errorCode'    => FACEBOOK_API_VALIDATION_ERROR,
                     'errorTitle'   => $summary,
                     'errorMessage' => $message));
  }

  public function encode_multiFeedStory($feed, $next) {
    return json_encode(
               array('method'   => 'multiFeedStory',
                     'content'  =>
                     array('next' => $next,
                           'feed' => $feed)));
  }

  public function encode_feedStory($feed, $next) {
    return json_encode(
               array('method'   => 'feedStory',
                     'content'  =>
                     array('next' => $next,
                           'feed' => $feed)));
  }

  public function create_templatizedFeedStory($title_template, $title_data=array(),
                                    $body_template='', $body_data = array(), $body_general=null,
                                    $image_1=null, $image_1_link=null,
                                    $image_2=null, $image_2_link=null,
                                    $image_3=null, $image_3_link=null,
                                    $image_4=null, $image_4_link=null) {
    return array('title_template'=> $title_template,
                 'title_data'   => $title_data,
                 'body_template'=> $body_template,
                 'body_data'    => $body_data,
                 'body_general' => $body_general,
                 'image_1'      => $image_1,
                 'image_1_link' => $image_1_link,
                 'image_2'      => $image_2,
                 'image_2_link' => $image_2_link,
                 'image_3'      => $image_3,
                 'image_3_link' => $image_3_link,
                 'image_4'      => $image_4,
                 'image_4_link' => $image_4_link);
  }


}


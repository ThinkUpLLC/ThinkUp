<?php
/**
 *
 * webapp/plugins/youtube/controller/class.YouTubePluginConfigurationController.php
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * YouTube Plugin Configuration Controller
 *
 * This class gets the OAuth tokens from YouTube and saves them in the database
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class YouTubePluginConfigurationController extends PluginConfigurationController {

    public function __construct($owner) {
        parent::__construct($owner, 'youtube');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/youtube/view/account.index.tpl');
        $this->view_mgr->addHelp('youtube', 'userguide/settings/plugins/youtube');

        /* set option fields **/
        // client ID text field
        $name_field = array('name' => 'youtube_client_id', 'label' => 'Client ID', 'size'=>50);
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('youtube_client_id', 'A client ID is required to use YouTube.');

        // client secret text field
        $name_field = array('name' => 'youtube_client_secret', 'label' => 'Client secret', 'size'=>40);
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('youtube_client_secret',
        'A client secret is required to use YouTube.');

        // Three optional fields that will be useful for very active YouTubers
        $max_crawl_time_label = 'Max crawl time in minutes';
        $max_crawl_time = array('name' => 'max_crawl_time', 'label' => $max_crawl_time_label,
        'default_value' => '20', 'advanced'=>true, 'size' => 3);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $max_crawl_time);

        $developer_key_label = 'YouTube Developer Key';
        $developer_key = array('name' => 'developer_key', 'label' => $developer_key_label,
        'default_value' => '', 'advanced'=>true, 'size' => 40);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $developer_key);

        $comments_label = 'Maximum Comments to Collect';
        $comments = array('name' => 'max_comments', 'label' => $comments_label,
        'default_value' => '', 'advanced'=>true, 'size' => 5);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $comments);

        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('youtube', true); //get cached

        $plugin = new YouTubePlugin();
        if ($plugin->isConfigured()) {
            $this->setUpYouTubeInteractions($options);
            $this->addToView('is_configured', true);
        } else {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        }

        $this->addToView('thinkup_site_url', Utils::getApplicationURL());
        return $this->generateView();
    }

     /**
     * Add user auth link or process incoming auth requests.
     * @param array $options Plugin options array
     */
    protected function setUpYouTubeInteractions(array $options) {
        //get options
        $client_id = $options['youtube_client_id']->option_value;
        $client_secret = $options['youtube_client_secret']->option_value;

        //prep redirect URI
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $redirect_uri = urlencode(Utils::getApplicationURL() .'account/?p=youtube');

        //create OAuth link
        $oauth_link = "https://accounts.google.com/o/oauth2/auth?client_id=".$client_id.
        "&redirect_uri=".$redirect_uri.
        "&scope=https://www.googleapis.com/auth/youtube.readonly%20https://www.googleapis.com/auth/plus.me".
        "%20https://www.googleapis.com/auth/yt-analytics.readonly&response_type=code&access_type=offline".
        "&approval_prompt=force";
        $this->addToView('oauth_link', $oauth_link);

        // Google provided a code to get an access token
        if (isset($_GET['code'])) {

            $code = $_GET['code'];
            $crawler_plugin_registrar = new YouTubeCrawler(null, null, null, null, null);
            $tokens = $crawler_plugin_registrar->getOAuthTokens($client_id, $client_secret, $code, 'authorization_code',
            $redirect_uri);

            if (isset($tokens->error)) {
                $this->addErrorMessage("Oops! Something went wrong while obtaining OAuth tokens.<br>YouTube says \"".
                $tokens->error.".\" Please double-check your settings and try again.", 'authorization');
            } else {
                if (isset($tokens->access_token)){
                    // Get user data
                    // First we need to query the YouTube API for the users G+ ID
                    $youtube_api_accessor = new YouTubeAPIAccessor();
                    $fields = array( "part" => "contentDetails", "mine" => "true");
                    $gplus_user_id_query = $youtube_api_accessor->apiRequest('channels',
                    $tokens->access_token, $fields);
                    // The error we could get from this call is a forbidden error if something went wrong with
                    // authentication.
                    if(isset($gplus_user_id_query->error)){
                        if ($gplus_user_id_query->error->code == "401"
                        && $gplus_user_id_query->error->message == 'Unauthorized') {
                            $this->addErrorMessage("Oops! Looks like YouTube API access isn't turned on. ".
                            "<a href=\"http://code.google.com/apis/console#access\">In the Google APIs console</a>, ".
                            "in Services, flip the YouTube and YouTube analytics API Status switch to 'On' and try again
                            .", 'authorization');
                        } else {
                            $this->addErrorMessage("Oops! Something went wrong querying the YouTube API.<br>".
                            "Google says \"". $gplus_user_id_query->error->code.": ".
                            $gplus_user_id_query->error->message.
                            ".\" Please double-check your settings and try again.", 'authorization');
                        }
                    } else {
                        // We have should have the users G+ id so we now just need their username from the G+ API
                        $gplus_id = $gplus_user_id_query->items[0]->contentDetails->googlePlusUserId;
                        $gplus_api_accessor = new GooglePlusAPIAccessor();
                        if(isset($gplus_id)) {
                            $gplus_user = $gplus_api_accessor->apiRequest('people/'.$gplus_id, $tokens->access_token,
                            null);
                            if (isset($gplus_user->error)) {
                                if ($gplus_user->error->code == "403"
                                && $gplus_user->error->message == 'Access Not Configured') {
                                    $this->addErrorMessage("Oops! Looks like Google+ API access isn't turned on. ".
                                    "<a href=\"http://code.google.com/apis/console#access\">In the Google APIs ".
                                    "console</a> in Services, flip the Google+ API Status switch to 'On' and "
                                    . "try again.", 'authorization');
                                } else {
                                    $this->addErrorMessage("Oops! Something went wrong querying the Google+ API.<br>".
                                    "Google says \"". $gplus_user->error->code.": ".$gplus_user->error->message.
                                    ".\" Please double-check your settings and try again.", 'authorization');
                                }
                            } else {
                                if (isset($gplus_user->id) && isset($gplus_user->displayName)) {
                                    $gplus_user_id = $gplus_user->id;
                                    $gplus_username = $gplus_user->displayName;
                                    //Process tokens
                                    $this->saveAccessTokens($gplus_user_id, $gplus_username, $tokens->access_token,
                                    $tokens->refresh_token);
                                } else {
                                    $this->addErrorMessage("Oops! Something went wrong querying the Google+ API.<br>".
                                    "Google says \"". Utils::varDumpToString($gplus_user).
                                    ".\" Please double-check your settings and try again.", 'authorization');
                                }
                            }
                        }  else {
                            // It may be possible that the user has not linked their YouTube account to their G+ account
                            // so we might not get a G+ ID
                            $this->addErrorMessage("You don't have a Google+ ID associated with your YouTube account, ".
                            "go to YouTube and link your Google+ account to your YouTube account to use this plugin. ".
                            "For more information click <a href=https://www.thinkup.com/docs/userguide/settings/plugin".
                            "s/youtube.html>here</a>", 'authorization');
                        }
                    }
                }
            }
        }

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'youtube');
        $this->addToView('owner_instances', $owner_instances);

    }

     /**
     * Save newly-acquired OAuth access tokens to application options.
     * @param str $gplus_user_id
     * @param str $gplus_username
     * @param str $access_token
     * @param str $refresh_token
     * @return void
     */
    protected function saveAccessTokens($gplus_user_id, $gplus_username, $access_token, $refresh_token) {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'youtube');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $access_token, $refresh_token);
                $this->addSuccessMessage("Success! Your YouTube account has been added to ThinkUp.", 'user_add');
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $access_token, $refresh_token);
                $this->addSuccessMessage("Success! You've reconnected your YouTube account. To connect a different ".
                "account, log out of YouTube in a different browser tab and try again.", 'user_add');
            }
        } else { //Instance does not exist
            $instance_dao->insert($gplus_user_id, $gplus_username, 'youtube');
            $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'youtube');
            $owner_instance_dao->insert(
            $this->owner->id,
            $instance->id, $access_token, $refresh_token);
            $this->addSuccessMessage("Success! Your YouTube account has been added to ThinkUp.", 'user_add');
        }

        if (!$user_dao->isUserInDB($gplus_user_id, 'youtube')) {
            $r = array('user_id'=>$gplus_user_id, 'user_name'=>$gplus_username,'full_name'=>$gplus_username,
            'avatar'=>'', 'location'=>'', 'description'=>'', 'url'=>'', 'is_verified'=>'', 'is_protected'=>'',
            'follower_count'=>0, 'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'',
            'last_post_id'=>'', 'network'=>'facebook' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
        $this->view_mgr->clear_all_cache();
    }

}


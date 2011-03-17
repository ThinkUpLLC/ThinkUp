<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/controller/class.TwitterAuthController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Twitter Auth Controller
 * Save the OAuth tokens for Twitter account authorization.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterAuthController extends ThinkUpAuthController {
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitter/view/auth.tpl');
        $this->setPageTitle('Authorizing Your Twitter Account');
        if (!isset($_GET['oauth_token']) || $_GET['oauth_token'] == '' ) {
            $this->addInfoMessage('No OAuth token specified.');
            $this->is_missing_param = true;
        }
        if (!isset($_SESSION['oauth_request_token_secret']) || $_SESSION['oauth_request_token_secret'] == '' ) {
            $this->addInfoMessage('Secret token not set.');
            $this->is_missing_param = true;
        }
    }

    public function authControl() {
        $msg = "";
        if (!$this->is_missing_param) {
            $request_token = $_GET['oauth_token'];
            $request_token_secret = $_SESSION['oauth_request_token_secret'];

            // get oauth values
            $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
            $options = $plugin_option_dao->getOptionsHash('twitter', true); //get cached

            $to = new TwitterOAuth($options['oauth_consumer_key']->option_value,
            $options['oauth_consumer_secret']->option_value, $request_token, $request_token_secret);

            $tok = $to->getAccessToken();

            if (isset($tok['oauth_token']) && isset($tok['oauth_token_secret'])) {
                $api = new TwitterAPIAccessorOAuth($tok['oauth_token'], $tok['oauth_token_secret'],
                $options['oauth_consumer_key']->option_value, $options['oauth_consumer_secret']->option_value,
                $options['num_twitter_errors']->option_value, $options['max_api_calls_per_crawl']->option_value, false);

                $u = $api->verifyCredentials();

                //echo "User ID: ". $u['user_id'];
                //echo "User name: ". $u['user_name'];
                $twitter_id = $u['user_id'];
                $tu = $u['user_name'];

                $od = DAOFactory::getDAO('OwnerDAO');
                $owner = $od->getByEmail($this->getLoggedInUser());

                if ($twitter_id > 0) {
                    $msg = "<h2 class=\"subhead\">Twitter authentication successful!</h2>";

                    $id = DAOFactory::getDAO('InstanceDAO');
                    $i = $id->getByUsername($tu);
                    $oid = DAOFactory::getDAO('OwnerInstanceDAO');

                    if (isset($i)) {
                        $msg .= "Instance already exists.<br />";

                        $oi = $oid->get($owner->id, $i->id);
                        if ($oi != null) {
                            $msg .= "Owner already has this instance, no insert  required.<br />";
                            if ($oid->updateTokens($owner->id, $i->id, $tok['oauth_token'],
                            $tok['oauth_token_secret'])) {
                                $msg .= "OAuth Tokens updated.";
                            } else {
                                $msg .= "OAuth Tokens NOT updated.";
                            }
                        } else {
                            if ($oid->insert($owner->id, $i->id, $tok['oauth_token'], $tok['oauth_token_secret'])) {
                                $msg .= "Added owner instance.<br />";
                            } else {
                                $msg .= "PROBLEM Did not add owner instance.<br />";
                            }
                        }

                    } else {
                        $msg .= "Instance does not exist.<br />";

                        $id->insert($twitter_id, $tu);
                        $msg .= "Created instance.<br />";

                        $i = $id->getByUsername($tu);
                        if ($oid->insert(
                        $owner->id,
                        $i->id,
                        $tok['oauth_token'],
                        $tok['oauth_token_secret'])) {
                            $msg .= "Created an owner instance.<br />";
                        } else {
                            $msg .= "Did NOT create an owner instance.<br />";
                        }
                    }
                }
            } else {
                $msg = "PROBLEM! Twitter authorization did not complete successfully. Check if your account already ".
                " exists. If not, please try again.";
            }
            $this->view_mgr->clear_all_cache();

            $config = Config::getInstance();
            $msg .= '<br /><br /><a href="'.$config->getValue('site_root_path').
        'account/index.php?p=twitter" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span 
        class="ui-icon ui-icon-circle-arrow-e"></span>Back to your account</a>';
            $this->addInfoMessage($msg);
        }
        return $this->generateView();
    }

}

<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
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
 */
/**
 * Facebook Plugin Configuration controller
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FacebookPluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;
    var $id;
    var $od;
    var $oid;
    /**
     * Constructor
     * @param Owner $owner
     * @return FacebookPluginConfigurationController
     */
    public function __construct($owner) {
        parent::__construct($owner, 'facebook');
        $this->disableCaching();
        $this->owner = $owner;
        $this->id = DAOFactory::getDAO('InstanceDAO');
        $this->od = DAOFactory::getDAO('OwnerDAO');
        $this->oid = DAOFactory::getDAO('OwnerInstanceDAO');
    }

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/facebook/view/facebook.account.index.tpl');

        /** set option fields **/
        // API Key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_key',
        'label'=>'Your Facebook API Key')); // add element
        $this->addPluginOptionHeader('facebook_api_key',
        'Facebook Configuration');
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_key',
        'The Facebook plugin requires a valid API Key.');

        // Application Secret text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_secret',
        'label'=>'Your Facebook Application Secret')); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_secret',
        'The Facebook plugin requires a valid Application Secret.');

        $status = self::processPageActions();
        $this->addToView("info", $status["info"]);
        $this->addToView("error", $status["error"]);
        $this->addToView("success", $status["success"]);

        $logger = Logger::getInstance();
        $user_pages = array();
        $owner_instances = $this->id->getByOwnerAndNetwork($this->owner, 'facebook');

        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('facebook', true); //get cached

        if (isset($options['facebook_api_key']) && isset($options['facebook_api_secret'])) {
            $api_key = $options['facebook_api_key']->option_value;
            $api_secret = $options['facebook_api_secret']->option_value;
            //echo "keys set: ".$api_key." ".$api_secret;
            $facebook = new Facebook($api_key, $api_secret);
            foreach ($owner_instances as $instance) {
                $crawler = new FacebookCrawler($instance, $facebook);
                $tokens = $this->oid->getOAuthTokens($instance->id);
                $session_key = $tokens['oauth_access_token'];
                if ($instance->network_user_id == $instance->network_viewer_id) {
                    $pages = $crawler->fetchPagesUserIsFanOf($instance->network_user_id, $session_key);
                    if ($pages) {
                        $keys = array_keys($pages);
                        foreach ($keys as $key) {
                            $pages[$key]["json"] = json_encode($pages[$key]);
                        }
                        $user_pages[$instance->network_user_id] = $pages;
                        $this->addToView('user_pages', $user_pages);
                    }
                }
            }
        } else {
            $this->addErrorMessage("Please set your Facebook API Key and Application Secret.");
        }

        $owner_instance_pages = $this->id->getByOwnerAndNetwork($this->owner, 'facebook page');
        $this->addToView('owner_instance_pages', $owner_instance_pages);

        $fbconnect_link = '<a href="#" onclick="FB.Connect.requireSession(); return false;" >
        <img id="fb_login_image" 
        src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect"/>
            </a>';
        $this->addToView('fbconnect_link', $fbconnect_link);
        $this->addToView('owner_instances', $owner_instances);
        if (isset($options['facebook_api_key'])) {
            $this->addToView('fb_api_key', $options['facebook_api_key']->option_value);
        }

        return $this->generateView();
    }

    protected function processPageActions() {
        $messages = array("error"=>'', "success"=>'', "info"=>'');

        //insert pages
        if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"])
        && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
            $page_data = json_decode(str_replace("\\", "", $_GET["facebook_page_id"]));
            $messages = self::insertPage($page_data->page_id, $_GET["viewer_id"], $_GET["owner_id"],
            $_GET["instance_id"], $page_data->name, $page_data->pic_square, $messages);
        }

        return $messages;
    }

    protected function insertPage($fb_page_id, $viewer_id, $owner_id, $existing_instance_id, $fb_page_name,
    $fb_page_avatar, $messages) {
        //check if instance exists
        $i = $this->id->getByUserAndViewerId($fb_page_id, $viewer_id, 'facebook');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_in_db = $user_dao->isUserInDB($fb_page_name, "facebook page");
        if ($i == null || !$user_in_db) {
            if ($i == null ) {
                $instance_id = $this->id->insert($fb_page_id, $fb_page_name, "facebook page", $viewer_id);
                if ($instance_id) {
                    $messages["success"] .= "Instance ID ".$instance_id.
                " created successfully for Facebook page ID $fb_page_id.";
                }
                $tokens = $this->oid->getOAuthTokens($existing_instance_id);
                $session_key = $tokens['oauth_access_token'];
                $this->oid->insert($owner_id, $instance_id, $session_key);
            }
            if (!$user_in_db) {
                $val = array();
                $val['user_name'] = $fb_page_name;
                $val['full_name'] = $fb_page_name;
                $val['user_id'] = $fb_page_id;
                $val['avatar'] = $fb_page_avatar;
                $val['location'] = '';
                $val['description'] = '';
                $val['url'] = '';
                $val['is_protected'] = false;
                $val['follower_count'] = 0;
                $val['post_count'] = 0;
                $val['joined'] = 0;
                $val['network'] = 'facebook page';
                $user = new User($val);
                $result = $user_dao->updateUser($user);
            }
        } else {
            $messages["info"] .= "Instance ".$fb_page_id.", facebook exists.";
            $instance_id = $i->id;
        }
        return $messages;
    }
}
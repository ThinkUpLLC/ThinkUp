<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/controller/class.TwitterPluginHashtagConfigurationController.php
 *
 * Copyright (c) 2013 Eduard Cucurella
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
 * Twitter Plugin Hashtag Configuration Controller
 *
 * Handles plugin hashtag configuration requests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella
 * @author Eduard Cucurella <ecucurella.t[at]tv3[dot]cat>
 *
 */
class TwitterPluginHashtagConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     *
     * @var Instance
     */
    var $user;
    
    public function __construct($owner, $folder_name, $user) {
        parent::__construct($owner, $folder_name);
        $this->user = $user;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitter/view/twitter.account.hashtag.tpl');
        $this->view_mgr->addHelp('twitterhashtag', 'userguide/settings/plugins/twitterhashtag');
        $this->addToView('twitter_app_name', "ThinkUp ". $_SERVER['SERVER_NAME']);
        $this->addToView('thinkup_site_url', Utils::getApplicationURL(true));

        if (isset($this->user) && $this->user<>''){
            $this->addToView('user', $this->user);
            $id = DAOFactory::getDAO('InstanceDAO');
            $instance = $id->getByUsername($this->user, 'twitter');        
            if (isset($instance)) {             
                $this->addToView('instance', $instance);
                $hd = DAOFactory::getDAO('HashtagDAO');
                $hashtags = $hd->getByUsername($this->user);             
                if (isset($hashtags)){
                    $this->addToView('hashtags', $hashtags);
                }
                else {
                    $this->addErrorMessage("Instance object not set.");
                    $this->addToView('is_hashtag', false);
                }                     
            }
            else {
                $this->addErrorMessage("Instance object not set.");
                $this->addToView('is_instance', false);
            }            
        }
        else {
            $this->addErrorMessage("Username not defined.");
            $this->addToView('is_user', false);            
        }        
        return $this->generateView();
    }
}

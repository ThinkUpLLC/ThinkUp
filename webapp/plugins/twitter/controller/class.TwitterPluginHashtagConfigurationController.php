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
     * Instance username
     * @var str $instance_username
     */
    var $instance_username;

    public function __construct(Owner $owner, $folder_name, $instance_username) {
        parent::__construct($owner, $folder_name);
        $this->instance_username = $instance_username;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitter/view/twitter.account.hashtag.tpl');
        $this->view_mgr->addHelp('twitterhashtag', 'userguide/settings/plugins/twitter/savedsearches');

        if (isset($this->instance_username) && $this->instance_username<>''){
            $this->addToView('user', $this->instance_username);
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->getByUsername($this->instance_username, 'twitter');
            if (isset($instance)) {
                $this->addToView('instance', $instance);
                $hashtaginstance_dao = DAOFactory::getDAO('InstanceHashtagDAO');
                $hashtags = $hashtaginstance_dao->getByUsername($this->instance_username, 'twitter');
                if (isset($hashtags)) {
                    $this->addToView('hashtags', $hashtags);
                }
            } else {
                $this->addErrorMessage("Twitter user @".$this->instance_username." does not exist.");
            }
        } else {
            $this->addErrorMessage("User undefined.");
        }
        return $this->generateView();
    }
}

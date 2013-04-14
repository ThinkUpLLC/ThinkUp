<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterRealtimePlugin.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Twitter Plugin
 *
 * Twitter crawler and webapp plugin retrieves data from Twitter and displays it.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 *
 */
class TwitterRealtimePlugin extends Plugin implements StreamerPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'twitterrealtime';
        //@TODO: Build in support for making a plugin dependent on another plugin's settings, like Twitter's
        //        $this->addRequiredSetting('oauth_consumer_key');
        //        $this->addRequiredSetting('oauth_consumer_secret');
    }

    public function activate() {
    }

    public function deactivate() {
        // don't deactivate the instances, since the twitter plugin/crawler probably still wants them active.
    }

    /**
     * @return void
     */
    public function stream() {
        $logger = Logger::getInstance('stream_log_location');
        $logger->logInfo("in TwitterRealtimePlugin->stream()", __METHOD__.','.__LINE__);
        $stream_master = new StreamMasterCollect();
        $stream_master->launchStreams();
    }

    /**
     * @return void
     */
    public function streamProcess() {
        $logger = Logger::getInstance('stream_log_location');
        $logger->logInfo("in TwitterRealtimePlugin->streamProcess()", __METHOD__.','.__LINE__);
        $stream_proc = new StreamProcess();
        $stream_proc->processStreamData();
    }

    /**
     * @return void
     */
    public function shutdownStreams() {
        $logger = Logger::getInstance('stream_log_location');
        $logger->logInfo("in TwitterRealtimePlugin->shutdownStreams()", __METHOD__.','.__LINE__);
        $stream_master = new StreamMasterCollect();
        $stream_master->shutdownStreams();
    }

    /**
     * @param  $owner
     * @return string
     */
    public function renderConfiguration($owner) {
        $controller = new TwitterRealtimePluginConfigurationController($owner, 'twitterrealtime');
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }
}

<?php
/**
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInsightsGeneratorPlugin.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
 *
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
 * Test of Insights Generator
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.InsightsGeneratorPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.InsightPluginParent.php';

class TestOfInsightsGeneratorPlugin extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testDontSendAsNonAdmin() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'never@example.com', 'is_activated'=>1, 'notification_frequency' => 'never', 'is_admin' => 0));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));

        $this->simulateLogin('never@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);
    }

    public function testNeverSendSetting() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'never@example.com', 'is_activated'=>1, 'notification_frequency' => 'never'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));

        $this->simulateLogin('never@example.com');
        // Should not send or set options with a 'never' instance
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertTrue(count($options)>0);
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);

        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao->updateOption($plugin_id, 'last_daily_email', date('Y-m-d', strtotime('last year')));
        if (!$plugin_option_dao->updateOption($plugin_id, 'last_weekly_email', date('Y-m-d', strtotime('last year')))) {
            $plugin_option_dao->insertOption($plugin_id, 'last_weekly_email', date('Y-m-d', strtotime('last year')));
        }
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 2);

        // Should not send with dates and 'never'
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
    }

    public function testDailySendSetting() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'daily@example.com', 'is_activated'=>1, 'notification_frequency' => 'daily'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 'time_generated'=>date('Y-m-d 03:00:00')));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>5, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 99 new lists', 'time_generated'=>date('Y-m-d 01:00:00')));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $this->simulateLogin('daily@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();

        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 1, 'Just last_daily_email set');
        $this->assertNotNull($options['last_daily_email'], 'Just last_daily_email set');
        $this->assertNull($options['last_weekly_email'], 'Just last_daily_email set');
        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*daily@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);
        $this->assertPattern('/99 new lists/', $sent);


        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send again same day');
    }

    public function testWeeklySendSetting() {
        $plugin = new InsightsGeneratorPlugin();
        $day_to_run = date('D', strtotime("Sunday +".$plugin::WEEKLY_DIGEST_DAY_OF_WEEK." days"));
        $day_not_to_run = date('D', strtotime("Sunday +".(($plugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6)." days"));
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'weekly@example.com', 'is_activated'=>1, 'notification_frequency' => 'weekly'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime($day_to_run.' 5pm')-(60*60*24*3))));


        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $this->simulateLogin('weekly@example.com');

        $plugin->current_timestamp = strtotime($day_not_to_run.' 5pm');
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send on '.$day_not_to_run);

        $plugin->current_timestamp = strtotime($day_to_run.' 5pm');
        $plugin->crawl();

        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 2);
        $this->assertNotNull($options['last_weekly_email']);
        $this->assertNotNull($options['last_daily_email']);
        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*weekly@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);


        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send again same day');
    }

    public function testBothSendSetting() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $day_to_run = date('D', strtotime("Sunday +".$plugin::WEEKLY_DIGEST_DAY_OF_WEEK." days"));
        $day_not_to_run = date('D', strtotime("Sunday +".(($plugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6)." days"));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'both@example.com', 'is_activated'=>1, 'notification_frequency' => 'both'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 
        'time_generated'=>date('Y-m-d 03:00:00', strtotime($day_not_to_run.' 1am'))));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $this->simulateLogin('both@example.com');

        // This should just send daily
        $plugin->current_timestamp = strtotime($day_not_to_run.' 5pm');
        $plugin->crawl();

        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);

        $this->assertEqual(count($options), 1);
        $this->assertNotNull($options['last_daily_email']);
        $this->assertNull($options['last_weekly_email']);
        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*both@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);
        
        // This should just send both
        $plugin_option_dao->deleteOption($options['last_daily_email']->id);
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);
        $plugin->current_timestamp = strtotime($day_to_run.' 5pm');
        $plugin->crawl();

        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);

        $this->assertEqual(count($options), 2);
        $this->assertNotNull($options['last_daily_email']);
        $this->assertNotNull($options['last_weekly_email']);
        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*both@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);
        $this->assertPattern('/Weekly/', $sent);
    }

    public function testMultiUser() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'notification_frequency' => 'daily'));
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. User','is_admin'=>0,
        'email'=>'normal@example.com', 'is_activated'=>1, 'notification_frequency' => 'daily'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5, 'id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>6, 'id'=>2));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'joetest', 'id' => 6, 
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6, 
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'Joe Test is on 99 new lists', 
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();

        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);

        // Now we make sure that mr normal got an email, since he'll be last in the list
        $this->assertTrue(count($options)>0);
        $this->assertNotNull($options['last_daily_email']);
        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*@example.com/', $sent);
        $this->assertPattern('/9 new lists/', $sent);


        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $log = file($logger_file);
        $last_log = join("\n", array_slice($log, -10));
        $this->assertPattern('/daily digest to admin@example/', $last_log);
        $this->assertPattern('/daily digest to normal@example/', $last_log);
    }

}

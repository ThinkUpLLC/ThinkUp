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
        date_default_timezone_set('America/New_York');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testDontSendAsNonAdmin() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'never@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'never', 'is_admin' => 0, 'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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
        'email'=>'never@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'never',
        'timezone' => 'America/New_York'
        ));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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
        //If it's the right day of the week, the crawl sets this, if not do it manually
        if (!$plugin_option_dao->updateOption($plugin_id, 'last_weekly_email', date('Y-m-d', strtotime('last year')))){
            try {
                $plugin_option_dao->insertOption($plugin_id, 'last_weekly_email', date('Y-m-d',
                strtotime('last year')));
            } catch (DuplicateOptionException $e) {
                //this will happen if updated rows are 0 but option still exists
            }
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
        'email'=>'daily@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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
        $this->debug(Utils::varDumpToString($options));
        $this->assertNotNull($options['last_daily_email'], 'last_daily_email set');
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
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
        $day_to_run = date('D', strtotime("Sunday +".(InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK)." days"));
        $day_not_to_run = date('D', strtotime("Sunday +".((InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6)." days"));
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'weekly@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'weekly',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime($day_to_run.' 5pm')-(60*60*24*3))));
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'example.com'));

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
        $this->debug($sent);
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*weekly@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);
        $this->assertPattern('/example.com/', $sent);

        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send again same day');
    }

    public function testBothSendSetting() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $day_to_run = date('D', strtotime("Sunday +".InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK." days"));
        $day_not_to_run = date('D', strtotime("Sunday +".((InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6)." days"));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'both@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'both',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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
        $this->assertPattern('/This week/', $sent);
    }
    public function testMultiUser() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. User','is_admin'=>0,
        'email'=>'normal@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily'));
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

    public function testMandrillHTML() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key', null);
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0, 'Starting with no settings');

        $long_ago = date('Y-m-d', strtotime('last year'));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'downtonabb.ey'));

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertPattern('/http:\/\/downtonabb.ey/', $sent);

        // We can tell if it's HTML because we'll have a JSON block to decode
        $this->debug($sent);
        $decoded = json_decode($sent);
        $this->assertNull($decoded);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));

        // Set just api key, send again
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin_option_dao->updateOption($options['last_daily_email']->id, 'last_daily_email', $long_ago);
        $config->setValue('mandrill_api_key','1234');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->text);
        $this->assertNull($decoded->global_merge_vars);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));

        // Finally, set a template name a test output
        $plugin_option_dao->updateOption($options['last_daily_email']->id, 'last_daily_email', $long_ago);
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->global_merge_vars);
        $this->assertEqual(count($decoded->global_merge_vars), 5);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        $this->assertPattern('/http:\/\/downtonabb.ey\/\?u=/', $merge_vars['insights'], 'Insights URL contains host');
        $this->assertPattern('/1234 new lists/', $merge_vars['insights']);
        $this->assertEqual($config->getValue('app_title_prefix').'ThinkUp', $merge_vars['app_title']);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
    }

    public function testMandrillHTMLWithExceptions() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key', '1234');
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');

        $long_ago = date('Y-m-d', strtotime('last year'));

        // When in test mode, the mailHTMLViaMandrill method will throw a Template Not Found exception
        // if the email address contains "templateerror".
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'templateerror@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'prefix'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        $this->simulateLogin('templateerror@example.com');
        $plugin->current_timestamp = strtotime('5pm');

        $exception = null;
        try {
            $plugin->crawl();
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertNull($e,'Should not get Mandrill template error');
        $sent = Mailer::getLastMail();
        //Sent plain text email
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        //Not HTML email via JSON
        $this->assertNull($decoded->global_merge_vars);

        //Check user got a log message
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $log = file($logger_file);
        $last_log = join("\n", array_slice($log, -10));
        $this->assertPattern('/invalid mandrill template/i', $last_log);
    }

    public function testTimezoneHandling() {
        $tz = date_default_timezone_get();
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'daily@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/Los_Angeles'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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

        date_default_timezone_set('America/New_York');
        $this->simulateLogin('daily@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('5am'); // Should not yet be 4am in America/Los_Angeles of owner
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        date_default_timezone_set('America/Los_Angeles');
        $plugin->current_timestamp = strtotime('3am'); // Still not time
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        date_default_timezone_set('America/Los_Angeles');
        $plugin->current_timestamp = strtotime('5am'); // Should be time now.
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertTrue(count($options)>0);
        date_default_timezone_set($tz);
    }

    public function testNoTimezoneHandling() {
        $tz = date_default_timezone_get();
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'daily@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily','timezone'=>''));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
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

        $config = Config::getInstance();
        date_default_timezone_set($config->getValue('timezone'));
        $this->simulateLogin('daily@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('3am'); // Should not set yet
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $plugin->current_timestamp = strtotime('5am'); // SHould send
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertTrue(count($options)>0);
        date_default_timezone_set($tz);
    }
}

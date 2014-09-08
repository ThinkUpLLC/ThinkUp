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
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstanceMySQLDAO.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstance.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.InsightsGeneratorPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php';

class TestOfInsightsGeneratorPlugin extends ThinkUpInsightUnitTestCase {

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
        'email'=>'never@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'never', 'is_admin' => 0,
        'timezone' => 'America/New_York'));
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 'time_generated'=>date('Y-m-d 03:00:00'), 'related_data'=>null));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 99 new lists', 'time_generated'=>date('Y-m-d 01:00:00'), 'related_data'=>null));

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
        $day_not_to_run = date('D', strtotime("Sunday +".((InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6).
        " days"));
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 'related_data'=>null,
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
        $day_not_to_run = date('D', strtotime("Sunday +".((InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6).
        " days"));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'both@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'both',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 'related_data'=>null,
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am')), 'related_data'=>null));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 99 new lists',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am')), 'related_data'=>null));

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

    public function testMandrillHTMLDaily() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key', null);
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->debug($options);
        $this->assertEqual(count($options), 0, 'Starting with no settings');

        $long_ago = date('Y-m-d', strtotime('-7 day'));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));

        //accented character
        $builders[] = FixtureBuilder::build('instances', array('id' => 7, 'network_username'=>'Bill Cõsby',
        'network'=>'facebook', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('id'=>2, 'owner_id'=>1, 'instance_id'=>7));
        $builders[] = FixtureBuilder::build('insights', array('id'=>3, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('insights', array('id'=>4, 'instance_id'=>7,
        'slug'=>'posts_on_this_day_popular_flashback', 'headline'=>'Wow: Made the List:',
        'text'=>'This was Bill Cosby\'s most popular post a year ago.',
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am')), 'related_data'=>null));

        // Hero image
        $hero_image = array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-02/olympics2014.jpg',
            'alt_text' => 'The Olympic rings in Sochi',
            'credit' => 'Photo: Atos International',
            'img_link' => 'http://www.flickr.com/photos/atosorigin/12568057033/'
        );
        $builders[] = FixtureBuilder::build('insights', array('id'=>5, 'instance_id'=>6,
        'slug'=>'olympics_2014', 'headline'=>'Do they give out medals for tweets?',
        'text'=>'You tweeted a hundred thousand times during the Olympics.',
        'related_data'=>serialize(array("hero_image"=>$hero_image)),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Tweets
        $builders[] = FixtureBuilder::build('insights', array('id'=>6, 'instance_id'=>6,
        'slug'=>'posts_on_this_day_popular_flashback', 'headline'=>'This was your fave a year ago.',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfPosts('twitter', 1, 5),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Facebook posts
        $builders[] = FixtureBuilder::build('insights', array('id'=>7, 'instance_id'=>7,
        'slug'=>'posts_on_this_day_popular_flashback', 'headline'=>'Your top Facebook posts',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfPosts('facebook', 1, 5),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Twitter Users
        $builders[] = FixtureBuilder::build('insights', array('id'=>8, 'instance_id'=>6,
        'slug'=>'least_likely_followers', 'headline'=>'2 interesting people followed @snooki',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfUsers('twitter', 1, 5),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Facebook Users
        $builders[] = FixtureBuilder::build('insights', array('id'=>9, 'instance_id'=>7,
        'slug'=>'biggest_fans_last_7_days', 'headline'=>'Last week, these were Alice Cooper\'s biggest admirers.',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfUsers('facebook'),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Facebook Photo
        $builders[] = FixtureBuilder::build('insights', array('id'=>10, 'instance_id'=>7,
        'slug'=>'posts_on_this_day_popular_flashback', 'headline'=>'This is a test of a Facebook photo',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfPosts('facebook', 1, 1),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        // Twitter photo
        $builders[] = FixtureBuilder::build('insights', array('id'=>11, 'instance_id'=>7,
        'slug'=>'posts_on_this_day_popular_flashback', 'headline'=>'This is a Twitter photo',
        'text'=>'',
        'related_data'=>$this->getRelatedDataListOfPosts('twitter', 2, 2),
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));

        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'downtonabb.ey'));

        $this->debug($builders);

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->debug($sent);
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
        $this->assertEqual(count($decoded->global_merge_vars), 4);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        $this->assertPattern('/http:\/\/downtonabb.ey\/.*\?u=/', $merge_vars['insights'], 'Insights URL contains host');
        /**
         * Now that we're utf8_encoding email contents for Mandrill compatibility, this accented character is
         * not handled the way it should. The utf8_encode outputs:
         * http://downtonabb.ey/?u=Bill+CÃµsby&amp;n=facebook&amp;d=2014-03-04&amp;s=frequency
         * So we're temporarily commenting out this assertion, a terrible--but temporary!--solution.
         * The long-term solution is to retrieve insights by network_user_id rather than name.
         * https://github.com/ginatrapani/ThinkUp/issues/972
         */
        //Should preserve accented character
        //$this->assertPattern('/http:\/\/downtonabb.ey\/.*\?u=Bill\+Cõsby/', $merge_vars['insights'],
        //    'Insight URL should not contain spaces');

        $this->debug($merge_vars['insights']);
        $this->assertNoPattern('/http:\/\/downtonabb.ey\/.*\?u=Bill\+Cosby/', $merge_vars['insights'],
            'Insight URL should not contain spaces');
        $this->assertPattern('/assets\/img\/icons\/facebook-gray\.png/', $merge_vars['insights'],
            'Facebook icon should appear');
        $this->assertPattern('/assets\/img\/icons\/twitter-gray\.png/', $merge_vars['insights'],
            'Twitter icon should appear');
        $this->assertPattern('/Wow:/', $merge_vars['insights'], 'Do not strip colons from headline');
        $this->assertPattern('/You receive new insights from ThinkUp once a day./', $merge_vars['insights']);
        $this->assertPattern('/To get insights once a week or unsubscribe altogether/', $merge_vars['insights']);
        $this->assertPattern('/1234 new lists/', $merge_vars['insights']);
        $this->debug($merge_vars['insights']);
        //assert unsub link
        $this->assertPattern('/http:\/\/downtonabb\.ey\/.*account\/index.php\?m\=manage\#instances/',
            $merge_vars['insights']);
        $this->assertEqual($config->getValue('app_title_prefix').'ThinkUp', $merge_vars['app_title']);
        //assert hero image
        $this->assertPattern('/<img src="https:\/\/www\.thinkup\.com\/assets\/images\/insights\/2014-02\/'.
            'olympics2014.jpg" alt="The Olympic rings in Sochi"/', $merge_vars['insights']);
        $this->assertPattern('/Photo: Atos International/', $merge_vars['insights']);
        // Assert Twitter user list
        $this->assertPattern('/2 interesting people followed @snooki/', $merge_vars['insights']);
        $this->assertPattern('/https:\/\/pbs.twimg.com\/profile_images\/436950275908055040\/'.
            '0Z8Pa9fD_normal.jpeg/', $merge_vars['insights']);
        $this->assertPattern('/Jon Angelo Gjetting/', $merge_vars['insights']);
        $this->assertPattern('/Creative Director thriving/', $merge_vars['insights']);
        // Assert Facebook user list
        $this->assertPattern('/Last week, these were Alice Cooper\'s biggest admirers./', $merge_vars['insights']);
        $this->assertPattern('/Jonathan Wegener/', $merge_vars['insights']);
        $this->assertPattern('/https:\/\/graph.facebook.com\/101531\/picture/', $merge_vars['insights']);
        // Assert Twitter posts list
        $this->assertPattern('/This was your fave a year ago./', $merge_vars['insights']);
        $this->assertPattern('/@thinkup/', $merge_vars['insights']);
        $this->assertPattern('/https:\/\/pbs.twimg.com\/profile_images\/2600723193\/'.
            'rvi01vw1b4mtq8gudcs6_normal.png/', $merge_vars['insights']);
        // Assert Facebook posts list
        $this->assertPattern('/Your top Facebook posts/', $merge_vars['insights']);
        $this->assertPattern('/Matt Jacobs/', $merge_vars['insights']);
        $this->assertPattern('/https:\/\/graph.facebook.com\/502783489\/picture/', $merge_vars['insights']);
        // Assert Facebook image post
        $this->assertPattern('/This is a test of a Facebook photo/', $merge_vars['insights']);
        $this->assertPattern('/https:\/\/fbcdn-photos-b-a.akamaihd.net\/hphotos-ak-prn2\/t1.0-0\/10257006'.
            '_10152090546942592_818305084183485605_s.jpg/', $merge_vars['insights']);
        // Assert Twitter image post
        $this->assertPattern('/This is a Twitter photo/', $merge_vars['insights']);
        $this->assertPattern('/http:\/\/instagr.am\/p\/EYhds/', $merge_vars['insights']);
        //assert CSS curly braces are preserved
        $this->assertPattern('/a:hover {/', $merge_vars['insights']);
        $this->assertEqual($config->getValue('app_title_prefix').'ThinkUp', $merge_vars['app_title']);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
    }

    public function testMandrillHTMLDailyFreeTrialMemberProDay1Through14() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key','1234');
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');

        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $long_ago = date('Y-m-d', strtotime('-7 day'));
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');
        $plugin_option_dao->insertOption($plugin_id, 'last_daily_email', $long_ago);
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $builders = self::buildDataForDailyEmailFreeTrial();

        $membership_levels = array("Member", "Pro");
        foreach ($membership_levels as $membership_level) {
            $i = 0;
            while ($i < 15) {
                $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User',
                    'is_admin'=>1, 'email'=>'admin@example.com', 'is_activated'=>1,
                    'email_notification_frequency' => 'daily', 'timezone' => 'America/New_York',
                    'is_free_trial'=>1, 'joined'=>'-'.$i.'d', 'membership_level'=>$membership_level));
                $this->simulateLogin('admin@example.com');
                $plugin->current_timestamp = strtotime('5pm');
                $plugin->crawl();
                $sent = Mailer::getLastMail();
                $merge_vars = array();
                $decoded = json_decode($sent);
                foreach ($decoded->global_merge_vars as $mv) {
                    $merge_vars[$mv->name] = $mv->content;
                }
                $html_email = $merge_vars['insights'];
                $this->debug($html_email);
                $days_left = 13 - $i;
                if ($days_left <= 13 && $days_left > 2) {
                    $this->assertPattern('/http:\/\/example.com\/thinkup\/membership.php/', $html_email);
                    $this->assertPattern('/'.$days_left.' days left in your free trial/', $html_email);
                    if ($membership_level == "Member") {
                        $this->assertPattern('/Just \$5\/month/', $html_email);
                    } elseif ($membership_level == "Pro") {
                        $this->assertPattern('/Just \$10\/month/', $html_email);
                    }
                }
                if ($days_left == 2) {
                    $this->assertPattern('/Only 48 hours left!/', $html_email);
                    if ($membership_level == "Member") {
                        $this->assertPattern('/just 16 cents a day/', $html_email);
                    } elseif ($membership_level == "Pro") {
                        $this->assertPattern('/just 32 cents a day/', $html_email);
                    }
                }
                if ($days_left == 1) {
                    $this->assertPattern('/Only 24 hours left!/', $html_email);
                }
                unlink(FileDataManager::getDataPath(Mailer::EMAIL));
                $plugin_option_dao->updateOption($options['last_daily_email']->id, 'last_daily_email', $long_ago);
                $owner_builder = null;
                $i++;
            }
        }
    }

    /**
     * Add just enough data to generate an email.
     * @return arr FixtureBuilders
     */
    private function buildDataForDailyEmailFreeTrial() {
        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        return $builders;
    }

    public function testMandrillHTMLWeekly() {
        $plugin = new InsightsGeneratorPlugin();
        $day_to_run = date('D', strtotime("Sunday +".(InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK)." days"));
        $day_not_to_run = date('D', strtotime("Sunday +".((InsightsGeneratorPlugin::WEEKLY_DIGEST_DAY_OF_WEEK+1)%6).
        " days"));
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
        'email'=>'weekly@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'weekly',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('id'=>1, 'owner_id'=>1, 'instance_id'=>5,
        'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'CDMoyer is on 29 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime($day_to_run.' 5pm')-(60*60*24*3))));

        $builders[] = FixtureBuilder::build('instances', array('id'=>6,'network_username'=>'Bill Cosby',
        'network'=>'facebook', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('id'=>2, 'owner_id'=>1, 'instance_id'=>6));
        $builders[] = FixtureBuilder::build('insights', array('id'=>3, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Wow: Made the List:',
        'text'=>'Bill Cosby is on 1,234 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime($day_to_run.' 5pm')-(60*60*24*3))));

        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'example.com'));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');

        $config = Config::getInstance();
        $config->setValue('mandrill_api_key','1234');

        $this->simulateLogin('weekly@example.com');

        $plugin->current_timestamp = strtotime($day_not_to_run.' 5pm');
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send on '.$day_not_to_run);

        $plugin->current_timestamp = strtotime($day_to_run.' 5pm');
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->global_merge_vars);
        $this->assertEqual(count($decoded->global_merge_vars), 4);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        $this->debug($merge_vars['insights']);
        $this->assertNotEqual('', $sent);
        $this->assertPattern('/to.*weekly@example.com/', $sent);
        $this->assertPattern('/29 new lists/', $sent);
        $this->assertPattern('/example.com/', $sent);
        $this->assertPattern('/You receive new insights from ThinkUp once a week./', $merge_vars['insights']);
        $this->assertPattern('/To get insights once a day or unsubscribe altogether/', $merge_vars['insights']);
        $this->assertPattern('/http:\/\/example.com\/.*\?u=Bill\+Cosby/', $merge_vars['insights'],
            'Insight URL should not contain spaces');
        $this->assertPattern('/Wow:/', $merge_vars['insights'], 'Do not strip colons from headline');
        $this->assertPattern('/assets\/img\/icons\/facebook-gray\.png/', $merge_vars['insights'],
            'Facebook icon should appear');
        $this->assertPattern('/assets\/img\/icons\/twitter-gray\.png/', $merge_vars['insights'],
            'Twitter icon should appear');

        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent, 'Should not send again same day');
    }

    public function testMandrillHTMLThinkUpLLCUnsubLinkWithWelcomeMessage() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key','1234');
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');

        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');

        $long_ago = date('Y-m-d', strtotime('last year'));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'downtonabb.ey'));

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->global_merge_vars);
        $this->assertEqual(count($decoded->global_merge_vars), 4);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        $this->assertPattern('/http:\/\/downtonabb.ey\/.*\?u=/', $merge_vars['insights'], 'Insights URL contains host');
        $this->assertPattern('/1234 new lists/', $merge_vars['insights']);

        //Assert welcome message shows up
        $this->assertPattern('/Thanks for joining ThinkUp/', $merge_vars['insights']);
        $this->assertPattern('/get an email like this/', $merge_vars['insights']);
        //Assert welcome header text
        $this->assertPattern('/Welcome to ThinkUp/', $merge_vars['insights']);
        //Assert subject line is welcoming
        $this->assertPattern('/Welcome to ThinkUp! Here are your insights./', $decoded->subject);

        $this->debug($merge_vars['insights']);
        $this->debug($decoded->subject);

        //assert correct unsub link
        $this->assertNoPattern('/http:\/\/downtonabb\.ey\/account\/index.php\?m\=manage\#instances/',
            $merge_vars['insights']);
        $this->assertPattern('/http:\/\/example.com\/thinkup\/settings.php/',
            $merge_vars['insights']);
        $this->assertEqual($config->getValue('app_title_prefix').'ThinkUp', $merge_vars['app_title']);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
    }

    public function testGetEmailMessageSubjectLine() {
        // High emphasis insights
        $insights = array();

        $insight_high = new Insight();
        $insight_high->headline = "This is a <strong>high emphasis</strong> insight for @joeuser&hellip;";
        $insight_high->emphasis = Insight::EMPHASIS_HIGH;
        $instance = new Instance();
        $instance->network_username = 'joeuser';
        $instance->network = 'twitter';
        $insight_high->instance = $instance;
        $insights[] = $insight_high;

        $plugin = new InsightsGeneratorPlugin();

        //Get headline as subject line
        TimeHelper::setTime(1);
        $result = $plugin->getEmailMessageSubjectLine('weekly', $insights);
        $this->assertEqual($result, 'This is a high emphasis insight for you…');

        //Don't get headline as subject line
        TimeHelper::setTime(2);
        $result = $plugin->getEmailMessageSubjectLine('weekly', $insights);
        $this->assertNotEqual($result, 'This is a high emphasis insight for you…');

        // Mediun and low emphasis insights
        $insights = array();

        $insight_med = new Insight();
        $insight_med->headline = "This is a medium emphasis&mdash;insight for @joeuser";
        $insight_med->emphasis = Insight::EMPHASIS_MED;
        $instance  = new Instance();
        $instance->network_username = 'joeuser';
        $instance->network = 'twitter';
        $insight_med->instance = $instance;
        $insights[] = $insight_med;
        $insight_low = new Insight();
        $insight_low->headline = "This is a medium emphasis insight for @joeuser";
        $insight_low->emphasis = Insight::EMPHASIS_LOW;
        $insights[] = $insight_low;

        TimeHelper::setTime(1);
        $result = $plugin->getEmailMessageSubjectLine('weekly', $insights);
        $this->assertEqual($result, 'This is a medium emphasis—insight for you');

        //No high or medium insights
        $insights = array();

        $insight_low_1 = new Insight();
        $insight_low_1->headline = "This is a low emphasis insight for @joeuser";
        $insight_low_1->emphasis = Insight::EMPHASIS_LOW;
        $insights[] = $insight_low_1;
        $insight_low_2 = new Insight();
        $insight_low_2->headline = "This is a low emphasis insight for @joeuser";
        $insight_low_2->emphasis = Insight::EMPHASIS_LOW;
        $insights[] = $insight_low_2;

        //Weekly
        $good_weekly_subjects = array(
            1 => "How did you do online this week? Here are your ThinkUp insights",
            2 => "Your ThinkUp insights this week",
            3 => "New ThinkUp insights are ready for you",
            4 => "This week's ThinkUp insights",
            5 => "This week was great! ThinkUp's got details",
            6 => "How did you do online this week? Here are your ThinkUp insights"
        );
        for ($i=1; $i<=6; $i++) {
            TimeHelper::setTime($i);
            $result = $plugin->getEmailMessageSubjectLine('Weekly', $insights);
            $this->assertEqual($result, $good_weekly_subjects[$i]);
        }

        //Daily
        $good_daily_subjects = array(
            1 => "You have new insights from ThinkUp",
            2 => "Your new insights from ThinkUp",
            3 => "New ThinkUp insights are ready for you",
            4 => "These are your latest ThinkUp insights",
            5 => "A few new ThinkUp insights for you",
            6 => "New ThinkUp insights are waiting for you",
            7 => "ThinkUp: Today's insights",
            8 => "These are your ThinkUp insights for ".date('l', $plugin->current_timestamp),
            9 => "ThinkUp found 2 insights for you today. Here's a look.",
            10 => "You have 2 new insights from ThinkUp",
            11 => "ThinkUp has new insights for you! Take a look",
        );
        for ($i=1; $i<=11; $i++) {
            TimeHelper::setTime($i);
            $result = $plugin->getEmailMessageSubjectLine('Daily', $insights);
            $this->assertEqual($result, $good_daily_subjects[$i]);
        }

        //Daily with just one insight
        //No high or medium insights
        $insights = array();

        $insight_low_1 = new Insight();
        $insight_low_1->headline = "This is a low emphasis insight for @joeuser";
        $insight_low_1->emphasis = Insight::EMPHASIS_LOW;
        $insights[] = $insight_low_1;

        $good_daily_subjects = array(
            1 => "You have new insights from ThinkUp",
            2 => "Your new insights from ThinkUp",
            3 => "New ThinkUp insights are ready for you",
            4 => "These are your latest ThinkUp insights",
            5 => "A few new ThinkUp insights for you",
            6 => "New ThinkUp insights are waiting for you",
            7 => "ThinkUp: Today's insights",
            8 => "These are your ThinkUp insights for ".date('l', $plugin->current_timestamp),
            9 => "ThinkUp has new insights for you! Take a look",
            10 => "You have new insights from ThinkUp",
            11 => "Your new insights from ThinkUp",
        );

        // Ensure we skip over the subjects involving the count to avoid pluralization issues
        for ($i=1; $i<=11; $i++) {
            TimeHelper::setTime($i);
            $result = $plugin->getEmailMessageSubjectLine('Daily', $insights);
            $this->assertEqual($result, $good_daily_subjects[$i]);
        }
    }

    public function testMandrillHTMLThinkUpLLCUnsubLinkWithOutWelcomeMessage() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key','1234');
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');

        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin_option_dao->insertOption($plugin_id, 'last_daily_email', date('2012-01-01'));
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');

        $long_ago = date('Y-m-d', strtotime('last year'));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'downtonabb.ey'));

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->global_merge_vars);
        $this->assertEqual(count($decoded->global_merge_vars), 4);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        $this->assertPattern('/http:\/\/downtonabb.ey\/.*\?u=/', $merge_vars['insights'], 'Insights URL contains host');
        $this->assertPattern('/1234 new lists/', $merge_vars['insights']);

        //Assert no welcome message shows up
        $this->assertNoPattern('/Thanks for joining ThinkUp/', $merge_vars['insights']);
        $this->assertNoPattern('/get an email like this/', $merge_vars['insights']);
        //Assert no welcome header text
        $this->assertNoPattern('/Welcome to ThinkUp/', $merge_vars['insights']);
        //Assert subject line is not welcoming
        $this->assertNoPattern('/Welcome to ThinkUp! Here are your insights./', $decoded->subject);

        $this->debug($merge_vars['insights']);
        $this->debug($decoded->subject);

        //assert correct unsub link
        $this->assertNoPattern('/http:\/\/downtonabb\.ey\/account\/index.php\?m\=manage\#instances/',
            $merge_vars['insights']);
        $this->assertPattern('/http:\/\/example.com\/thinkup\/settings.php/',
            $merge_vars['insights']);
        $this->assertEqual($config->getValue('app_title_prefix').'ThinkUp', $merge_vars['app_title']);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
    }

    public function testMandrillHTMLThinkUpLLCWithTout() {
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $plugin = new InsightsGeneratorPlugin();
        $config = Config::getInstance();
        $config->setValue('mandrill_api_key','1234');
        $config->setValue('thinkupllc_email_tout','Ohai! This is a tout, you should check out our blog');
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');

        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin_option_dao->insertOption($plugin_id, 'last_daily_email', date('2012-01-01'));
        $plugin_option_dao->insertOption($plugin_id, 'mandrill_template', $template = 'my_template');

        $long_ago = date('Y-m-d', strtotime('last year'));

        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp Q. User','is_admin'=>1,
        'email'=>'admin@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
        'timezone' => 'America/New_York'));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 6,
        'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>6, 'id'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>6,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists', 'related_data'=>null,
        'time_generated'=>date('Y-m-d 03:00:00', strtotime('1am'))));
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'downtonabb.ey'));

        $this->simulateLogin('admin@example.com');
        $plugin->current_timestamp = strtotime('5pm');
        $plugin->crawl();
        $sent = Mailer::getLastMail();
        $this->assertNotEqual($sent, '');
        $decoded = json_decode($sent);
        $this->assertNotNull($decoded);
        $this->assertNotNull($decoded->global_merge_vars);
        $this->assertEqual(count($decoded->global_merge_vars), 4);
        $merge_vars = array();
        foreach ($decoded->global_merge_vars as $mv) {
            $merge_vars[$mv->name] = $mv->content;
        }
        // Assert email tout shows up
        $this->assertPattern('/Ohai/', $merge_vars['insights'], 'Insights URL contains host');
        $this->debug($merge_vars['insights']);
        $this->debug($decoded->subject);
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:',
        'text'=>'Joe Test is on 1234 new lists', 'related_data'=>null,
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
        'text'=>'CDMoyer is on 29 new lists', 'time_generated'=>date('Y-m-d 03:00:00')));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
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
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
        'text'=>'CDMoyer is on 29 new lists', 'time_generated'=>date('Y-m-d 03:00:00')));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>5,
        'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
        'text'=>'CDMoyer is on 99 new lists', 'time_generated'=>date('Y-m-d 01:00:00')));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $config = Config::getInstance();
        date_default_timezone_set($config->getValue('timezone'));
        $this->simulateLogin('daily@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('3am'); // Should not send yet
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $plugin->current_timestamp = strtotime('5am'); // Should send
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNotEqual('', $sent);
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertTrue(count($options)>0);
        date_default_timezone_set($tz);
    }

    public function testHandlingLastSentTime() {
        $tz = date_default_timezone_get();
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User','is_admin'=>1,
            'email'=>'daily@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'daily',
            'timezone'=>'America/New_York'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>5,
            'auth_error'=>''));
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'cdmoyer', 'id' => 5,
            'network'=>'twitter', 'is_activated'=>1, 'is_public'=>1));
        $builders[] = FixtureBuilder::build('insights', array('id'=>1, 'instance_id'=>5,
            'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
            'text'=>'CDMoyer is on 29 new lists', 'time_generated'=>date('Y-m-d 4:00', strtotime('-49 hour'))));
        $builders[] = FixtureBuilder::build('insights', array('id'=>2, 'instance_id'=>5,
            'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
            'text'=>'CDMoyer is on 99 new lists', 'time_generated'=>date('Y-m-d H:00:00', strtotime('-45 hour'))));
        $builders[] = FixtureBuilder::build('insights', array('id'=>3, 'instance_id'=>5,
            'slug'=>'new_group_memberships', 'headline'=>'Made the List:', 'related_data'=>null,
            'text'=>'CDMoyer is on 42 new lists', 'time_generated'=>date('Y-m-d H:00:00', strtotime('-42 hour'))));

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $this->assertEqual(count($options), 0);

        $config = Config::getInstance();
        //date_default_timezone_set($config->getValue('timezone'));
        $this->simulateLogin('daily@example.com');
        $plugin = new InsightsGeneratorPlugin();
        $plugin->current_timestamp = strtotime('Yesterday 4am'); // Should send
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNoPattern('/29 new lists/', $sent);
        unlink(FileDataManager::getDataPath(Mailer::EMAIL));

        $plugin->current_timestamp = strtotime('4am'); // Should send
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin_option_dao->updateOption($options['last_daily_email']->id, 'last_daily_email',
            date('Y-m-d H:00:00', strtotime('-43 hour')));
        $options = $plugin_option_dao->getOptionsHash($plugin->folder_name, true);
        $plugin->crawl();

        $sent = Mailer::getLastMail();
        $this->assertNoPattern('/29 new lists/', $sent);
        $this->assertNoPattern('/99 new lists/', $sent);
        $this->assertPattern('/42 new lists/', $sent);
    }
}

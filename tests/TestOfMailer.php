<?php
/**
 *
 * ThinkUp/tests/TestOfMailer.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfMailer extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue("mandrill_api_key", "");
    }

    public function tearDown(){
        parent::tearDown();
        $config = Config::getInstance();
        $config->setValue("mandrill_api_key", "");
        // delete test email file if it exists
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        if (file_exists($test_email)) {
            unlink($test_email);
        }
    }

    public function testFromName() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "My Crazy Custom ");
        $_SERVER['HTTP_HOST'] = "my_thinkup_hostname";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);
        $this->assertPattern('/From: "My Crazy Custom ThinkUp" <notifications@my_thinkup_hostname>/',
        $email_body);

        $config->setValue("app_title_prefix", "My Other Installation of ");
        $_SERVER['HTTP_HOST'] = null;
        $_SERVER['SERVER_NAME'] = "my_other_hostname";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);
        $this->assertPattern('/From: "My Other Installation of ThinkUp" <notifications@my_other_hostname>/',
        $email_body);

        $config->setValue("app_title_prefix", "Yet Another Installation of ");
        $_SERVER['HTTP_HOST'] = null;
        $_SERVER['SERVER_NAME'] = null;
        $builder = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'testservername') );
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);
        $this->assertPattern('/From: "Yet Another Installation of ThinkUp" <notifications@testservername>/',
        $email_body);
    }

    public function testMandrill() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "My Crazy Custom ");
        $config->setValue("mandrill_api_key", "1234567890");
        $_SERVER['HTTP_HOST'] = "thinkupapp.com";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);

        // Exact JSON structure copied from Mandrill's site
        $json = '{"text":"Me worky, yo?","subject":"Testing 123","from_email":"notifications@thinkupapp.com",'.
        '"from_name":"My Crazy Custom ThinkUp","to":[{"email":"you@example.com","name":"you@example.com"}]}';

        // Compare JSON string, ignoring whitespace differences
        $this->assertEqual($json, $email_body);
    }

    public function testMandrillThinkUpLLCEndpoint() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "My Crazy Custom ");
        $config->setValue("mandrill_api_key", "1234567890");
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');
        $_SERVER['HTTP_HOST'] = "thinkup.com";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);

        // Exact JSON structure copied from Mandrill's site
        $json = '{"text":"Me worky, yo?","subject":"Testing 123","from_email":"team@thinkup.com",'.
        '"from_name":"My Crazy Custom ThinkUp","to":[{"email":"you@example.com","name":"you@example.com"}]}';

        // Compare JSON string, ignoring whitespace differences
        $this->assertEqual($json, $email_body);
    }

    public function testMandrillThinkUpLLCEndpoint() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "My Crazy Custom ");
        $config->setValue("mandrill_api_key", "1234567890");
        //From address should be team@thinkup.com when endpoint is set
        $config->setValue("thinkupllc_endpoint", 'http://example.com/thinkup/');
        $_SERVER['HTTP_HOST'] = "thinkup.com";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);

        // Exact JSON structure copied from Mandrill's site
        $json = '{"text":"Me worky, yo?","subject":"Testing 123","from_email":"team@thinkup.com",'.
        '"from_name":"My Crazy Custom ThinkUp","to":[{"email":"you@example.com","name":"you@example.com"}]}';

        // Compare JSON string, ignoring whitespace differences
        $this->assertEqual($json, $email_body);
    }

    public function testHTMLViaMandrillTemplate() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "Prefix ");
        $config->setValue("mandrill_api_key", "asdfasdfasdfadsfasd");
        $_SERVER['HTTP_HOST'] = "thinkupapp.com";
        Mailer::mailHTMLViaMandrillTemplate($to='chris@inarow.net',$subject='Test Subject','thinkup-digest',
        array('insights' =>'test insights', 'merge2' => 'Some other text'));
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);

        $decoded = json_decode($email_body);
        $this->debug($decoded);
        $this->assertEqual($subject, $decoded->subject);
        $this->assertEqual($to, $decoded->to[0]->email);
        $this->assertEqual('notifications@thinkupapp.com', $decoded->from_email);
        $this->assertEqual(2, count($decoded->global_merge_vars));
    }

    public function testHTMLViaMandrillTemplateThinkUpLLCEndpoint() {
        $config = Config::getInstance();
        $config->setValue("app_title_prefix", "Prefix ");
        $config->setValue("mandrill_api_key", "asdfasdfasdfadsfasd");
        $config->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');
        $_SERVER['HTTP_HOST'] = "thinkup.com";
        Mailer::mailHTMLViaMandrillTemplate($to='chris@inarow.net',$subject='Test Subject','thinkup-digest',
        array('insights' =>'test insights', 'merge2' => 'Some other text'));
        $email_body = Mailer::getLastMail();

        $decoded = json_decode($email_body);
        $this->assertEqual($subject, $decoded->subject);
        $this->assertEqual('team@thinkup.com', $decoded->from_email);
        $this->assertEqual($to, $decoded->to[0]->email);
        $this->assertEqual(2, count($decoded->global_merge_vars));
    }

    public function testHTMLViaMandrillTemplateExceptions() {
        $config = Config::getInstance();
        $config->setValue("mandrill_api_key", "asdfasdfasdfadsfasd");
        $exception = null;
        try {
            Mailer::mailHTMLViaMandrillTemplate('templateerror@foo.com', 'Subject', 'template', array());
        } catch (Mandrill_Unknown_Template $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
        $this->assertPattern('/Unknown template/i', $exception->getMessage());

        $exception = null;
        try {
            Mailer::mailHTMLViaMandrillTemplate('keyerror@foo.com', 'Subject', 'template', array());
        } catch (Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
        $this->assertPattern('/invalid api key/i', $exception->getMessage());
    }
}

<?php
/**
 *
 * ThinkUp/tests/WebTestOfDeleteInstance.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfDeleteInstance extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        //set up required Twitter plugin options
        $this->builders[] = FixtureBuilder::build('options', array('namespace'=>'plugin_options-1',
        'option_name'=>'oauth_consumer_secret', 'option_value'=>'testconsumersecret'));
        $this->builders[] = FixtureBuilder::build('options', array('namespace'=>'plugin_options-1',
        'option_name'=>'oauth_consumer_key', 'option_value'=>'testconsumerkey'));
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testDeleteInstance() {
        $email = 'me@example.com';
        $cookie_dao = DAOFactory::getDao('CookieDAO');
        $cookie = $cookie_dao->generateForEmail($email);

        $this->get($this->url.'/index.php');
        $this->assertNoText($email);
        $this->getBrowser()->setCookie(Session::COOKIE_NAME, $cookie);

        $this->get($this->url.'/index.php');
        $this->assertText($email);

        $this->click("Settings");
        $this->get($this->url.'/account/index.php?p=twitter#manage_plugin');
        $this->assertLink('@ev');
        $this->assertLink('@thinkupapp');
        $this->assertLink('@linkbaiter');
        $this->assertLink('@shutterbug');
        $this->assertSubmit('Delete');

        //delete existing instance
        $this->post($this->url.'/account/index.php?p=twitter', array('action'=>'Delete', 'instance_id'=>'3',
        'csrf_token' => self::TEST_CSRF_TOKEN));
        $this->assertPattern("/Account deleted\./");
        $this->assertLink('@thinkupapp');
        $this->assertLink('@linkbaiter');
        $this->assertNoLink('@shutterbug');
        $this->assertSubmit('Delete');

        //delete non-existent instance
        $this->post($this->url.'/account/index.php?p=twitter', array('action'=>'Delete', 'instance_id'=>'231',
        'csrf_token' => self::TEST_CSRF_TOKEN));
        $this->assertPattern("/Could not find that account\./");
        $this->assertLink('@thinkupapp');
        $this->assertLink('@linkbaiter');
        $this->assertSubmit('Delete');

        $this->click('Log out');
        //        $this->assertText('You have successfully logged out');
        //        $this->showSource();
        $this->assertText("Log in");

        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me2@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //delete instance with no privileges
        $this->post($this->url.'/account/index.php?p=twitter', array('action'=>'Delete', 'instance_id'=>'2',
        'csrf_token' => self::TEST_CSRF_TOKEN));

        $this->assertPattern("/Insufficient privileges\./");
    }
}
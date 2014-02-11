<?php
/**
 *
 * ThinkUp/tests/WebTestOfTwitterPublicAccountSettings.php
 *
 * Copyright (c) 2014 Eduard Cucurella
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
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Eduard Cucurella
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfTwitterPublicAccountSettings extends ThinkUpWebTestCase {
    public function setUp() {
        parent::setUp();
        //$this->builders = self::buildData();
        $this->builDataTwitter();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function builDataTwitter() {
        
        //set up some Twitter plugin options configuration
        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'oauth_consumer_key',
            'option_value'  =>  'blablabla' 
            ));

        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'oauth_consumer_secret',
            'option_value'  =>  'blablabla' 
            ));
        
        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'archive_limit',
            'option_value'  =>  '3200'
            ));
        
        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'num_twitter_errors',
            'option_value'  =>  '5'
            ));
        
        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'tweet_count_per_call',
            'option_value'  =>  '100'
            ));
        
        $this->builders[] = FixtureBuilder::build('options', array(
            'namespace'     =>  'plugin_options-1',
            'option_name'   =>  'last_daily_email',
            'option_value'  =>  '2014-01-15'
            ));

        //Add owners
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("secretpassword");
        $this->builders[] = FixtureBuilder::build('owners', array(
            'id'=>101, 
            'email'=>'admin@example.com', 
            'pwd'=>$hashed_pass,
            'is_activated'=>1,
            'is_admin'=>1, 
            'pwd_salt'=>OwnerMySQLDAO::$default_salt
        ));

        $this->builders[] = FixtureBuilder::build('owners', array(
            'id'=>102, 
            'email'=>'noadmin@example.com', 
            'pwd'=>$hashed_pass,
            'is_activated'=>1,
            'is_admin'=>0, 
            'pwd_salt'=>OwnerMySQLDAO::$default_salt
        ));

    }

    public function buildDataInstances() {

        //Add instances
        $this->builders[] = FixtureBuilder::build('instances', array(
            'id'=>201, 
            'network_user_id'=>1,
            'network_username'=>'ecucurella', 
            'is_public'=>1, 
            'network'=>'twitter', 
            'crawler_last_run'=>'-31d'
        ));

        $this->builders[] = FixtureBuilder::build('instances', array(
            'id'=>202, 
            'network_user_id'=>2, 
            'network_username'=>'vetcastellnou',
            'is_public'=>1, 
            'network'=>'twitter', 
            'crawler_last_run'=>'-31d'
        ));

        //Add instance_owner
        $this->builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>101, 
            'instance_id'=>201
        ));
        
        $this->builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>101, 
            'instance_id'=>202
        ));
        
        $this->builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>102, 
            'instance_id'=>201
        ));
    }

    public function buildDataPublicInstance() {

        //Add public instance
        $this->builders[] = FixtureBuilder::build('instances', array(
            'id'=>203, 
            'network_user_id'=>3,
            'network_username'=>'transitc55', 
            'is_public'=>1, 
            'network'=>'twitter', 
            'crawler_last_run'=>'-31d'
        ));

        //Add instance_owner
        $this->builders[] = FixtureBuilder::build('owner_instances', array(
            'owner_id'=>101, 
            'instance_id'=>203,
            'oauth_access_token'=>'blablabla',
            'oauth_access_token_secret'=>'blablabla',
            'auth_error'=>'',
            'is_twitter_referenced_instance'=>1
        ));

    }


    public function testTwitterAccountSettingsAdminWithInstance() {

        $this->buildDataInstances();
        
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'admin@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //Assert Add a public Twitter account 
        //when user is admin and have instances
        $this->get($this->url.'account/?p=twitter');
        $this->assertText('Add a public Twitter account');
        $this->assertText('Reference account:');
        $this->assertText('Twitter account:');
        $this->assertLink('Add a Twitter account');
        $this->assertSubmit('add account');
        $this->assertText('@ecucurella');
        $this->assertText('@vetcastellnou');
        $this->assertPattern('/<option value="201">ecucurella</');
        $this->assertPattern('/<option value="202">vetcastellnou</');   
        $this->assertNoPattern('/<option value="203">transitc55</');      
    }

    public function testTwitterAccountSettingsAdminWithoutInstance() {
        
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'admin@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //NO Assert Add a public Twitter account 
        //when user is admin but no authorized accounts configured
        $this->get($this->url.'account/?p=twitter');
        $this->assertNoText('Add a public Twitter account');
        $this->assertNoText('Reference account:');
        $this->assertNoText('Twitter account:');
        $this->assertLink('Add a Twitter account');
        $this->assertNoText('@ecucurella');
        $this->assertNoText('@vetcastellnou');
        $this->assertNoPattern('/<option value="201">ecucurella</');
        $this->assertNoPattern('/<option value="202">vetcastellnou</'); 
        $this->assertNoPattern('/<option value="203">transitc55</');          
    }

    public function testTwitterAccountSettingsNoAdminWithInstance() {
        
        $this->buildDataInstances();
        
        //Log in as NO admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'noadmin@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //NO Assert Add a public Twitter account 
        //when user is no admin instead has authorized accounts configured
        $this->get($this->url.'account/?p=twitter');
        $this->assertNoText('Add a public Twitter account');
        $this->assertNoText('Reference account:');
        $this->assertNoText('Twitter account:');
        $this->assertLink('Add a Twitter account');
        $this->assertText('@ecucurella');
        $this->assertNoText('@vetcastellnou');
        $this->assertNoPattern('/<option value="201">ecucurella</');
        $this->assertNoPattern('/<option value="202">vetcastellnou</');
        $this->assertNoPattern('/<option value="203">transitc55</');   

    }

    public function testTwitterAccountSettingsAdminNoPublicAccountInSelect() {

        $this->buildDataInstances();
        $this->buildDataPublicInstance();
        
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'admin@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->get($this->url.'account/?p=twitter');
        $this->assertText('Add a public Twitter account');
        $this->assertText('Reference account:');
        $this->assertText('Twitter account:');
        $this->assertLink('Add a Twitter account');
        $this->assertSubmit('add account');
        $this->assertText('@ecucurella');
        $this->assertText('@vetcastellnou');
        $this->assertText('Public Account');
        $this->assertText('@transitc55');
        $this->assertPattern('/<option value="201">ecucurella</');
        $this->assertPattern('/<option value="202">vetcastellnou</');       
        $this->assertNoPattern('/<option value="203">transitc55</');   
    }

}

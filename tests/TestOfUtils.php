<?php
/**
 *
 * ThinkUp/tests/TestOfUtils.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau
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
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUtils extends ThinkUpUnitTestCase {

    public function testgetPluginViewDirectory() {
        $config = Config::getInstance();
        $path = Utils::getPluginViewDirectory('twitter');
        $this->assertEqual(realpath($path), realpath(THINKUP_WEBAPP_PATH.'plugins/twitter/view'));

        $path = Utils::getPluginViewDirectory('sweetmaryjane');
        $this->assertEqual(realpath($path), realpath($config->getValue('source_root_path').
        '/webapp/plugins/sweetmaryjane/view/'));
    }

    public function testGetPercentage(){
        $this->assertEqual(Utils::getPercentage(50, 100), 50);
        $this->assertEqual(Utils::getPercentage(250, 1000), 25);
        $this->assertEqual(Utils::getPercentage('not', 'anumber'), 0);
        $this->assertEqual(Utils::getPercentage(150, 50), 300);
    }

    public function testValidateEmail(){
        //do validate valid public internet addresses
        $this->assertTrue(Utils::validateEmail('h@bit.ly'));
        $this->assertTrue(Utils::validateEmail('you@example.com'));
        $this->assertTrue(Utils::validateEmail('youfirstname.yourlastname@example.co.uk'));
        //do validate local addresses
        $this->assertTrue(Utils::validateEmail('yaya@yaya'));
        $this->assertTrue(Utils::validateEmail('me@localhost'));
        //don't validate addresses with invalid chars
        $this->assertFalse(Utils::validateEmail('yaya'));
        $this->assertFalse(Utils::validateEmail('me@localhost@notavalidaddress'));
        $this->assertFalse(Utils::validateEmail('me@local host'));
        $this->assertFalse(Utils::validateEmail('me@local#host'));
    }

    public function testValidateURL(){
        $this->assertFalse(Utils::validateURL('yaya'));
        $this->assertFalse(Utils::validateURL('http:///thediviningwand.com'));
        $this->assertTrue(Utils::validateURL('http://asdf.com'));
        $this->assertTrue(Utils::validateURL('https://asdf.com'));
    }

    public function testIndentJSON() {
        $data = array('jam', 'jelly', 'ham', 'biscuits', array ( 'cola', 'beer', 'grapefruit juice' ));

        $test_str = '[
    "jam",
    "jelly",
    "ham",
    "biscuits",
    [
        "cola",
        "beer",
        "grapefruit juice"
    ]
]';

        $json_data = json_encode($data);
        $indented_json_data = Utils::indentJSON($json_data);
        $this->assertEqual($test_str, $indented_json_data);
        $this->assertNotEqual($json_data, $indented_json_data);

        $data = new stdClass();
        $data->name = 'Dave';
        $data->job = 'Fixing stuff.';
        $data->link = 'http://thereifixedit.com';
        $data->spouse = new stdClass();
        $data->spouse->name = 'Jill';
        $data->spouse->job = 'CEO of MadeUp inc.';

        $test_str = '{
    "name":"Dave",
    "job":"Fixing stuff.",
    "link":"http:\/\/thereifixedit.com",
    "spouse":{
        "name":"Jill",
        "job":"CEO of MadeUp inc."
    }
}';

        $json_data = json_encode($data);
        $indented_json_data = Utils::indentJSON($json_data);
        $this->assertEqual($test_str, $indented_json_data);
        $this->assertNotEqual($json_data, $indented_json_data);

        $data = new stdClass();
        $data->test1 = 'This text element should totally not wrap "just because" it ends with a :\\';
        $data->test2 = 'What if I end with double slashes!? \\\\';
        $data->test3 = 'Oh, "just because :\ ", she said';

        $test_str = '{
    "test1":"This text element should totally not wrap \"just because\" it ends with a :\\\\",
    "test2":"What if I end with double slashes!? \\\\\\\\",
    "test3":"Oh, \"just because :\\\\ \", she said"
}';
        $json_data = json_encode($data);
        $indented_json_data = Utils::indentJSON($json_data);
        $this->debug($indented_json_data);
        $this->assertEqual($test_str, $indented_json_data);
    }

    public function testConvertNumericStrings() {
        // integer
        $test_str = '"123456789"';
        $number = '123456789';
        $converted = Utils::convertNumericStrings($test_str);
        $this->assertEqual($converted, $number);

        // float
        $test_str = '"1234.56789"';
        $number = '1234.56789';
        $converted = Utils::convertNumericStrings($test_str);
        $this->assertEqual($converted, $number);

        // not a number
        $test_str = '"123456789s"';
        $number = '"123456789s"';
        $converted = Utils::convertNumericStrings($test_str);
        $this->assertEqual($converted, $number);

        // not a float
        $test_str = '"12345.6789s"';
        $number = '"12345.6789s"';
        $converted = Utils::convertNumericStrings($test_str);
        $this->assertEqual($converted, $number);

        // two dots, not a number
        $test_str = '"12345.6.789"';
        $number = '"12345.6.789"';
        $converted = Utils::convertNumericStrings($test_str);
        $this->assertEqual($converted, $number);
    }

    public function testMergeSQLVars() {
        $sql = "SELECT u.*, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow, ".
        "round(post_count/(datediff(curdate(), joined)), 2) AS avg_tweets_per_day FROM tu_users AS u INNER JOIN ".
        "tu_follows AS f ON u.user_id = f.follower_id WHERE f.user_id = :userid AND f.network=:network AND ".
        "f.network=u.network AND active=1 AND follower_count > 10000 AND friend_count > 0 ORDER BY ".
        "LikelihoodOfFollow ASC, u.follower_count DESC LIMIT :count ;";
        $vars = array(
            ':userid'=>1001,
            ':network'=>'some_network',
            ':count'=>123
        );

        $merged_sql = Utils::mergeSQLVars($sql, $vars);
        $this->debug($merged_sql);
        $expected_merged_sql = "SELECT u.*, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow, ".
        "round(post_count/(datediff(curdate(), joined)), 2) AS avg_tweets_per_day FROM tu_users AS u INNER JOIN ".
        "tu_follows AS f ON u.user_id = f.follower_id WHERE f.user_id = 1001 AND f.network='some_network' AND ".
        "f.network=u.network AND active=1 AND follower_count > 10000 AND friend_count > 0 ORDER BY ".
        "LikelihoodOfFollow ASC, u.follower_count DESC LIMIT 123 ;";
        $this->assertEqual($merged_sql, $expected_merged_sql);
    }

    public function testSetDefaultTimezonePHPini() {
        // ini value present, should be set to that
        ini_set('date.timezone','America/New_York');
        Utils::setDefaultTimezonePHPini();
        $tz = ini_get('date.timezone');
        //$tz = date_default_timezone_get();
        $this->assertEqual($tz, 'America/New_York');
    }

    public function testPredictNextMilestoneDate() {
        //No next milestone prediced
        $this->assertNull(Utils::predictNextMilestoneDate(10, 0));

        //Next milestone in 5 units of time
        $expected = array('next_milestone'=>100, 'will_take'=>5);
        $this->assertEqual(Utils::predictNextMilestoneDate(75, 5), $expected);
    }
    public function testGetLastSaturday()  {
        $last_saturday = Utils::getLastSaturday('11/11/2011');
        $this->assertEqual('11/5', $last_saturday);

        $last_saturday = Utils::getLastSaturday('11/6/2011');
        $this->assertEqual('11/5', $last_saturday);
    }

    public function testGetSiteRootPathFromFileSystem() {
        // function assumes $_SERVER['PHP_SELF'] is set
        // it only is in the web server context so we set it here to test
        $_SERVER['PHP_SELF'] = Config::getInstance()->getValue('site_root_path').'index.php';
        $filesystem_site_root_path = Utils::getSiteRootPathFromFileSystem();
        $cfg_site_root_path = Config::getInstance()->getValue('site_root_path');
        $this->assertEqual($filesystem_site_root_path, $cfg_site_root_path);

        //API calls
        $_SERVER['PHP_SELF'] = Config::getInstance()->getValue('site_root_path').'api/v1/session/login.php';
        $filesystem_site_root_path = Utils::getSiteRootPathFromFileSystem();
        $cfg_site_root_path = Config::getInstance()->getValue('site_root_path');
        $this->assertEqual($filesystem_site_root_path, $cfg_site_root_path);
    }

    public function testGetApplicationRequestURI() {
        // function assumes $_SERVER['REQUEST_URI'] is set
        // it only is in the web server context so we set it here to test
        $_SERVER['REQUEST_URI'] = Config::getInstance()->getValue('site_root_path').'index.php';
        $this->debug($_SERVER['REQUEST_URI']);
        $request_uri = Utils::getApplicationRequestURI();
        $this->assertEqual($request_uri, 'index.php');

        $_SERVER['REQUEST_URI'] = Config::getInstance()->getValue('site_root_path').'account/?p=facebook';
        $request_uri = Utils::getApplicationRequestURI();
        $this->assertEqual($request_uri, 'account/?p=facebook');

        //API calls
        $_SERVER['REQUEST_URI'] = Config::getInstance()->getValue('site_root_path').'api/v1/session/login.php';
        $request_uri = Utils::getApplicationRequestURI();
        $this->assertEqual($request_uri, 'api/v1/session/login.php');
    }

    public function testGetApplicationHostName() {
        //no $_SERVER vars set, but with application setting set
        $builder = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'testservername') );
        $host_name = Utils::getApplicationHostName();
        $expected_host_name = 'testservername';
        $this->assertEqual($host_name, $expected_host_name);

        //SERVER_NAME, not HTTP_HOST
        $_SERVER['HTTP_HOST'] = null;
        $_SERVER['SERVER_NAME'] = 'mytestservername';
        $host_name = Utils::getApplicationHostName();
        $expected_host_name = 'mytestservername';
        $this->assertEqual($host_name, $expected_host_name);

        //HTTP_HOST, not SERVER_NAME
        $_SERVER['HTTP_HOST'] = 'myothertestservername';
        $_SERVER['SERVER_NAME'] = null;
        $host_name = Utils::getApplicationHostName();
        $expected_host_name = 'myothertestservername';
        $this->assertEqual($host_name, $expected_host_name);
    }

    public function testGetApplicationURL() {
        $cfg = Config::getInstance();
        $cfg->setValue('site_root_path', '/my/path/to/thinkup/');

        //no $_SERVER vars set, but with application setting set
        $builder = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'server_name', 'option_value'=>'testservername') );
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://testservername/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //SERVER_NAME, not HTTP_HOST
        $_SERVER['HTTP_HOST'] = null;
        $_SERVER['SERVER_NAME'] = 'mytestservername';
        $_SERVER['HTTPS'] = null;
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestservername/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //no SSL
        $_SERVER['SERVER_NAME'] = null;
        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['HTTPS'] = null;
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //no SSL but with $_SERVER['HTTPS'] set to empty string
        $_SERVER['HTTPS'] = '';
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //with SSL
        $_SERVER['HTTPS'] = true;
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'https://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //nonstandard port
        $_SERVER['HTTPS'] = null;
        $_SERVER['SERVER_PORT'] = '1003';
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestthinkup:1003/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //standard port 80
        $_SERVER['HTTPS'] = null;
        $_SERVER['SERVER_PORT'] = '80';
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //SSL standard port 443
        $_SERVER['HTTPS'] = true;
        $_SERVER['SERVER_PORT'] = '443';
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'https://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //no port set
        $_SERVER['HTTPS'] = null;
        $_SERVER['SERVER_PORT'] = '80';
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://mytestthinkup/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //localhost
        $_SERVER['HTTP_HOST'] = "localhost";
        $utils_url = Utils::getApplicationURL();
        $expected_url = 'http://localhost/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //localhost - return IP
        $_SERVER['HTTP_HOST'] = "localhost";
        $utils_url = Utils::getApplicationURL(true);
        $expected_url = 'http://127.0.0.1/my/path/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //with spaces in site_rooth_path
        $_SERVER['HTTP_HOST'] = "localhost";
        $cfg->setValue('site_root_path', '/my/path and this space/to/thinkup/');
        $utils_url = Utils::getApplicationURL(false);
        $expected_url = 'http://localhost/my/path+and+this+space/to/thinkup/';
        $this->assertEqual($utils_url, $expected_url);

        //with capital letters in site_root_path
        $_SERVER['HTTP_HOST'] = "localhost";
        $cfg->setValue('site_root_path', '/ThinkUp/');
        $utils_url = Utils::getApplicationURL(false);
        $expected_url = 'http://localhost/ThinkUp/';
        $this->assertEqual($utils_url, $expected_url);

        //with capital letters and spaces in site_root_path
        $_SERVER['HTTP_HOST'] = "localhost";
        $cfg->setValue('site_root_path', '/Think Up/');
        $utils_url = Utils::getApplicationURL(false);
        $expected_url = 'http://localhost/Think+Up/';
        $this->assertEqual($utils_url, $expected_url);

        //with capital letters in host and in site_root_path
        $_SERVER['HTTP_HOST'] = "LocalHost";
        $cfg->setValue('site_root_path', '/Think Up/');
        $utils_url = Utils::getApplicationURL(false);
        $expected_url = 'http://localhost/Think+Up/';
        $this->assertEqual($utils_url, $expected_url);
    }

    public function testOfIsThinkUpLLC() {
        $cfg = Config::getInstance();
        $cfg->setValue('thinkupllc_endpoint', null);
        $this->assertFalse(Utils::isThinkUpLLC());

        $cfg->setValue('thinkupllc_endpoint', 'http://example.com/thinkup/');
        $this->assertTrue(Utils::isThinkUpLLC());
    }
}

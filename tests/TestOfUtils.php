<?php
/**
 *
 * ThinkUp/tests/TestOfUtils.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2011 Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUtils extends ThinkUpBasicUnitTestCase {

    public function testRootPathConstant() {
        Utils::defineConstantRootPath();

        $this->assertTrue( defined('THINKUP_ROOT_PATH') );
        $this->assertTrue( is_readable(THINKUP_ROOT_PATH) );
    }

    public function testWebappPathConstant() {
        Utils::defineConstantWebappPath();

        $this->assertTrue( defined('THINKUP_WEBAPP_PATH') );
        $this->assertTrue( is_readable(THINKUP_WEBAPP_PATH) );
    }

    public function testBaseUrlConstant() {
        Utils::defineConstantBaseUrl();

        $this->assertTrue( defined('THINKUP_BASE_URL') );
    }

    public function testgetPluginViewDirectory() {
        $config = Config::getInstance();
        $path = Utils::getPluginViewDirectory('twitter');
        $this->assertEqual($path, $config->getValue('source_root_path').'webapp/plugins/twitter/view/');

        $path = Utils::getPluginViewDirectory('sweetmaryjane');
        $this->assertEqual($path, $config->getValue('source_root_path').'webapp/plugins/sweetmaryjane/view/');
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
        $data = array(
            'jam',
            'jelly',
            'ham',
            'biscuits',
            array (
                'cola',
                'beer',
                'grapefruit juice'
            )
        );

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
}
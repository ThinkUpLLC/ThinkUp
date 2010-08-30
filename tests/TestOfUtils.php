<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUtils extends ThinkUpBasicUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('Utils class test');
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
        $this->assertFalse(Utils::validateEmail('yaya'));
        $this->assertFalse(Utils::validateEmail('yaya@yaya'));
        $this->assertTrue(Utils::validateEmail('h@bit.ly'));
        $this->assertTrue(Utils::validateEmail('you@example.com'));
    }

    public function testValidateURL(){
        $this->assertFalse(Utils::validateURL('yaya'));
        $this->assertFalse(Utils::validateURL('http:///thediviningwand.com'));
        $this->assertTrue(Utils::validateURL('http://asdf.com'));
        $this->assertTrue(Utils::validateURL('https://asdf.com'));
    }
}
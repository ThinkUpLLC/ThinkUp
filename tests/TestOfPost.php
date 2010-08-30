<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Post class
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfPost extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Post class test');
    }

    public function testExtractURLs() {
        $testme= "blah blah blah http:///badurl.com d http://bit.ly and http://example.org";
        $urls = Post::extractURLs($testme);
        $expected = array ('http:///badurl.com', 'http://bit.ly', 'http://example.org');
        $this->assertIdentical($expected, $urls);
        //@TODO Finesse the regex to NOT match URLs with triple slashes, http:///badurl.com
    }
}
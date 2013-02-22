<?php
/**
 *
 * ThinkUp/tests/TestOfSmartyModifierLinkUsernames.php
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/view/plugins/modifier.link_usernames.php';
require_once THINKUP_WEBAPP_PATH.'_lib/view/plugins/modifier.link_usernames_to_twitter.php';

class TestOfSmartyModifierLinkUsernames extends ThinkUpBasicUnitTestCase {
    /**
     *
     * @var array string
     */
    var $test_tweets;
    /**
     *
     * @var array string
     */
    var $internally_linked_tweets;
    /**
     *
     * @var array string
     */
    var $externally_linked_tweets;

    public function setUp() {
        $config = Config::getInstance();
        $this->test_tweets = array(
        "Hey @anildash think this up!",
        "If you're interested, @ me details",
        ".@anildash thinks so",
        "This is a tweet with multiple usernames like @waxpancake and @thinkupapp",
        "Blah blah blah (@username). Blah blah");

        $this->internally_linked_tweets = array(
        'Hey <a href="' . $config->getValue('site_root_path') .
        'user/?u=anildash&n=twitter&i=me">@anildash</a> think this up!',
        "If you're interested, @ me details",
        '.<a href="'.$config->getValue('site_root_path') .'user/?u=anildash&n=twitter&i=me">@anildash</a> thinks so',
        'This is a tweet with multiple usernames like <a href="' . $config->getValue('site_root_path') .
        'user/?u=waxpancake&n=twitter&i=me">@waxpancake</a> '.
        'and <a href="' . $config->getValue('site_root_path') . 'user/?u=thinkupapp&n=twitter&i=me">@thinkupapp</a>',
        'Blah blah blah (<a href="' . $config->getValue('site_root_path') .
        'user/?u=username&n=twitter&i=me">@username</a>). Blah blah');

        $this->externally_linked_tweets = array(
        'Hey <a href="https://twitter.com/intent/user?screen_name=anildash">@anildash</a> think this up!',
        "If you're interested, @ me details",
        '.<a href="https://twitter.com/intent/user?screen_name=anildash">@anildash</a> thinks so',
        'This is a tweet with multiple usernames like '.
        '<a href="https://twitter.com/intent/user?screen_name=waxpancake">@waxpancake</a> '.
        'and <a href="https://twitter.com/intent/user?screen_name=thinkupapp">@thinkupapp</a>',
        'Blah blah blah (<a href="https://twitter.com/intent/user?screen_name=username">@username</a>). Blah blah');
    }

    public function testLinks() {
        //test internal links
        foreach ($this->test_tweets as $index => $test_tweet) {
            $linked_tweet = smarty_modifier_link_usernames($test_tweet, "me", "twitter");
            $this->assertEqual($this->internally_linked_tweets[$index], $linked_tweet);
        }

        //test Twitter.com links
        foreach ($this->test_tweets as $index => $test_tweet) {
            $linked_tweet = smarty_modifier_link_usernames_to_twitter($test_tweet, "me", "twitter");
            $this->assertEqual($this->externally_linked_tweets[$index], $linked_tweet);
        }
    }
}

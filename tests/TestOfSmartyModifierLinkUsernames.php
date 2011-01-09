<?php
/**
 *
 * ThinkUp/tests/TestOfSmartyModifierLinkUsernames.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/view/plugins/modifier.link_usernames.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/view/plugins/modifier.link_usernames_to_twitter.php';

class TestOfSmartyModiferLinkUsernames extends ThinkUpBasicUnitTestCase {
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

    public function __construct() {
        $this->UnitTestCase('Link Twitter usernames Smarty modifier test');
        $this->test_tweets = array(
        "Hey @anildash think this up!", 
        "If you're interested, @ me details", 
        ".@anildash thinks so",
        "This is a tweet with multiple usernames like @waxpancake and @thinkupapp",
        "Blah blah blah (@username). Blah blah");

        $this->internally_linked_tweets = array(
        'Hey <a href="/user/?u=anildash&n=twitter&i=me">@anildash</a> think this up!', 
        "If you're interested, @ me details", 
        '.<a href="/user/?u=anildash&n=twitter&i=me">@anildash</a> thinks so',
        'This is a tweet with multiple usernames like <a href="/user/?u=waxpancake&n=twitter&i=me">@waxpancake</a> '.
        'and <a href="/user/?u=thinkupapp&n=twitter&i=me">@thinkupapp</a>',
        'Blah blah blah (<a href="/user/?u=username&n=twitter&i=me">@username</a>). Blah blah');

        $this->externally_linked_tweets = array(
        'Hey <a href="http://twitter.com/anildash">@anildash</a> think this up!', 
        "If you're interested, @ me details", 
        '.<a href="http://twitter.com/anildash">@anildash</a> thinks so',
        'This is a tweet with multiple usernames like <a href="http://twitter.com/waxpancake">@waxpancake</a> '.
        'and <a href="http://twitter.com/thinkupapp">@thinkupapp</a>',
        'Blah blah blah (<a href="http://twitter.com/username">@username</a>). Blah blah');
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

<?php
/**
 *
 * ThinkUp/tests/TestOfPost.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * Test of Post class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfPost extends ThinkUpBasicUnitTestCase {

    public function testExtractURLs() {
        $test_patterns = array (
         "Introducing the ThinkUp developers mailing list http://bit.ly/gXpdUZ"=> array ('http://bit.ly/gXpdUZ')
        ,"http://j.mp/g2F037 good advice (Mad Men-illustrated) for women in tech"=> array ('http://j.mp/g2F037')
        ,"blah blah blah http:///badurl.com d http://bit.ly and http://example.org"=> array ('http://bit.ly', 'http://example.org')
        ,"blah blah blah http:///badurl.com d HTTP://yo.com/exi.xml?hi=yes and http://example.org/blah/yoiadsf/934324/" 
        => array ('HTTP://yo.com/exi.xml?hi=yes', 'http://example.org/blah/yoiadsf/934324/')
        ,"I bought the book at http://amazon.com. You should read it, too"=> array ('http://amazon.com')
        ,"So, Who's on first? check “http://culturalwormhole.blogspot.com/” for more."=> array ('http://culturalwormhole.blogspot.com/')
        ,"We know all about that (http://friendoflou.com), but we're not impressed." => array ('http://friendoflou.com')
        ,"A more terse, yet still friendly introduction notme.com norme.com/ bit.ly/gXpdUZ and blah yo.com/exi.xml?hi=yes blah"
        => array ('http://bit.ly/gXpdUZ', 'http://yo.com/exi.xml?hi=yes')
        ,"tersely www.google.com notme.google.com www.nytimes.com"=> array ('http://www.google.com', 'http://www.nytimes.com')
        ,"would you believe this url?  http://foo.com/more_(than)_one_(parens)   " => array('http://foo.com/more_(than)_one_(parens)')
        ,"detects embedding <http://foo.com/blah_blah/> nicely <tag>http://example.com</tag>" 
        => array('http://foo.com/blah_blah/', 'http://example.com')
        );      
        
            foreach ($test_patterns as $testme=>$expected) {
                $urls = Post::extractURLs($testme);
                $this->assertIdentical($expected, $urls, $testme.' %s');   
                $this->assertTrue(array_reduce(array_map('Utils::validateURL', $urls), 
                                                          function($a, $b) {return $a && $b;}, true));          
            }
    }

    public function testExtractMentions() {
        $test_str = '@samwhoo woot yay win cake';
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@samwhoo');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = '@samwhoo @anildash woot yay win cake';
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@samwhoo', '@anildash');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = '@samwhoo @anildash @ginatrapani woot yay win cake';
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@samwhoo', '@anildash', '@ginatrapani');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = 'woot yay win cake #game';
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array();
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = "@sam'whoo woot yay win cake";
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@sam');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = "@sam#whoo woot yay win cake";
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@sam');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = "@sam/whoo woot yay win cake";
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@sam');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = "@sam.whoo woot yay win cake";
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@sam');
        $this->assertIdentical($mentions, $actual_mentions);

        $test_str = "@sam-whoo woot yay win cake";
        $mentions = Post::extractMentions($test_str);
        $actual_mentions = array('@sam');
        $this->assertIdentical($mentions, $actual_mentions);
    }

    public function testExtractHashtags() {
        $test_str = '@samwhoo woot yay win #cake';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#cake');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay #win #cake';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#win', '#cake');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot #yay #win #cake';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#yay', '#win', '#cake');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay win cake';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array();
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay win #ca-ke';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#ca');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay win #ca@ke';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#ca');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay win #ca.ke';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#ca');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = "@samwhoo woot yay win #ca'ke";
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#ca');
        $this->assertIdentical($hashtags, $actual_hashtags);

        $test_str = '@samwhoo woot yay win #ca/ke';
        $hashtags = Post::extractHashtags($test_str);
        $actual_hashtags = array('#ca');
        $this->assertIdentical($hashtags, $actual_hashtags);
    }
}
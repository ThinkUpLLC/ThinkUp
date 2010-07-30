<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpWebTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.FollowMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';


class TestOfPublicTimeline extends ThinkUpWebTestCase {

    function setUp() {
        parent::setUp();

        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $q = "INSERT INTO tu_owners (id, email, pwd, is_activated) VALUES (1, 'me@example.com', '"
        .$cryptpass."', 1)";
        $this->db->exec($q);

        //Add instance
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 1234,
        'thinkupapp', 1)";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey',
        'avatar.jpg');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private',
        'Private Poster', 'avatar.jpg', 1);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (17,
        'thinkupapp', 'ThinkUpers', 'avatar.jpg', 0, 10);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (18,
        'shutterbug', 'Shutter Bug', 'avatar.jpg', 0, 10);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (19,
        'linkbaiter', 'Link Baiter', 'avatar.jpg', 0, 10);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_user_errors (user_id, error_code, error_text, error_issued_to_user_id) VALUES (15, 404,
        'User not found', 13);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen) VALUES (13, 12, '1/1/2006');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen) VALUES (13, 14, '1/1/2006');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen) VALUES (13, 15, '1/1/2006');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen) VALUES (13, 16, '1/1/2006');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen) VALUES (16, 12, '1/1/2006');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (18, 'shutterbug', 1);";
        $this->db->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (19, 'linkbaiter', 1);";
        $this->db->exec($q);

        $counter = 0;
        while ($counter < 40) {
            $reply_or_forward_count = $counter + 200;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', 
            $reply_or_forward_count, $reply_or_forward_count);";
            $this->db->exec($q);

            $counter++;
        }

        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($post_id, 18, 'shutterbug', 
            'Shutter Bug', 'avatar.jpg', 'This is image post $counter', 'web', 
            '2006-01-02 00:$pseudo_minute:00', 0, 0);";
            $this->db->exec($q);

            $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image) VALUES
            ('http://example.com/".$counter."', 'http://example.com/".$counter.".jpg', '', 0, $post_id, 1);";
            $this->db->exec($q);

            $counter++;
        }

        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($post_id, 19, 'linkbaiter', 
            'Link Baiter', 'avatar.jpg', 'This is link post $counter', 'web', 
            '2006-03-01 00:$pseudo_minute:00', 0, 0);";
            $this->db->exec($q);

            $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image) VALUES (
            'http://example.com/".$counter."', 'http://example.com/".$counter.".html', 'Link $counter', 
            0, $post_id, 0);";
            $this->db->exec($q);

            $counter++;
        }
    }

    function tearDown() {
        parent::tearDown();
    }

    function testPublicTimelineAndPages() {
        $this->get($this->url.'/public.php');
        $this->assertTitle('Public Timeline | ThinkUp');
        $this->assertText('Log In');
        $this->click('Log In');
        $this->assertTitle('Log in | ThinkUp');
        $this->assertText('Register');
        $this->click('Register');
        $this->assertTitle('Register | ThinkUp');
        $this->assertText('Forgot password');
        $this->click('Forgot password');
        $this->assertTitle('ThinkUp');
    }

    function testNextAndPreviousControls() {
        $categories[] = "";
        $categories[] = "?v=mostreplies";
        $categories[] = "?v=mostretweets";

        foreach ($categories as $category) {

            $this->get($this->url.'/public.php'.$category);
            $this->assertTitle('Public Timeline | ThinkUp');

            $this->assertText('ev');
            $this->assertText('This is post 39');
            $this->assertText('This is post 25');
            $this->assertText('Page 1 of 3');

            $this->assertLinkById("next_page");
            $this->assertNoLinkById("prev_page");

            $this->clickLinkById("next_page");

            $this->assertText('Page 2 of 3');
            $this->assertText('This is post 24');
            $this->assertText('This is post 10');
            $this->assertLinkById("next_page");
            $this->assertLinkById("prev_page");

            $this->clickLinkById("next_page");

            $this->assertNoLinkById("next_page");
            $this->assertLinkById("prev_page");
            $this->assertText('This is post 9');
            $this->assertText('This is post 0');
            $this->assertText('Page 3 of 3');
        }
    }

    function testNextAndPreviousPhotosControls() {
        $this->get($this->url.'/public.php?v=photos');
        $this->assertTitle('Public Timeline | ThinkUp');

        $this->assertText('shutterbug');
        $this->assertText('This is image post 39');
        $this->assertText('This is image post 25');
        $this->assertText('Page 1 of 3');

        $this->assertLinkById("next_page");
        $this->assertNoLinkById("prev_page");

        $this->clickLinkById("next_page");

        $this->assertText('Page 2 of 3');
        $this->assertText('This is image post 24');
        $this->assertText('This is image post 10');
        $this->assertLinkById("next_page");
        $this->assertLinkById("prev_page");

        $this->clickLinkById("next_page");

        $this->assertNoLinkById("next_page");
        $this->assertLinkById("prev_page");
        $this->assertText('This is image post 9');
        $this->assertText('This is image post 0');
        $this->assertText('Page 3 of 3');

    }

    function testNextAndPreviousLinksControls() {
        $this->get($this->url.'/public.php?v=links');
        $this->assertTitle('Public Timeline | ThinkUp');

        $this->assertText('linkbaiter');
        $this->assertText('This is link post 39');
        $this->assertText('This is link post 25');
        $this->assertText('Page 1 of 3');

        $this->assertLinkById("next_page");
        $this->assertNoLinkById("prev_page");

        $this->clickLinkById("next_page");

        $this->assertText('Page 2 of 3');
        $this->assertText('This is link post 24');
        $this->assertText('This is link post 10');
        $this->assertLinkById("next_page");
        $this->assertLinkById("prev_page");

        $this->clickLinkById("next_page");

        $this->assertNoLinkById("next_page");
        $this->assertLinkById("prev_page");
        $this->assertText('This is link post 9');
        $this->assertText('This is link post 0');
        $this->assertText('Page 3 of 3');
    }
}

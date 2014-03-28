<?php
/**
 *
 * ThinkUp/tests/TestOfChartHelper.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test Of ChartHelper
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfChartHelper extends ThinkUpUnitTestCase {
    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    public function testGetPostActivityVisualizationData() {
        $hot_posts = array(
        (object)array(
                'post_text' => 'First Û Post',
                'favlike_count_cache' => 1,
                'all_retweets' => 2,
                'reply_count_cache' => 3,
        ),
        (object)array(
                'post_text' => 'Second Post',
                'favlike_count_cache' => 10,
                'all_retweets' => 20,
                'reply_count_cache' => 30,
        )
        );

        $result = ChartHelper::getPostActivityVisualizationData($hot_posts, 'twitter');
        $this->assertEqual(gettype($result), 'string');

        $visualization_object = json_decode($result);
        $this->assertEqual(sizeof($visualization_object->rows), 2);
        $this->assertEqual(sizeof($visualization_object->cols), 4);

        $this->assertEqual($visualization_object->cols[0]->label, 'Tweet');
        $this->assertEqual($visualization_object->cols[1]->label, 'Replies');
        $this->assertEqual($visualization_object->cols[2]->label, 'Retweets');
        $this->assertEqual($visualization_object->cols[3]->label, 'Favorites');

        //Different PHP versions handle this differently
        if (version_compare(phpversion(), '5.4', '<')) {
            $this->assertEqual($visualization_object->rows[0]->c[0]->v, 'First  Post...');
        } else {
            $this->assertEqual($visualization_object->rows[0]->c[0]->v, 'First ? Post...');
        }
        $this->assertEqual($visualization_object->rows[0]->c[1]->v, 3);
        $this->assertEqual($visualization_object->rows[0]->c[2]->v, 2);
        $this->assertEqual($visualization_object->rows[0]->c[3]->v, 1);

        $result = ChartHelper::getPostActivityVisualizationData($hot_posts, 'facebook');
        $this->assertEqual(gettype($result), 'string');

        $visualization_object = json_decode($result);
        $this->assertEqual(sizeof($visualization_object->rows), 2);
        $this->assertEqual(sizeof($visualization_object->cols), 4);

        $this->assertEqual($visualization_object->cols[0]->label, 'Post');
        $this->assertEqual($visualization_object->cols[1]->label, 'Comments');
        $this->assertEqual($visualization_object->cols[2]->label, 'Shares');
        $this->assertEqual($visualization_object->cols[3]->label, 'Likes');

        $this->assertEqual($visualization_object->rows[1]->c[0]->v, 'Second Post...');
        $this->assertEqual($visualization_object->rows[1]->c[1]->v, 30);
        $this->assertEqual($visualization_object->rows[1]->c[2]->v, 20);
        $this->assertEqual($visualization_object->rows[1]->c[3]->v, 10);
    }
}

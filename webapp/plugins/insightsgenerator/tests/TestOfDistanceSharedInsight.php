<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfDistanceSharedInsight.php
 *
 * Copyright (c) Gareth Brady
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
 * Test of Distance Shared Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/distanceshared.php';

class TestOfDistanceSharedInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(3); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new DistanceSharedInsight();
        $this->assertIsA($insight_plugin, 'DistanceSharedInsight' );
    }

    public function testNoDistance() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'author_user_id' => 42,
        'network' => $this->instance->network,
        'post_text' => "I got to start exercise soon."));

        $insight_plugin = new DistanceSharedInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testMultipleDistanceFromDifferentAppsSharedMoreThisWeek() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => ' I just ran 1.01 mi with Nike+. http://go.nike.com/  #nikeplus',
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'I just ran 7.74 km with Nike+. http://go.nike.com/  #nikeplus', 
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>3,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out running 4.01 km with #Endomondo.',
            'pub_date' => date('Y-m-d', strtotime('-2 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>4,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'Was out cycling 13.23 miles with #Endomondo.',
             'pub_date' => date('Y-m-d', strtotime('-2 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>5,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out running 7,3 km in 43min with #Endomondo', 
            'pub_date' => date('Y-m-d', strtotime('-3 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>6,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'Just completed a 13.94 mi bike - Have you worked out this week? #RunKeeper', 
             'pub_date' => date('Y-m-d', strtotime('-3 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>7,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'Just completed a 5.01 km run - Interval training was hard in the rain #RunKeeper', 
             'pub_date' => date('Y-m-d', strtotime('-4 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>8,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'Just completed a 3.23 km run in mission “Lay of the Land” of #zombiesrun:
              collected 9 supplies, outran a zombie mob', 
             'pub_date' => date('Y-m-d', strtotime('-4 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>9,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'Just completed a 2.28 mile run in mission “The Man Who Sold the World” of #zombiesrun:
              collected 31 supplies',
             'pub_date' => date('Y-m-d', strtotime('-5 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>10,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => '4.3 miles in 1hr 8mins - thats ok isnt it for a brisk dog walk? #MapMyFitness ', 
             'pub_date' => date('Y-m-d', strtotime('-5 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>11,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => '4.66 km mountain run done!! #OnTopOfTheWorld #MapMyRun', 
             'pub_date' => date('Y-m-d', strtotime('-6 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>12,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'just finished a Runtastic race cycling of 10.34 km in 1h 55m with
              #Runtastic Road Bike Android app', 
             'pub_date' => date('Y-m-d', strtotime('-2 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>13,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'just finished a Runtastic walking of 4.35 mi in 2h 11m with
              #Runtastic Pedometer iPhone app', 'pub_date' => date('Y-m-d', strtotime('-6 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>14,
             'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
             'post_text' => 'I prefer to use #nikeplus rather than #runtastic',
             'pub_date' => date('Y-m-d', strtotime('-7 day'))));
       $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>15,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out cycling 3.82 miles with #Endomondo.',
            'pub_date' => date('Y-m-d', strtotime('-9 days'))));
        $insight_plugin = new DistanceSharedInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $insight_text = "That's even better than last week!";
        $this->assertNotEqual(false, strpos($result->text, "shared <strong>65.39 miles</strong>. $insight_text"));
        $this->assertEqual($result->headline, "Nobody can call @janesmith a couch potato!");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleDistanceFromDifferentAppsSharedLessThisWeek() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => ' I just ran 1.00 mi with Nike+. http://go.nike.com/  #nikeplus',
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'I just ran 1.00 km with Nike+. http://go.nike.com/  #nikeplus',
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>3,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out running 1.00 km with #Endomondo.', 
            'pub_date' => date('Y-m-d', strtotime('-8 days'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>4,
            'author_username'=> 'janesmith', 'author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out cycling 1.10 miles with #Endomondo.', 
            'pub_date' => date('Y-m-d', strtotime('-8 days'))));
       
        $insight_plugin = new DistanceSharedInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);

        $this->assertNotNull($result);
        $insight_text = "That's virtually the same as last week!";
        $this->assertNotEqual(false,strpos($result->text,"shared <strong>1.62 miles</strong>. $insight_text"));
        $this->assertEqual($result->headline, "Nobody can call @janesmith a couch potato!");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleDistanceFromDifferentAppsSharedSameThisWeek() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
            'post_text' => ' I just ran 1.00 mi with Nike+. http://go.nike.com/  #nikeplus',
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'I just ran 1.00 km with Nike+. http://go.nike.com/  #nikeplus',
            'pub_date' => date('Y-m-d', strtotime('-1 day'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>3,
            'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out running 1.00 km with #Endomondo.', 
            'pub_date' => date('Y-m-d', strtotime('-8 days'))));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>4,
            'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
            'post_text' => 'Was out cycling 1.00 miles with #Endomondo.', 
            'pub_date' => date('Y-m-d', strtotime('-8 days'))));
       
        $insight_plugin = new DistanceSharedInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $insight_text = "That's exactly the same as last week!";
        $this->assertNotEqual(false, strpos($result->text, "shared <strong>1.62 miles</strong>. $insight_text"));
        $this->assertEqual($result->headline, "Been on the move ?");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

  public function testMultipleDistanceFromDifferentAppsSharedLessMoreThanOneMileThisWeek() {
      TimeHelper::setTime(1);
      $insight_dao = DAOFactory::getDAO('InsightDAO');
      $post_builders = array();
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => ' I just ran 1.00 mi with Nike+. http://go.nike.com/  #nikeplus',
          'pub_date' => date('Y-m-d', strtotime('-1 day'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'I just ran 1.00 km with Nike+. http://go.nike.com/  #nikeplus',
          'pub_date' => date('Y-m-d', strtotime('-1 day'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>3,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'Was out running 1.00 km with #Endomondo.', 
          'pub_date' => date('Y-m-d', strtotime('-8 days'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>4,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'Was out cycling 10.00 miles with #Endomondo.', 
          'pub_date' => date('Y-m-d', strtotime('-8 days'))));
     
      $insight_plugin = new DistanceSharedInsight();
      $insight_plugin->generateInsight($this->instance, null, $posts, 3);

      $today = date ('Y-m-d');
      $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
      $this->assertNotNull($result);
      $insight_text = "That's still better than everyone who decided to stay on the couch.";
      $this->assertNotEqual(false, strpos($result->text, "shared <strong>1.62 miles</strong>. $insight_text"));
      $this->assertEqual($result->headline, "Keep it up @janesmith!");
      $this->debug($this->getRenderedInsightInHTML($result));
      $this->debug($this->getRenderedInsightInEmail($result));
  }

  public function testGetDistanceFromPosts() {
      $insight_dao = DAOFactory::getDAO('InsightDAO');
      $post_builders = array();
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => ' I just ran 1.00 mi with Nike+. http://go.nike.com/  #nikeplus',
          'pub_date' => date('Y-m-d', strtotime('-2 day'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'I just ran 1.00 km with Nike+. http://go.nike.com/  #nikeplus',
          'pub_date' => date('Y-m-d', strtotime('-2 day'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>3,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'Was out running 1.00 km with #Endomondo.', 
          'pub_date' => date('Y-m-d', strtotime('-8 days'))));
      $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>4,
          'author_username'=> 'janesmith','author_user_id' => 42, 'network' => 'twitter',
          'post_text' => 'Was out cycling 1.00 miles with #Endomondo.', 
          'pub_date' => date('Y-m-d', strtotime('-8 days'))));

      $posts = array();
      foreach($post_builders as $post) {
          $post = new Post($post->columns);
          $posts[] = $post;
      }
     
      $insight_plugin = new DistanceSharedInsight();
      $distance = $insight_plugin->getDistanceFromPosts($posts);

      $this->assertEqual($distance["Sunday"], 1.621371192);
      $this->assertEqual($distance["Saturday"], 1.621371192);
  } 
}

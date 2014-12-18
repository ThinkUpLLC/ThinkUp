<?php
/*
 Plugin Name: Top Stories (End of Year)
 Description: Show the user their relevance by mentioning which major news stories they wrote about this year.
 When: December 18
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoytopstories.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Copyright (c) 2014 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 */

class EOYTopStoriesInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_top_stories';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-18';
    //staging
    //var $run_date = '12-16';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network != 'facebook') {
            $this->logger->logInfo("Done generating insight (Skipped Non-Facebook)", __METHOD__.','.__LINE__);
            return;
        }

        $year = date('Y');
        $regenerate = false;
        //testing
        //$regenerate = true;

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug,
            $instance,
            $insight_date = "$year-$this->run_date",
            $regenerate,
            $day_of_year = $this->run_date
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $instance->network
            );

            $topics = array(
                'the Ice Bucket Challenge' => array('ice bucket','ALS Ice'),
                'Robin Williams' => array('Robin Williams'),
                'the Super Bowl' => array('Super Bowl','Superbowl'),
                'MH370' => array('MH370','MH 370', 'Malaysia Airlines'),
                'the World Cup' => array("World Cup"),
                'the Sochi Olympics' => array('Sochi','Olympics'),
                'LeBron James' => array('lebron'),
                'Derek Jeter' => array('jeter'),
                'Floyd Wayweather Jr.' => array('mayweather'),
                'Carmelo Anthony' => array('carmelo'),
                'Cristiano Ronaldo' => array('ronaldo'),
                'Dale Earnhardt Jr.' => array('earnhardt'),
                'Dale Earnhardt Jr.' => array('earnhardt'),
                'Beyoncé' => array('beyonce'),
                'Pharrell' => array('pharrell'),
                'Nicki Minaj' => array('minaj'),
                'Taylor Swift' => array('Taylor Swift'),
                'Sam Smith' => array('Sam Smith'),
                'Jimmy Fallon' => array('fallon'),
                'Kim Kardashian' => array('kardashian'),
                'Game of Thrones' => array('game of thrones'),
                'Orange is the New Black' => array('Orange is the New Black','oitnb'),
                'The Walking Dead' => array('walking dead'),
                'Downton Abbey' => array('downton'),
                'True Detective' => array('True Detective'),
                'Frozen' => array('Frozen'),
                'Guardians of the Galaxy' => array('Guardians of the Galaxy'),
                'The Lego Movie' => array('lego movie'),
                'Gone Girl' => array('Gone Girl'),
            );

            $matches = array();
            foreach ($last_year_of_posts as $post) {
                foreach ($topics as $key => $strings) {
                    foreach ($strings as $string) {
                        if (stristr($post->post_text, $string) !== FALSE) {
                            $matches[$key] = array('term'=>$key, 'post'=>$post);
                            unset($topics[$key]);
                            break;
                        }
                    }
                }
            }

            $insight = new Insight();

            if (count($matches) == 0) {
                $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
                $qualified_year = "";
                if (date('Y', strtotime($earliest_pub_date)) == date('Y')) {
                    if (date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                        //Earliest post was this year; figure out what month we have data since this year
                        $since = date('F', strtotime($earliest_pub_date));
                        $qualified_year = " (at least since ".$since.")";
                    }
                }
                $headline = $this->username." didn't rehash 2014's top news on Facebook";
                $insight_text = "No Ice Bucket Challenge, Robin Williams, or Malaysia Airlines here. "
                    . $this->username." broke away from the herd and avoided talking about 2014's biggest stories "
                    ."on Facebook this year" . $qualified_year. '.';
                $posts = null;
                //Show avatar if there are no posts
                $insight->header_image = $user->avatar;
                //Show button if there are no posts
                $insight->setButton(array(
                    'url' => 'http://newsroom.fb.com/news/2014/12/2014-year-in-review/',
                    'label' => "See Facebook's Year in Review"
                ));
            } else {
                $headline = $this->username." was part of $year's biggest trends";
                $posts = array();
                $mentioned = array();
                foreach ($matches as $m) {
                    if (count($posts) < 3) {
                        $posts[] = $m['post'];
                    }
                    $mentioned[] = $m['term'];
                }
                $num = count($mentioned);
                if ($num > 1) {
                    $mentioned[$num-1] = 'and '.$mentioned[$num-1];
                }
                $mention_string = join($num==2?' ':', ', $mentioned);
                $thatwas = $num == 1 ? 'That was one' : 'Those were some';
                $insight_text = $this->username."'s $year included $mention_string. $thatwas of "
                    . '<a href="http://newsroom.fb.com/news/2014/12/2014-year-in-review/">Facebook\'s top topics of '
                    . "the year</a> &mdash; that's so $year!";
            }

            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";
            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            if ($posts) {
                $insight->setPosts($posts);
            }

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYTopStoriesInsight');

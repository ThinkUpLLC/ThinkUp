<?php
/*
 Plugin Name: Controversial Topics (End of Year)
 Description: Tells user which controversial topics they mentioned or avoided this year.
 When: December 16
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoycontroversialtopics.php
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

class EOYControversialTopicsInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_controversial_topics';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-16';
    //staging
    //var $run_date = '11-06';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network != 'facebook') {
            $this->logger->logInfo("Done generating insight (Skipped Non-facebook)", __METHOD__.','.__LINE__);
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
                'ferguson' => array("Ferguson", "Mike Brown", "Michael Brown"),
                'obamacare' => array("Obamacare"),
                'immigration' => array("immigration"),
                'gamergate' => array("GamerGate"),
                'isis' => array("ISIS"),
                'israel' => array("Israel"),
                'palestine' => array("Palestine"),
                'hamas' => array("hamas"),
                'donaldsterling' => array("Donald Sterling"),
                'marriage' => array("gay marriage","marriage equality","same-sex marriage"),
                'ebola' => array("ebola"),
                'climatechange' => array("climate change","global warming"),
            );

            $matches = array();
            foreach ($last_year_of_posts as $post) {
                foreach ($topics as $key => $strings) {
                    foreach ($strings as $string) {
                        if (stristr($post->post_text, $string) !== FALSE) {
                            $matches[$key] = array('term'=>$string, 'post'=>$post);
                            unset($topics[$key]);
                            break;
                        }
                    }
                }
            }

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
                $headline = $this->username.' kept the drama off of Facebook';
                $insight_text = $this->username.' avoided contentious topics like immigration and ebola, '
                    . 'which can be a great way to keep Facebook a little more friendly'.$qualified_year.'.';
                $posts = null;
            }
            else {
                $headline = $this->username." wasn't afraid of $year's big issues";
                $posts = array();
                $mentioned = array();
                foreach ($matches as $m) {
                    if (count($posts) < 3) {
                        $posts[] = $m['post'];
                    }
                    $mentioned[] = $m['term'];
                }
                $mentioned[count($mentioned)-1] = 'and '.$mentioned[count($mentioned)-1];
                $mention_string = join(count($mentioned)==2?' ':', ', $mentioned);
                $insight_text = $this->username." talked about $mention_string in $year. ".
                    "It's great to use Facebook to address things that matter.";
            }

            $insight = new Insight();
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

    public function getMaxMonth($point_chart) {
        $short_month = array_search(max($point_chart),$point_chart);
        return date('F', strtotime("$short_month 1 2014"));
    }

    public function hasFBomb(Post $post) {
        $text = strtolower($post->post_text);
        $has_fbomb = $post->in_reply_to_user_id != $instance->network_user_id && preg_match('/fuck/', $text);

        return $has_fbomb;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYFBombCountInsight');

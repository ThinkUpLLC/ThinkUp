<?php
/*
 Plugin Name: Controversial Topics (End of Year)
 Description: Which controversial topics did you mention or avoid this year.
 When: December 16
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoycontroversialtopics.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Copyright (c) 2014-2016 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
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
                'ferguson' => array("Ferguson", "Mike Brown", "Michael Brown"),
                'obamacare' => array("Obamacare"),
                'immigration' => array("immigration"),
                'gamergate' => array("GamerGate"),
                'isis' => array("ISIS"),
                'israel' => array("Israel"),
                'palestine' => array("Palestine"),
                'hamas' => array("Hamas"),
                'donaldsterling' => array("Donald Sterling"),
                'marriage' => array("gay marriage","marriage equality","same-sex marriage"),
                'ebola' => array("ebola"),
                'climatechange' => array("climate change","global warming"),
            );

            $matches = array();
            foreach ($last_year_of_posts as $post) {
                foreach ($topics as $key => $strings) {
                    foreach ($strings as $string) {
                        if (preg_match_all('/\b'.$string.'\b/i', $post->post_text) > 0) {
                            $matches[$key] = array('term'=>$string, 'post'=>$post);
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
                $headline = $this->username.' kept the drama off Facebook in 2014';
                $insight_text = $this->username." avoided contentious topics like immigration and ebola in $year"
                    . $qualified_year. ', which can be a good way to keep Facebook more friendly.';
                $posts = null;
                //Show avatar if there are no posts
                $insight->header_image = $user->avatar;
            }
            else {
                $headline = $this->username." took on $year's hot-button issues";
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
                $insight_text = $this->username." mentioned $mention_string on Facebook in $year. ".
                    "It's great to use Facebook to discuss issues that matter.";
            }

            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";
            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            if ($posts) {
                $posts = array_slice($posts, 0, 12);
                $link_dao = DAOFactory::getDAO('LinkDAO');
                foreach ($posts as $post) {
                    $post->links = $link_dao->getLinksForPost($post->post_id, $post->network);
                }
                $insight->setPosts($posts);
            }

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYControversialTopicsInsight');

<?php
/*
 Plugin Name: Lorenzo Llamas
 Description: Did you talk about llamas?
 When: February 27
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/llamas.php
 *
 * Copyright (c) 2012-2015 Gina Trapani
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
 * Copyright (c) 2014-2015 Chris Moyer, Anil Dash
 *
 * @author Chris Moyer chris@inarow.net, Anil Dash anil@thinkup.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2015 Chris Moyer, Anil Dash
 */

class LlamasInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'llamas';
    /**
     * Date to run this insight
     **/
    var $run_date = '02-27';
    //staging
    //var $run_date = '11-06';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $hero_image = array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/llama.jpg',
            'alt_text' => 'Llama ',
            'credit' => 'Photo: Eric Kilby',
            'img_link' => 'https://www.flickr.com/photos/ekilby/8564867495/'
        );

        if ($instance->network != 'twitter') {
            $this->logger->logInfo("Done generating insight (Skipped Non-Twitter)", __METHOD__.','.__LINE__);
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
                'llama' => array("llama","llamas"),
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
                $headline = $this->username.' managed to avoid llamageddon!';
                $insight_text = "It seems like half the internet was " .
                    "<a href='http://www.theverge.com/2015/2/26/8116693/live-the-internet-is-going-bananas-for-this-llama-chase'>talking about runaway llamas</a> " .
                    'yesterday. Kudos to ' . $this->username . ' for showing a llama restraint.';
                $posts = null;
            }
            else {
                $headline = $this->username." showed a whole llama love";
                $posts = array();
                $mentioned = array();
                foreach ($matches as $m) {
                    if (count($posts) < 3) {
                        $posts[] = $m['post'];
                    }
                }
                $insight_text = "Two runaway llamas <a href='http://www.theverge.com/2015/2/26/8116693/live-the-internet-is-going-bananas-for-this-llama-chase'>took over Twitter yesterday</a>" .
                    ", and like a llama people, " . $this->username . " couldn't resist.";
            }

            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";
            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->setHeroImage($hero_image);
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
$insights_plugin_registrar->registerInsightPlugin('LlamasInsight');

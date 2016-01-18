<?php
/*
 Plugin Name: Thanksgiving: Who Thanked You
 Description: Who thanked you this year.
 When: Annually on Thanksgiving
*/
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/thanksgivingwhothankedyou.php
 *
 * Copyright (c) 2014-2016 Chris Moyer
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class ThanksgivingWhoThankedYouInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'thanksgiving_who_thanked_you';

    public function generateInsight(Instance $instance,  User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);

        $thanksgiving_day = date('m/j', strtotime("3 weeks thursday",mktime(0,0,0,11,1,date('Y'))));
        $regenerate = false;
        //test
        // $thanksgiving_day = date('11/20');
        // $regenerate = true;

        if (!$this->shouldGenerateAnnualInsight($this->slug, $instance, 'today', $regenerate, $thanksgiving_day,
            null, array('instagram'))) {
            $this->logger->logInfo("Skipped generating insight on ".$instance->network, __METHOD__ . ',' . __LINE__);
            return;
        }

        $this->logger->logInfo("Begin generating insight", __METHOD__ . ',' . __LINE__);
        $user_dao = DAOFactory::getDAO('UserDAO');
        $post_dao = DAOFactory::getDAO('PostDAO');
        $iterator = $post_dao->getAllRepliesInRange($instance->network_user_id,$instance->network, $count=0,
            $from=date('Y-m-d', strtotime('January 1')), $until=date('Y-m-d'), $page=1, $order_by='pub_date',
            $direction = 'DESC', $is_public=false, $iterator=true);

        $thankees = array();
        $seen = array();
        foreach ($iterator as $post) {
            $author_id = $post->author_user_id;
            if ($author_id == $instance->network_user_id || in_array($author_id, $seen)) {
                // Skip ourselves or people we've seen.
                continue;
            }
            $text = strtolower($post->post_text);
            $has_thanks = preg_match('/(\W|^)(thanks|thank you)(\W|$)/', $text);
            if ($has_thanks) {
                if (preg_match('/(\W|^)no (thanks|thank you)/', $text) || preg_match('/thank(s| you),? but/', $text)) {
                    $has_thanks = false;
                }
            }
            if (!$has_thanks) {
                continue;
            }

            $seen[] = $author_id;
            $user = $user_dao->getDetails($author_id, $instance->network);
            if ($user) {
                $thankees[] = $user;
            }
        }
        $thankees_totals = array();
        foreach ($post_iterator as $post) {
            $author_id = $post->author_user_id;
            if ($author_id == $instance->network_user_id) {
                // Skip ourselves
                continue;
            }
            $text = strtolower($post->post_text);
            $has_thanks = preg_match('/(\W|^)(thanks|thank you)(\W|$)/', $text);
            if ($has_thanks) {
                if (preg_match('/(\W|^)no (thanks|thank you)/', $text) || preg_match('/thank(s| you),? but/', $text)) {
                    $has_thanks = false;
                }
            }
            if (!$has_thanks) {
                continue;
            }

            if (isset($thankees_totals[$author_id])) {
                $thankees_totals[$author_id] = $thankees_totals[$author_id] + 1;
            } else {
                $thankees_totals[$author_id] = 0;
            }
        }
        arsort($thankees_totals, SORT_NUMERIC);
        foreach ($thankees_totals as $user_id=>$count) {
            $user = $user_dao->getDetails( $user_id, $instance->network);
            if ($user) {
                $thankees[] = $user;
            }
        }

        if (count($thankees) > 1) {
            $num = count($thankees);
            $insight = new Insight();
            $insight->slug = $this->slug;
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            if ($instance->network == 'facebook') {
                $insight->headline = $num." Facebook friends were thankful for ".$this->username. " in ".date('Y');
                $insight->text = "It's great to have friends who share the love. These $num people were thankful for "
                    . $this->username." over the past year.";
                $insight->setHeroImage(array(
                    'url' => 'https://www.thinkup.com/assets/images/insights/2014-11/thanksgiving-3.jpg',
                    'alt_text' => '',
                    'credit' => 'Photo: paul bica',
                    'img_link' => 'https://www.flickr.com/photos/dexxus/2981387336/'
                ));
            } else {
                $insight->headline = $num." people were thankful for ".$this->username." in ".date('Y');
                if (count($thankees) > 20) {
                    $insight->text = "These are just some of the people who shared an appreciation for "
                        .$this->username." this year.";
                } else {
                    $insight->text = "These are all the people who shared an appreciation for "
                        .$this->username." this year.";
                }
                $insight->setHeroImage(array(
                    'url' => 'https://www.thinkup.com/assets/images/insights/2014-11/thanksgiving-4.jpg',
                    'alt_text' => '',
                    'credit' => 'Photo: David Joyce',
                    'img_link' => 'https://www.flickr.com/photos/deapeajay/3024604627/'
                ));
            }
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $thankees_sliced = array_slice($thankees, 0, 20);
            $insight->setPeople($thankees_sliced);
            $this->insight_dao->insertInsight($insight);
        }
        $this->logger->logInfo("Done generating insight", __METHOD__ . ',' . __LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ThanksgivingWhoThankedYouInsight');

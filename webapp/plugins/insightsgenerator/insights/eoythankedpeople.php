<?php
/*
 Plugin Name: EOY Thanked People
 Description: Who you thanked this year.
 When: Annually on Thanksgiving
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoythankedpeople.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class EOYThankedPeopleInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_thanked_people';


    public function generateInsight(Instance $instance,  User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);

        $thanksgiving_day = date('m/j', strtotime("3 weeks thursday",mktime(0,0,0,11,1,date('Y'))));
        if (!$this->shouldGenerateAnnualInsight($this->slug, $instance, 'today', false, $thanksgiving_day)) {
            $this->logger->logInfo("Skipped generating insight", __METHOD__ . ',' . __LINE__);
            return;
        }

        $this->logger->logInfo("Begin generating insight", __METHOD__ . ',' . __LINE__);
        $user_dao = DAOFactory::getDAO('UserDAO');
        $post_dao = DAOFactory::getDAO('PostDAO');
        $post_iterator = $post_dao->getThisYearOfPostsIterator($instance->network_user_id, $instance->network);

        $thankees = array();
        $seen = array();
        foreach ($post_iterator as $post) {
            $in_reply_id = $post->in_reply_to_user_id;
            if ($in_reply_id == 0 || $in_reply_id == $instance->network_user_id || in_array($in_reply_id, $seen)) {
                // No self thanks, untargeted thanks, or rethanking
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
            $seen[] = $in_reply_id;
            $user = $user_dao->getDetails($in_reply_id, $instance->network);
            if ($user) {
                $thankees[] = $user;
            }
        }

        if (count($thankees) > 1) {
            $insight = new Insight();
            $insight->slug = $this->slug;
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            if ($instance->network == 'facebook') {
                $insight->headline = $this->username . ' has friends to be thankful for';
                $insight->text = "These are the friends ".$this->username." was thankful for this year.";
                $insight->setHeroImage(array(
                    'url' => 'https://farm3.staticflickr.com/2629/4135626581_7bc080c706_z.jpg',
                    'alt_text' => $insight->headline,
                    'credit' => 'Photo: John-Morgan',
                    'img_link' => 'https://www.flickr.com/photos/aidanmorgan/4135626581/sizes/z/'
                ));
                $insight->setButton(array(
                    'label' => 'Share your thanks',
                    'url' => 'http://www.facebook.com/sharer/sharer.php?t=Thanks!',
                ));
            } else {
                $insight->headline = "Reflecting on what ".$this->username." is thankful for";
                $insight->text = "These are the people ".$this->username." was thankful for this year.";
                $insight->setHeroImage(array(
                    'url' => 'https://farm4.staticflickr.com/3141/2441818832_aa89a2ffa2_z.jpg',
                    'alt_text' => $insight->headline,
                    'credit' => 'Photo: stevevoght',
                    'img_link' => 'https://www.flickr.com/photos/voght/2441818832/sizes/z/'
                ));
                $insight->setButton(array(
                    'label' => 'Say "thanks" one more time',
                    'url' => 'https://twitter.com/intent/tweet?text=Thanks!',
                ));
            }
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->setPeople($thankees);
            $this->insight_dao->insertInsight($insight);
        }
        $this->logger->logInfo("Done generating insight", __METHOD__ . ',' . __LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYThankedPeopleInsight');

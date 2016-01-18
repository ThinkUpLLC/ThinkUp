<?php
/*
 Plugin Name: Location Sharing Awareness
 Description: How often you shared your location.
 When: Weekly, Fridays for Twitter, and Monthly,s 28th for Twitter
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/locationawareness.php
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
class LocationAwarenessInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'location_awareness';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        if ($instance->network == 'twitter' ||  /* testing */ $instance->network == 'test_no_monthly') {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $monthly = 0;
            $weekly = 0;
            if ($instance->network == 'twitter') {
                $weekly = 6;
                $monthly = 28;
            } else if ($instance->network == 'test_no_monthly') {
                $monthly = 0;
                $weekly = 2;
            }

            $did_monthly = false;
            if ($monthly && self::shouldGenerateMonthlyInsight($this->slug, $instance, 'today', false, $monthly)) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
                    $count=0, $order_by="pub_date", $in_last_x_days = date('t'),
                    $iterator = false, $is_public = false);
                $this->generateMonthlyInsight($instance, $posts);
                $did_monthly = true;
            }

            $do_weekly = $weekly && !$did_monthly;
            if ($do_weekly && self::shouldGenerateWeeklyInsight($this->slug, $instance, 'today', false, $weekly)) {
                $this->generateWeeklyInsight($instance, $last_week_of_posts);
            }

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }

    private function generateWeeklyInsight($instance, $posts) {
        $located_posts = 0;
        $geo_data = array();
        foreach ($posts as $p) {
            if ($this->isPreciselyLocated($p)) {
                $geo_data[] = $p->geo;
                $located_posts++;
            }
        }

        if ($located_posts < 5) {
            return;
        }

        $insight = new Insight();
        $insight->slug = $this->slug;
        $insight->instance_id = $instance->id;
        $insight->date = $this->insight_date;
        $insight->related_data = array('map_points' => array_unique($geo_data));
        $insight->headline = $this->getHeadline($located_posts, 'week');
        $insight->text = $this->getText('week', $located_posts);
        $insight->emphasis = Insight::EMPHASIS_LOW;
        $insight->filename = basename(__FILE__, ".php");
        $insight->setHeroImage($this->getHeroImage());
        $button = $this->getButton($instance);
        if ($button) {
            $insight->setButton($button);
        }
        $this->insight_dao->insertInsight($insight);
    }

    private function generateMonthlyInsight($instance, $posts) {
        $located_posts = 0;
        $geo_data = array();
        foreach ($posts as $p) {
            if ($this->isPreciselyLocated($p)) {
                $geo_data[] = $p->geo;
                $located_posts++;
            }
        }

        if ($located_posts < 1) {
            return;
        }

        $insight = new Insight();
        $insight->slug = $this->slug;
        $insight->instance_id = $instance->id;
        $insight->date = $this->insight_date;
        $insight->related_data = array('map_points' => array_unique($geo_data));
        $insight->headline = $this->getHeadline($located_posts, 'month');
        $insight->text = $this->getText('month', $located_posts);
        $insight->emphasis = Insight::EMPHASIS_HIGH;
        $insight->setHeroImage($this->getHeroImage());
        $button = $this->getButton($instance);
        if ($button) {
            $insight->setButton($button);
        }
        $insight->filename = basename(__FILE__, ".php");
        $this->insight_dao->insertInsight($insight);
    }

    private function getHeroImage() {
        return array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-07/pushpin.jpg',
                'alt_text' => 'You are here.',
                'credit' => 'Photo: Appie Verschoor',
                'img_link' => 'https://www.flickr.com/photos/xiffy/6768438411'
        );
    }

    private function getButton($instance) {
        if ($instance->network == 'twitter') {
            return array(
                'url' => 'https://twitter.com/settings/security',
                'label' => 'Update location settings'
            );
        }
        return null;
    }

    private function getText($period, $total) {
        $posts = $total == 1 ? '%post' : '%posts';
        $time = $this->calculateTime($total);
        return $this->getVariableCopy(array(
            "Last %period, %username included precise location details in %total $posts. "
                . "That's roughly %time anyone could have found %username in person.",
            "%username added precise location information to %total $posts last %period."
        ), array(
            'period' => $period,
            'total' => $total,
            'time' => $time
        ));
    }

    private function getHeadline($total, $period) {
      $time = $this->calculateTime($total);

      return $this->getVariableCopy(array(
        "%username has been spotted in the wild",
        "%username has been sharing location data",
        "%total location share".($total > 1 ? 's' : ''),
        "%time on the map",
      ), array('total' => $total, 'time' => $time));
    }

    private function calculateTime($total) {
        // 45 minutes per posting, a rough estimate
        return TimeHelper::secondsToGeneralTime($total * 45 * 60);
    }

    /**
     * Look at a post, determine if specific geo data is attached.
     *
     * The current logic, examining decimals of precisions is based on comparing data attached to
     * actual posts, using the facebook and twitter mobile apps vs website.
     *
     * @param Post $post - The post to examine
     * @return bool Whether precise data is attached
     */
    public function isPreciselyLocated($post) {
        if (empty($post->geo)) {
            return false;
        }

        if (strstr($post->geo, ',') === false) {
            return false;
        }

        $latlon = explode(',', $post->geo);

        $max_decimals = 0;
        for ($i=0; $i<2; $i++) {
            $val = $latlon[$i];
            $val = preg_replace('/^[-0-9]*\./', '', $val);
            $max_decimals = max(strlen($val), $max_decimals);
        }

        return $max_decimals > 7;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LocationAwarenessInsight');

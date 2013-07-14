<?php
/*
 Plugin Name: Metweet
 Description: How many times you share posts mentioning you. (Monday)
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/metweet.php
 *
 * Copyright (c) 2012-2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once('../../twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php');

class MetweetInsight extends InsightPluginParent implements InsightPlugin {
	public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        //Only insert this insight for twitter, if it's Monday or if we're testing
        if ((date('w') == 1 || $in_test_mode) && $instance->network == 'twitter') {
        	$metweet_count = 0;

        	foreach ($last_week_of_posts as $post) {
        		if (isset($post->in_retweet_of_post_id)) {
        			$post_text = $post->post_text;
        			
        			$text_parser = new Twitter_Extractor($post_text);
        			$elements = $text_parser->extract();

        			$mentions_in_post = $elements['mentions'];
        			if (in_array($instance->network_username,$mentions_in_post)) {
        				$metweet_count++;
        			}
        		}
        	}

        	if ($metweet_count > 1) {
        		$insight_text = $this->username." retweeted posts mentioning themself "
        		."<strong>".$metweet_count." times</strong> in the last week";

        		$insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $insight_baseline_dao->insertInsightBaseline("metweet_count", $instance->id, $metweet_count,
                $this->insight_date);

                $last_monday = date('Y-m-d', strtotime('-7 day'));
                $last_monday_insight_baseline = $insight_baseline_dao->getInsightBaseline("metweet_count",
                $instance->id, $last_monday);
                if (isset($last_monday_insight_baseline)) {
                	if ($last_monday_insight_baseline->value > $count ) {
                		$difference = $last_monday_insight_baseline->value - $count;
                		$insight_text .= ", $difference fewer time".($difference>1?"s":"")." than the prior week.";
                	} elseif ($last_monday_insight_baseline->value < $count ) {
                        $difference = $count - $last_monday_insight_baseline->value;
                        $insight_text .= ", $difference more time".($difference>1?"s":"")." than the prior week.";
                    } else {
                        $insight_text .= ".";
                    }
                } else {
                    $insight_text .= ".";
                }

                $this->insight_dao->insertInsight("metweet", $instance->id, $this->insight_date, "Metweets:",
                $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
        	}
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('MetweetInsight');

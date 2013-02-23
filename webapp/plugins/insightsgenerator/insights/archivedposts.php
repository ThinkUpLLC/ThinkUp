<?php
/*
 Plugin Name: Archived Posts
 Description: Notify user every 100 posts captured.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/archivedposts.pho
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @copyright 2012-2013 Gina Trapani
 */

class ArchivedPostsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $archived_posts_in_hundreds = intval($instance->total_posts_in_system / 100);
        if ($archived_posts_in_hundreds > 0) {
            $insight_baseline_slug = "archived_posts_".$archived_posts_in_hundreds;

            $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            if (!$insight_baseline_dao->doesInsightBaselineExist($insight_baseline_slug, $instance->id)) {
                $insight_baseline_dao->insertInsightBaseline($insight_baseline_slug, $instance->id,
                $archived_posts_in_hundreds);

                $config = Config::getInstance();

                switch ($instance->network) {
                    case "twitter":
                        $posts_term = "tweets";
                        break;
                    case "foursquare":
                        $posts_term = "checkins";
                        break;
                    default:
                        $posts_term = "posts";
                }

                $text = "ThinkUp has captured over <strong>". (number_format($archived_posts_in_hundreds * 100)).
                ' '.$posts_term . '</strong> by '.$this->username.'.';
                $this->insight_dao->insertInsight("archived_posts", $instance->id, $this->insight_date, "Archived:",
                $text, basename(__FILE__, ".php"), Insight::EMPHASIS_MED);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ArchivedPostsInsight');

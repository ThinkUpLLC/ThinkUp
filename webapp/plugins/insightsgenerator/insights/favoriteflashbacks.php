<?php
/*
 Plugin Name: Favorite Flashback
 Description: Posts you favorited on this day in years past.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/flashbacks.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2012
 */

class FavoriteFlashbackInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $fav_dao = DAOFactory::getDAO('FavoritePostDAO');

        $days_ago = 0;
        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));
            $existing_insight = $this->insight_dao->getInsight("favorites_year_ago_flashback", $instance->id,
            $since_date);
            if (!isset($existing_insight)) {
                //Generate flashback post list
                $flashback_favs = $fav_dao->getFavoritesFromOneYearAgo($instance->network_user_id,
                $instance->network, $since_date);
                if (isset($flashback_favs) && sizeof($flashback_favs) > 0 ) {
                    $this->insight_dao->insertInsight("favorites_year_ago_flashback", $instance->id,
                    $since_date, "Stuff you liked:", "On this day in years past, you liked: ",
                    basename(__FILE__, ".php"), Insight::EMPHASIS_LOW, serialize($flashback_favs));
                }
            }
            $days_ago++;
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FavoriteFlashbackInsight');

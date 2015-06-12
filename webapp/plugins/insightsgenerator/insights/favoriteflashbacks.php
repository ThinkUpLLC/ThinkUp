<?php
/*
 Plugin Name: Favorite Flashback
 Description: Posts you favorited on this day in years past.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/flashbacks.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2015 Gina Trapani
 */

class FavoriteFlashbackInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $fav_dao = DAOFactory::getDAO('FavoritePostDAO');

        $days_ago = 0;
        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));
            if (self::shouldGenerateInsight('favorites_year_ago_flashback', $instance,
                $insight_date=$since_date, $regenerate_existing_insight=false)) {

                //Generate flashback post list
                $flashback_favs = $fav_dao->getFavoritesFromOneYearAgo($instance->network_user_id,
                    $instance->network, $since_date);

                if (isset($flashback_favs) && sizeof($flashback_favs) > 0 ) {

                    //Load photos for Instagram
                    if ($instance->network == 'instagram') {
                        foreach ($flashback_favs as $post) {
                            $flashback_fav_photos = array();
                            $photo_dao = DAOFactory::getDAO('PhotoDAO');
                            $post =$photo_dao->getPhoto($post->post_id, 'instagram');
                            $flashback_fav_photos[] = $post;
                        }
                        $flashback_favs = $flashback_fav_photos;
                    }

                    $post_year = date(date( 'Y' , strtotime($flashback_favs[0]->pub_date)));
                    $current_year = date('Y');
                    $number_of_years_ago = $current_year - $post_year;
                    $plural = ($number_of_years_ago > 1 )?'s':'';

                    if ($instance->network == 'twitter') {
                        $headline = $number_of_years_ago." year". $plural." ago " . $this->username
                            ." favorited @" .$flashback_favs[0]->author_username;
                    } else {
                        $headline = $this->terms->getProcessedText($number_of_years_ago." year"
                            . $plural." ago " . $this->username ." " .$this->terms->getVerb('liked') . " "
                            . $flashback_favs[0]->author_username."'s %post");
                    }
                    $my_insight = new Insight();
                    $my_insight->instance_id = $instance->id;
                    $my_insight->slug = 'favorites_year_ago_flashback';
                    $my_insight->date = $since_date;
                    $my_insight->headline = $headline;
                    $my_insight->text = "Can you believe how fast time flies?";
                    $my_insight->emphasis = Insight::EMPHASIS_MED;
                    $my_insight->filename = basename(__FILE__, ".php");
                    $my_insight->setPosts($flashback_favs);

                    $this->insight_dao->insertInsight($my_insight);
                }
            }
            $days_ago++;
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FavoriteFlashbackInsight');

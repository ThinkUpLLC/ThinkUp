<?php
/*
 Plugin Name: Saved Search Results
 Description: When there are new posts containing saved keyword search terms.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/savedsearchresults.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * @copyright 2013 Gina Trapani
 */

class SavedSearchResultsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        //Set up DAOs
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
        $hashtag_post_dao = DAOFactory::getDAO('HashtagPostDAO');
        $hashtag_dao = DAOFactory::getDAO('HashtagDAO');

        // Get all the hashtags for the instance
        $instance_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
        // foreach hashtag, get the count of new posts
        foreach ($instance_hashtags as $instance_hashtag) {
            $total_new_posts = $hashtag_post_dao->getTotalPostsByHashtagAndDate($instance_hashtag->hashtag_id);
            //Only insert insight if there are new results
            if ($total_new_posts > 0) {
                //Assemble insight text
                $post_term = ($instance->network == 'twitter')?'tweets':'posts';
                $hashtag = $hashtag_dao->getHashtagByID($instance_hashtag->hashtag_id);
                $link = 'search.php?u='.$instance->network_username.'&n='.$instance->network.
                '&c=searches&k='.urlencode($hashtag->hashtag).'&q='.urlencode($hashtag->hashtag);
                $headline = number_format($total_new_posts)." new ".$post_term." contain \"<strong>".
                $hashtag->hashtag."</strong>\".";
                $insight_text = "View new ".$post_term." containing <a href=\"".$link."\">". $hashtag->hashtag."</a>.";
                // Insert insight
                $this->insight_dao->insertInsightDeprecated("saved_search_results_".$instance_hashtag->hashtag_id,
                $instance->id, $this->insight_date, $headline, $insight_text, basename(__FILE__, ".php"),
                Insight::EMPHASIS_MED);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('SavedSearchResultsInsight');

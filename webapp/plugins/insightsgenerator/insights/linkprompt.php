<?php
/*
 Plugin Name: Link Prompt
 Description: Pings you about posting interesting links in your updates.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/linkprompt.php
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

require_once dirname(__FILE__).'/../../twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php';

class LinkPromptInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        $supported_networks = array('twitter', 'facebook', 'google+'); // Insight isn't relevant for Foursquare etc.
        $alternate_day = ((int)date('j')) % 2;
        //Only prompt for supported networks on alternate days or if we're testing
        if ((in_array($instance->network, $supported_networks) && $alternate_day) || $in_test_mode) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            
            $recent_posts = $post_dao->getPostsByUserInRange($instance->network_user_id, $instance->network,
            date('Y-m-d H:i:s', strtotime('-2 days midnight')), date('Y-m-d H:i:s', strtotime('today midnight')));
            
            $posts_with_links = array();

            foreach ($recent_posts as $post) {
                $post_text = $post->post_text;

                $text_parser = new Twitter_Extractor($post_text);
                $elements = $text_parser->extract();

                if (count($elements['urls'])) {
                    $posts_with_links[] = $post;
                }
            }

            if (!count($posts_with_links)) {
                switch ($instance->network) {
                    case 'twitter':
                        $insight_text = $this->username." hasn't tweeted a link in the last 2 days. "
                        ."Followers always like to check out interesting sites and articles.";
                        break;

                    case 'facebook':
                        $insight_text = "None of ".$this->username."'s status updates have included a link "
                        ."in the past 2 days. Friends always appreciate some new sites or articles to read "
                        ."in status updates.";
                        break;

                    case 'google+':
                        $insight_text = "None of ".$this->username."'s posts have included a link "
                        ."in the past 2 days. Its always nice to share interesting sites and articles "
                        ."with your circles every once in a while.";
                        break;
                }

                $this->insight_dao->insertInsight("link_prompt", $instance->id, $this->insight_date,
                "Nudge:", $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LinkPromptInsight');
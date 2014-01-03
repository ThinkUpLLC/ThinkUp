<?php
/*
 Plugin Name: Link Prompt
 Description: Pings you about posting interesting links in your updates.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/linkprompt.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__).'/../../twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php';

class LinkPromptInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('link_prompt', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=null, $count_last_week_of_posts=null,
        $excluded_networks=array('foursquare', 'youtube'), $alternate_day=(((int)date('j')) % 2))) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $link_dao = DAOFactory::getDAO('LinkDAO');

            // Check from midnight two days ago until an hour from now
            // (to avoid clock-sync issues)
            $recent_posts = $post_dao->getPostsByUserInRange($instance->network_user_id, $instance->network,
            date('Y-m-d H:i:s', strtotime('-2 days midnight')), date('Y-m-d H:i:s', strtotime('+1 hour')));

            $posts_with_links = array();

            foreach ($recent_posts as $post) {
                $post_text = $post->post_text;

                $text_parser = new Twitter_Extractor($post_text);
                $elements = $text_parser->extract();

                if (count($elements['urls'])) {
                    $posts_with_links[] = $post;
                }
            }

            $num_posts = $post_dao->countAllPostsByUserSinceDaysAgo($instance->network_user_id,
            $instance->network, 30);
            $num_links = $link_dao->countLinksPostedByUserSinceDaysAgo($instance->network_user_id,
            $instance->network, 30);

            if ($num_posts && (($num_links / $num_posts) > 0.2) && count($recent_posts) && !count($posts_with_links)) {
                $headline = $this->username." hasn't ".$this->terms->getVerb('posted')
                ." a link in the last 2 days on " . $instance->network . ".";

                $insight_text = "It may be time to share an interesting link with "
                .$this->terms->getNoun('friend', InsightTerms::PLURAL).".";

                $this->insight_dao->insertInsightDeprecated('link_prompt', $instance->id, $this->insight_date,
                $headline, $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Determine whether an insight should be generated or not.
     * @param str $slug slug of the insight to be generated
     * @param Instance $instance user and network details for which the insight has to be generated
     * @param date $insight_date date for which the insight has to be generated
     * @param bool $regenerate_existing_insight whether the insight should be regenerated over a day
     * @param int $day_of_week the day of week (0 for Sunday through 6 for Saturday) on which the insight should run
     * @param int $count_last_week_of_posts if set, wouldn't run insight if there are no posts from last week
     * @param arr $excluded_networks array of networks for which the insight shouldn't be run
     * @param bool $alternate_day whether today is an alternate day or not
     * @return bool $run whether the insight should be generated or not
     */
    public function shouldGenerateInsight($slug, Instance $instance, $insight_date=null,
    $regenerate_existing_insight=false, $day_of_week=null, $count_last_week_of_posts=null,
    $excluded_networks=null, $alternate_day=true) {
        if (Utils::isTest()) {
            return true;
        } else {
            return $alternate_day && parent::shouldGenerateInsight($slug, $instance, $insight_date,
            $regenerate_existing_insight, $day_of_week, $count_last_week_of_posts, $excluded_networks);
        }
    }
}

//$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
//$insights_plugin_registrar->registerInsightPlugin('LinkPromptInsight');

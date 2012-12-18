<?php
/*
 Plugin Name: Word Frequency
 Description: Most frequently-mentioned words in replies of highly-replied-to posts.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/wordfrequency.php
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
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class WordFrequencyInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);

        foreach ($last_week_of_posts as $post) {
            // Frequent word insight: If > 20 replies, let user know most-frequently mentioned words are available
            if ($post->reply_count_cache >= 20 && $post->network != 'google+') {
                if (!isset($config)) {
                    $config = Config::getInstance();
                }
                $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
                $this->insight_dao->insertInsight('replies_frequent_words_'.$post->id, $instance->id,
                $simplified_post_date, "Conversation starter:",
               'Your post got <strong>'.$post->reply_count_cache.' replies</strong>. See <a href="'.
                $config->getValue('site_root_path'). 'post/?t='.$post->post_id.'&n='.$post->network.
                '">the most frequently-mentioned reply words</a>.',
                basename(__FILE__, ".php"), Insight::EMPHASIS_LOW, serialize($post));
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('WordFrequencyInsight');

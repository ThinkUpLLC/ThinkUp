<?php
/*
 Plugin Name: Favorited Links
 Description: Posts you've liked or favorited that contain links each day.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/favoritedlinks.php
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

class FavoritedLinksInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_text = '';

        if (self::shouldGenerateInsight('favorited_links', $instance, $insight_date='today',
        $regenerate_existing_insight=true)) {
            $fpost_dao = DAOFactory::getDAO('FavoritePostDAO');
            $favorited_posts = $fpost_dao->getAllFavoritePosts($instance->network_user_id, $instance->network, 40);
            $todays_favorited_posts_with_links = array();

            foreach ($favorited_posts as $post) {
                if (date('Y-m-d', strtotime($post->pub_date)) == date('Y-m-d')) {
                    $post_text = $post->post_text;

                    $text_parser = new Twitter_Extractor($post_text);
                    $elements = $text_parser->extract();

                    if (count($elements['urls'])) {
                        $todays_favorited_posts_with_links[] = $post;
                    }
                }
            }

            $favorited_links_count = count($todays_favorited_posts_with_links);
            if ($favorited_links_count) {
                $verb = '';
                $post_type = '';

                if ($favorited_links_count == 1) {
                    $headline = $this->username." ".$this->terms->getVerb('liked')
                    ." <strong>1 ".$this->terms->getNoun('post')."</strong> with a link in it.";
                } else {
                    $headline = $this->username." ".$this->terms->getVerb('liked')
                    ." <strong>".$favorited_links_count." ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                    ."</strong> with links in them:";
                }

                //Instantiate the Insight object
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->instance_id = $instance->id;
                $my_insight->slug = 'favorited_links'; //slug to label this insight's content
                $my_insight->date = $this->insight_date; //date of the data this insight applies to
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = '';
                $my_insight->emphasis = Insight::EMPHASIS_LOW; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                $my_insight->setPosts($todays_favorited_posts_with_links);

                $this->insight_dao->insertInsight($my_insight);


            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FavoritedLinksInsight');
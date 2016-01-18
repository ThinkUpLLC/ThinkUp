<?php
/*
 Plugin Name: Favorited Links
 Description: Posts you've liked or favorited that contain links each day.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/favoritedlinks.php
 *
 * Copyright (c) 2012-2016 Nilaksh Das, Gina Trapani
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
 * @copyright 2012-2016 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class FavoritedLinksInsight extends InsightPluginParent implements InsightPlugin {

    /**
     * Maximum number of posts we display in this insight
     * This limit prevents a InsightFieldExceedsMaxLengthException
     */
    const MAX_POSTS = 15;

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        if ($instance->network == 'twitter') {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

            if (self::shouldGenerateInsight('favorited_links', $instance, $insight_date='today',
                $regenerate_existing_insight=true)) {
                $fav_post_dao = DAOFactory::getDAO('FavoritePostDAO');
                $favorited_posts = $fav_post_dao->getRecentlyFavoritedPosts($instance->network_user_id,
                    $instance->network, self::MAX_POSTS * 3);
                $todays_favorited_posts_with_links = array();

                $num_good_links = 0;
                $num_good_posts = 0;
                $seen_urls = array();
                foreach (array_reverse($favorited_posts) as $post) {
                    if (date('Y-m-d', strtotime($post->favorited_timestamp)) == date('Y-m-d')) {
                        $good_links = array();
                        $good_post = false;
                        foreach ($post->links as $link) {
                            if (!empty($link->title)) {
                                $url = !empty($link->expanded_url) ? $link->expanded_url : $link->url;
                                // Skipping photos that look like links
                                if (!preg_match('/pic.twitter.com/', $url)
                                    && !preg_match('/twitter.com\/.*\/photo\//', $url)) {

                                    $seen_expanded = !empty($link->expanded_url)
                                        && in_array($link->expanded_url, $seen_urls);
                                    // Skip URLs we've seen before
                                    if (!$seen_expanded && !in_array($link->url, $seen_urls)) {
                                        $good_links[] = $link;
                                        $seen_urls[] = $link->url;
                                        $seen_urls[] = $link->expanded_url;
                                        $num_good_links++;
                                        $good_post = true;
                                    }
                                }
                            }
                        }
                        if ($good_post) {
                            $num_good_posts++;
                        }
                        if (count($good_links)) {
                            $post->links = $good_links;
                            $todays_favorited_posts_with_links[] = $post;
                        }
                    }
                }

                $todays_favorited_posts_with_links = array_reverse($todays_favorited_posts_with_links);
                $todays_favorited_posts_with_links = array_slice($todays_favorited_posts_with_links,0,self::MAX_POSTS);
                if ($num_good_posts) {
                    if ($num_good_posts == 1) {
                        if ($num_good_links == 1) {
                            $insight_text = $this->username." ".$this->terms->getVerb('liked')
                                ." 1 ".$this->terms->getNoun('post')." with a link in it.";
                        } else {
                            $insight_text = $this->username." ".$this->terms->getVerb('liked')
                                ." 1 ".$this->terms->getNoun('post')
                                ." with <strong>$num_good_links links</strong> in it.";
                        }
                    } else {
                        if ($num_good_posts >= self::MAX_POSTS) {
                            //Since number of posts is at max limit, some may have been cut off
                            //So let's not cite specific totals
                            $insight_text = "Here are the latest links from ".$this->terms->getNoun('post',
                                InsightTerms::PLURAL). " ".$this->username." ".$this->terms->getVerb('liked').".";
                        } else {
                            $insight_text = $this->username." ".$this->terms->getVerb('liked')
                                ." ".$num_good_posts." ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                                ." with <strong>$num_good_links links</strong> in them.";
                        }
                    }

                    $my_insight = new Insight();
                    $my_insight->instance_id = $instance->id;
                    $my_insight->slug = 'favorited_links';
                    $my_insight->date = $this->insight_date;
                    $my_insight->headline = $this->getVariableCopy(array(
                        'The latest link%s %username %liked',
                        '%total link%s %username %liked',
                    ), array('total' => number_format($num_good_links), 's' => $num_good_links == 1 ? '' : 's'));
                    $my_insight->text = $insight_text;
                    $my_insight->header_image = '';
                    $my_insight->emphasis = Insight::EMPHASIS_LOW;
                    $my_insight->filename = basename(__FILE__, ".php");
                    $my_insight->setPosts($todays_favorited_posts_with_links);

                    $this->insight_dao->insertInsight($my_insight);
                }
            }
            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FavoritedLinksInsight');

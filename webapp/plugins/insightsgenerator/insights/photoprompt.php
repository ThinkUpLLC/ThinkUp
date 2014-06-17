<?php
/*
 Plugin Name: Photo Prompt
 Description: Reminds you to post a new photo if you normally post photos but haven't in the last 7 days.
 When: First Crawl and Weekly
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/photoprompt.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class PhotoPromptInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'photoprompt';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        if ($instance->network == 'twitter' || $instance->network == 'facebook') {
            $firstrun = !$this->insight_dao->doesInsightExist($this->slug, $instance->id);
            $run = false;
            if ($firstrun) {
                $run = true;
            }
            else if ($instance->network == 'facebook' &&
                self::shouldGenerateWeeklyInsight($this->slug, $instance, 'today', false, 1)) {
                $run = true;
            }
            else if ($instance->network == 'twitter' &&
                self::shouldGenerateWeeklyInsight($this->slug, $instance, 'today', false, 5)) {
                $run = true;
            }

            if ($run) {
                $photo_this_week = $this->findPostWithPhoto($last_week_of_posts);

                if (!$photo_this_week) {
                    $post_dao = DAOFactory::getDAO('PostDAO');
                    $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
                        $count=0, $order_by="pub_date", $in_last_x_days = 14, $iterator = false, $is_public = false);

                    $photo_at_all = $this->findPostWithPhoto($posts);

                    if ($photo_at_all) {
                        $insight = new Insight();
                        $insight->slug = $this->slug;
                        $insight->instance_id = $instance->id;
                        $insight->date = $this->insight_date;
                        $insight->headline = $this->getVariableCopy(array(
                            "They're worth a thousand words...",
                            "Picture this...",
                            "Missed a photo opportunity?"
                        ));

                        $days = floor((time()- strtotime($photo_at_all->pub_date))/(60*60*24));
                        $insight->text = $this->username." hasn't posted a photo in $days day".($days==1?"":"s").". "
                            . "It might be worth finding something to share.";
                        $insight->filename = basename(__FILE__, ".php");
                        $this->insight_dao->insertInsight($insight);
                    }
                }

            }

        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Loop over a set of posts and find the first with a photo
     *
     * @var array $posts Posts to search
     * @return Post Post with a photo or null
     */
    private function findPostWithPhoto($posts) {
        foreach ($posts as $post) {
            foreach ($post->links as $link) {
                if ($link->image_src) {
                    return $post;
                }
            }
        }
        return null;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('PhotoPromptInsight');

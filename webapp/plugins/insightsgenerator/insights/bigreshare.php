<?php
/*
 Plugin Name: Big Reshare
 Description: Retweet or reshare by someone with more followers than you have.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/bigreshare.php
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

class BigReshareInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $post_dao = DAOFactory::getDAO('PostDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $service_user = $user_dao->getDetails($instance->network_user_id, $instance->network);
        $insight_text = '';

        foreach ($last_week_of_posts as $post) {
            $big_reshares = $post_dao->getRetweetsByAuthorsOverFollowerCount($post->post_id, $instance->network,
            $service_user->follower_count);

            if (isset($big_reshares) && sizeof($big_reshares) > 0 ) {
                if (!isset($config)) {
                    $config = Config::getInstance();
                }
                if (sizeof($big_reshares) > 1) {
                    $headline = "People with lots of followers ".$this->terms->getVerb('shared')." "
                    ."$this->username's " . $this->terms->getNoun('post') . ".";
                } else {
                    $follower_count_multiple =
                    intval(($big_reshares[0]->follower_count) / $service_user->follower_count);
                    if ($follower_count_multiple > 1 ) {
                        $headline = "Someone with <strong>".$follower_count_multiple.
                        "x</strong> more followers than $this->username ".$this->terms->getVerb('shared')." "
                        ."this " . $this->terms->getNoun('post') . ".";
                    } else {
                        $headline = "Someone with lots of followers ".$this->terms->getVerb('shared')." "
                        ."$this->username's " . $this->terms->getNoun('post') . ".";
                    }
                }
                //Replace each big resharer's bio line with the text of the post
                foreach ($big_reshares as $sharer) {
                    $sharer->description = '"'.$post->post_text.'"';
                }
                $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                //Instantiate the Insight object
                $my_insight = new Insight();
                $my_insight->slug = "big_reshare_".$post->id; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $simplified_post_date; //date of the data this insight applies to
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->header_image = $header_image;
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                $my_insight->emphasis = Insight::EMPHASIS_HIGH; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
                $my_insight->setPeople($big_reshares);
                $my_insight->setPosts(array($post));

                $this->insight_dao->insertInsight($my_insight);

            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('BigReshareInsight');

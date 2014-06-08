<?php
/*
 Plugin Name: Gender Analysis
 Description: This plugin does amazing things
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/genderanalysis.php
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
 *
 * GenderAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

class GenderAnalysisInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
		$this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        
        $filename = basename(__FILE__, ".php");
        
        $post_dao = DAOFactory::getDAO('PostDAO');
        echo "post";
        $fpost_dao = DAOFactory::getDAO('FavoritePostDAO');
        echo "fav";
        $posts = $post_dao->getMostFavCommentPostsByUserId($instance->network_user_id, $instance->network);
        echo "posts=" . Utils::varDumpToString($posts) . "<br />";
        echo "for_before";
        foreach ($posts as $post) {
        	$gender_fav = $fpost_dao->getGenderOfFavoriters($post->post_id);
        	$gender_comm = $fpost_dao->getGenderOfCommenters($post->post_id);
        	echo "post_id=".$post->post_id."<br />";
        	echo "f_f=".$gender_fav['female_likes_count']."<br />";
        	echo "m_f=".$gender_fav['male_likes_count']."<br />";
        	echo "f_c=".$gender_comm['female_comm_count']."<br />";
        	echo "m_c=".$gender_comm['male_comm_count']."<br />";
        	$my_insight = new Insight();
        	$my_insight->instance_id = $instance->id;
        	$my_insight->slug = 'insight_name_goes_here'.$post->post_id;
        	$my_insight->date = date('Y-m-d', strtotime($post->pub_date));
        	$my_insight->headline = 'Gender Analysis';
        	$my_insight->text = "<strong>".number_format($gender_fav['female_likes_count']).
                        " women</strong> who liked ". $instance->network_username."'s post";
        	$my_insight->emphasis = Insight::EMPHASIS_HIGH;
        	$my_insight->filename = $filename;
        	$this->insight_dao->insertInsight($my_insight);
        	$my_insight = null;
        } 
        

//         foreach ($last_week_of_posts as $post) {
//             $my_insight = new Insight();
//             $my_insight->instance_id = $instance->id;
//             $my_insight->slug = 'insight_name_goes_here'.$post->id;
//             $my_insight->date = date('Y-m-d', strtotime($post->pub_date));
//             $my_insight->headline = 'Snappy headline';
//             $my_insight->text = 'Body of the insight';
//             $my_insight->emphasis = Insight::EMPHASIS_MED;
//             $my_insight->filename = $filename;

//             //OPTIONAL: Attach related data of various types using Insight setter functions
//             //$my_insight->setPosts($my_insight_posts);
//             //$my_insight->setLinks($my_insight_links);
//             //$my_insight->setPeople($my_insight_people);
//             //etc

//             $this->insight_dao->insertInsight($my_insight);
//             $my_insight = null;
//         }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        echo "Gender done\n";
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('GenderAnalysisInsight');


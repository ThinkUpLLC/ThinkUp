<?php
/*
 Plugin Name: Hello ThinkUp
 Description: Sample insight.
 When:
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/hellothinkupinsight.php
 *
 * Copyright (c) 2012-2014 Gina Trapani, Chris Moyer
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
 * @copyright 2012-2014 Gina Trapani, Chris Moyer
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */

class HelloThinkUpInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $possible_messaging = array(
            array(
                'headline' => 'Ohai',
                'text' => 'Greetings, humans',
                'header_image' => 'http://farm3.staticflickr.com/2713/4098259769_725b5fb65b_o.jpg'
            ),
            array(
                'headline' => 'Hello',
                'text' => 'Greetings, earthlings',
                'header_image' => 'http://farm9.staticflickr.com/8078/8276342554_5a51725f5f_n.jpg'
            ),
            array(
                'headline' => 'Yo',
                'text' => 'Greetings, peeps',
                'header_image' => 'http://farm6.staticflickr.com/5006/5367216303_83c5f2dc39_n.jpg'
            )
        );

        //Instantiate the Insight object
        $my_insight = new Insight();

        //REQUIRED: Set the insight's required attributes
        //We pull some from the options above.  But the could just be strings like 'Ohai'
        $which_messaging = TimeHelper::getTime() % count($possible_messaging);
        foreach ($possible_messaging[$which_messaging] as $field => $value) {
            $my_insight->{$field} = $value;
        }

        $my_insight->slug = 'my_test_insight_hello_thinkup'; //slug to label this insight's content
        $my_insight->instance_id = $instance->id;
        $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
        $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
        $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally, default is Insight::EMPHASIS_LOW

        //OPTIONAL: Attach related data of various types using Insight setter functions
        //$my_insight->setPosts($my_insight_posts);
        //$my_insight->setLinks($my_insight_links);
        //$my_insight->setPeople($my_insight_people);
        //$my_insight->setMilestones($my_insight_milestones);
        //etc

        $this->insight_dao->insertInsight($my_insight);

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

//$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
//$insights_plugin_registrar->registerInsightPlugin('HelloThinkUpInsight');

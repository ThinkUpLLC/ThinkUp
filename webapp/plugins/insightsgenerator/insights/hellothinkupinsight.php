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
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class HelloThinkUpInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        //Instantiate the Insight object
        $my_insight = new Insight();

        //REQUIRED: Set the insight's required attributes
        $my_insight->instance_id = 1;
        $my_insight->slug = 'my_test_insight_hello_thinkup'; //slug to label this insight's content
        $my_insight->date = '2013-12-21'; //date of the data this insight applies to
        $my_insight->headline = 'Ohai';
        $my_insight->text = "Greetings humans";
        $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
        $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way

        //OPTIONAL: Attach related data of various types using Insight setter functions
        //$my_insight->setPosts($my_insight_posts);
        //$my_insight->setLinks($my_insight_links);
        //$my_insight->setPeople($my_insight_people);
        //etc

        $this->insight_dao->insertInsight($my_insight);
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('HelloThinkUpInsight');

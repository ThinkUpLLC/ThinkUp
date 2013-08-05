<?php
/*
 Plugin Name: Hello ThinkUp
 Description: Example developer insight plugin.
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

        //        $insight_dao = DAOFactory::getDAO('InsightDAO');
        //
        //        $insight_dao->insertInsight('hello_thinkup_insight', $instance->id, $this->insight_date,
        //        "Hello ThinkUp:", "Hello insight world ".$instance->network_username, Insight::EMPHASIS_LOW);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('HelloThinkUpInsight');

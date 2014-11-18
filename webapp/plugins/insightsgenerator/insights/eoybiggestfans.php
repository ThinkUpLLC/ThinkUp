<?php
/*
 Plugin Name: Biggest Fans (End of Year)
 Description: Who has liked your posts the most over the last year.
 When: December 2
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoybiggestfans.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYBiggestFansInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $slug = 'eoy_biggest_fans';
        $date = '12-02';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date,
            $excluded_networks = array('twitter')
        );

        if ($should_generate_insight) {
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            $fans = $fav_dao->getUsersWhoFavoritedMostOfYourPosts($instance->network_user_id,
                $instance->network, TimeHelper::getDaysSinceJanFirst());
            if (!isset($fans) || sizeof($fans) == 0 ) {
                return;
            }

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $post_dao = DAOFactory::getDAO('PostDAO');
            $date = date('Y-m-d');
            $network = $instance->network;

            $url = Utils::getApplicationURL()."?";
            $url_params = array(
                "u" => $instance->network_username,
                "n" => $network,
                "d" => $date,
                "s" => $insight->slug
            );
            $url .= http_build_query($url_params);

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's biggest Twitter fans of %year",
                        'body' => "It's good to have fans. %user_list faved %username's " .
                            "tweets the most this year."
                    ),
                    'action_button' => array(
                        'label' => "That's probably worth a thanks!",
                        'url' => "https://twitter.com/intent/tweet?related=thinkup&text=" .
                            "Thanks for the faves, %user_list!&url=". $url ."&via=thinkup"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's biggest Facebook fans of %year",
                        'body' => "It feels great to have friends who support you. " .
                        "%user_list liked %username's status updates the most this year."
                    ),
                    'action_button' => array(
                        'label' => "Tell 'em you appreciate it!",
                        'url' => 'https://www.facebook.com/sharer.php?u=' . $url
                    )
                )
            );

            $type = 'normal';
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year
                )
            );

            $fan_list = array();
            foreach ($fans as $fan) {
                if ($network == 'twitter') {
                    $fan_list[] = "@".$fan->username;
                }
                else {
                    $fan_list[] = $fan->full_name;
                }
            }
            $fan_list = $this->makeList($fan_list);
            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'user_list' => $fan_list
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->setPeople($fans);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            if ($instance->is_public) {
                $copy[$network]['action_button']['url'] = $this->getVariableCopy(
                    array(
                        $copy[$network]['action_button']['url']
                    ),
                    array(
                        'user_list' => $fan_list
                    )
                );
                $insight->setButton($copy[$network]['action_button']);
            }

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Turn an array of items into a list with "and"
     * @param arr $items Array of strings to join in a list
     * @return str List of items, like "Item 1, item 2, and item 3"
     */
    private function makeList($items) {
        $count = count($items);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $items[0];
        }

        if ($count === 2) {
            return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
        }

        if ($count > 2) {
            return implode(', ', array_slice($items, 0, -1)) . ', and ' . end($items);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYBiggestFansInsight');

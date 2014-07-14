 <?php
 /*
  Plugin Name: Diversify your links
  Description: Encourages user to share links from different sources.
  When: 25th of the month, Every Wednesday.
  */
 /**
  *
  * ThinkUp/webapp/plugins/insightsgenerator/insights/diversifyyourlinks.php
  *
  * Copyright (c) 2014 Gareth Brady
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
  * @copyright 2014 Gareth Brady
  * @author Gareth Brady <gareth.brady92 [at] gmail [dot] com>
  */

class DiversifyLinksInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     */
    var $slug = 'diversify_links'; 
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $should_generate_insight_weekly = $this->shouldGenerateWeeklyInsight($this->slug, $instance, 'today',
        $regenerate=false, 3);
      
        $should_generate_insight_monthly = $this->shouldGenerateMonthlyInsight($this->slug, $instance, 'today',
        $regenerate=false, 25);
        if($should_generate_insight_weekly) {
            $link_dao = DAOFactory::getDAO('LinkDAO');
            $terms = new InsightTerms($instance->network);
            $links = $link_dao->getLinksByUserSinceDaysAgo($instance->network_user_id, $instance->network, 0, 7);
            if(count($links > 5)) {
                $most_used_url = $this->getUrlData($links, 'most_used_url');
                if($most_used_url != NULL) {
                    $url_counts = array();
                    $graph_links = $link_dao->getLinksByUserSinceDaysAgo($instance->network_user_id,
                    $instance->network, 100, 0); //Gets link objects for use in the graph.
                    $last_x_links_text ='';
                    $followers_friends_text = "";
                    $text1 ='';
                    $text2 ='';
                    $text3 = '';
                    $followers_friends_text = $terms->getNoun('follow', InsightTerms::PLURAL);

                    // if($instance->network == 'twitter') {
                    //     $followers_friends_text = "followers";
                    // } else {
                    //     $followers_friends_text = "friends";
                    // }
                   
                    if(count($graph_links) >= 50 && count($graph_links) < 100 ) {
                        $fifty_links = array_slice($graph_links, 0, 50, true);
                        $vis_data = $this->getUrlData($fifty_links,'vis_data');
                        $last_x_links_text = "Here's a breakdown of $instance->network_username's last ";
                        $last_x_links_text .= "<strong>50</strong> links:";
                    } elseif(count($graph_links) == 100) {
                        $vis_data = $this->getUrlData($graph_links,'vis_data');
                        $last_x_links_text = "Here's a breakdown of $instance->network_username's last ";
                        $last_x_links_text .= "<strong>100</strong> links:";
                    }
                    $text1 = "Over <strong>half</strong> of the links $this->username shared on ";
                    $text1 .= "$instance->network last week came from <strong>$most_used_url</strong>.<br> ";
                    $text1 .= "Sharing a variety of links allows $followers_friends_text to find out what interests ";
                    $text1 .= "$this->username. <br><br> $last_x_links_text";
                    $text2 = "The <strong>majority</strong> of the links $this->username shared last week went to ";
                    $text2 .= "<strong>$most_used_url</strong>.<br> Sharing links to different websites is a great way for";
                    $text2 .= " $followers_friends_text to get to know $this->username. <br><br> ";
                    $text2 .= "$last_x_links_text";
                    $text3 = "Over <strong>50%</strong> of the links $this->username shared last week went to ";
                    $text3 .= "<strong>$most_used_url</strong>.<br> ";
                    $text3 .= "Did you know using a wide variety of links is a great way $this->username's ";
                    $text3 .= "$followers_friends_text to learn about $this->username's";
                    $text3 .= "interests ? <br><br> $last_x_links_text";
                    $insight = new Insight();
                    $insight->slug = 'diversify_links_weekly';
                    $insight->instance_id = $instance->id;
                    $insight->date = $this->insight_date;
                    $insight->headline = $this->getVariableCopy(array(
                        "Why not share a new website this week ?",
                        "Looks like $instance->network_username likes $most_used_url.",
                        "Spread the love."
                    ), array('network' => ucfirst($instance->network)));
                    $insight->text = $this->getVariableCopy(array(
                        $text1,$text2,$text3
                    ));
                    $insight->setBarChart($vis_data);
                    $insight->filename = basename(__FILE__, ".php");
                    $this->insight_dao->insertInsight($insight);
                } 
            }
        } 
        if($should_generate_insight_monthly) {
            $link_dao = DAOFactory::getDAO('LinkDAO');
            $links = $link_dao->getLinksByUserSinceDaysAgo($instance->network_user_id, $instance->network, 0, date('t'));
            $most_used_url = $this->getUrlData($links, 'most_used_url');
            if($most_used_url != NULL) {
                $resultset;
                $metadata;
                $vis_data;
                $url_counts = array();
                $last_x_links_text ='';
                $followers_friends_text = "";
                $text1 ='';
                $text2 ='';
                $text3 = '';
                $graph_links = $link_dao->getLinksByUserSinceDaysAgo($instance->network_user_id, $instance->network,
                100, 0);
                if($instance->network == 'twitter') {
                    $followers_friends_text = "followers";
                } else {
                    $followers_friends_text = "friends";
                }
                if(count($graph_links) >= 50 && count($graph_links) < 100 ) {
                    $fifty_links = array_slice($graph_links, 0, 50, true);
                    $vis_data = $this->getUrlData($fifty_links,'vis_data');
                    $last_x_links_text = "Here's a breakdown of $instance->network_username's last ";
                    $last_x_links_text .= "<strong>50</strong> links:";
                } elseif(count($graph_links) == 100) {
                    $vis_data = $this->getUrlData($graph_links,'vis_data');
                    $last_x_links_text = "Here's a breakdown of $instance->network_username's last ";
                    $last_x_links_text .= "<strong>100</strong> links:";
                }
                $text1 = "Over <strong>half</strong> of the links $this->username shared on ";
                $text1 .= "$instance->network last month came from <strong>$most_used_url</strong>.<br> ";
                $text1 .= "Sharing a variety of links allows $followers_friends_text to find out what interests ";
                $text1 .= "$this->username. <br><br> $last_x_links_text";
                $text2 = "The <strong>majority</strong> of the links $this->username shared last month went to ";
                $text2 .= "<strong>$most_used_url</strong>.<br> Sharing links to different websites is a great way for";
                $text2 .= " $followers_friends_text to get to know $this->username. <br><br> ";
                $text2 .= "$last_x_links_text";
                $text3 = "Over <strong>50%</strong> of the links $this->username shared last month went to ";
                $text3 .= "<strong>$most_used_url</strong>.<br> ";
                $text3 .= "Did you know using a wide variety of links is a great way $this->username's ";
                $text3 .= "$followers_friends_text to learn about $this->username's";
                $text3 .= "interests ? <br><br> $last_x_links_text";
                $insight = new Insight();
                $insight->slug = 'diversify_links_monthly';
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->headline = $this->getVariableCopy(array(
                    "Why not share some new websites next month ?",
                    "Looks like $instance->username's most used website was $most_used_url.",
                    "Spread the love."
                ), array('network' => ucfirst($instance->network)));
                $insight->text = $this->getVariableCopy(array(
                    $text1,$text2,$text3
                ));
                $insight->setBarChart($vis_data);
                $insight->filename = basename(__FILE__, ".php");
                $this->insight_dao->insertInsight($insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    /**
     * Get the Url data. Calculates the data for the most popular link and
     * if it accounts for over 50% of all links shared.
     * Creats vis_data for pie_chart.
     *
     * @param arr array of links.
     * @param str string declaring what data user wants returned.
     * @return str The url of the site or NULL.
     * @return json Contains data to be passed to GoogleCharts.
     */
    private function getUrlData($links, $get_option) {
        $domain_start;
        $domain_end;
        $domain;
        $url_counts = array();
        // if(count($links) < 5) {
        //     return null;
        // }
        foreach($links as $link) {
            if($link->expanded_url == "") {
                continue;
            } else {
                $url = parse_url($link->expanded_url);
                $domain = $url['host'];
            }
            if(array_key_exists($domain, $url_counts)) {
                $url_counts[$domain]++;
            } else {
                $url_counts[$domain] = 1;
            }
        }
        if($get_option == 'most_used_url') {
            if(max($url_counts)/array_sum($url_counts) > 0.5) {
                return array_search(max($url_counts),$url_counts);
            } else {
                return null;
            }
        } elseif($get_option =='vis_data') {
            $resultset = array();
            $metadata = array();
            foreach ($url_counts as $links => $count) {
                $resultset[] = array('c' => array( array('v' =>$links), array('v' => $count)));
                $metadata = array( array('type' => 'string', 'label' => 'Url'),
                            array('type' => 'number', 'label' => 'Number of Shares'),
                            );
            }
            $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
            return $vis_data;
        }
    }
}

 $insights_plugin_registrar = PluginRegistrarInsights::getInstance();
 $insights_plugin_registrar->registerInsightPlugin('DiversifyLinksInsight');
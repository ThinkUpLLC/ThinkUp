 <?php
 /*
  Plugin Name: Diversify your links
  Description: Encourages user to share links from different sources.
  When: Monthly (3rd for Twitter, 1st for Facebook)
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
    var $slug = '';
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $day_of_month = $instance->network == 'facebook' ? 1 : 3;
        $should_generate_insight_monthly = $this->shouldGenerateMonthlyInsight($this->slug, $instance, 'today',
            $regenerate=false, $day_of_month);

        if ($should_generate_insight_monthly) {
            $link_dao = DAOFactory::getDAO('LinkDAO');
            $links =$link_dao->getLinksByUserSinceDaysAgo($instance->network_user_id, $instance->network, 0, date('t'));
            $slug = "diversify_links_monthly";
            $time_frame = "month";
            if(count($links ) > 4) {
                $this->runInsight($links, $slug, $time_frame, $instance, $link_dao);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Creates text and headline strings and gets data for vis_data. Inserts Insight.
     * @param arr $links.
     * @param str $slug.
     * @param str $time_frame.
     * @param instance $instance.
     * @param dao $link_dao.
     */
    private function runInsight($links, $slug, $time_frame,$instance, $link_dao) {
        $total_counts = array();
        $tweet_counts = array();
        $retweet_counts = array();
        foreach($links as $link) {
            if($link['expanded_url'] == "") {
                continue;
            } else {
                $url = parse_url($link['expanded_url']);
                $domain = $url['host'];
            }

            if(!array_key_exists($domain, $total_counts)) {
                $total_counts[$domain] = 0;
            }
            if(!array_key_exists($domain, $tweet_counts)) {
                $tweet_counts[$domain] = 0;
            }
            if(!array_key_exists($domain, $retweet_counts)) {
                $retweet_counts[$domain] = 0;
            }
            $total_counts[$domain]++;
            if ($link['in_retweet_of_post_id']) {
                $retweet_counts[$domain]++;
            }
            else {
                $tweet_counts[$domain]++;
            }
        }

        $popular_url = array_search(max($total_counts),$total_counts);

        arsort($total_counts, SORT_NUMERIC);
        $url_counts = array_slice($total_counts, 0, 20);
        $most_links = max($url_counts);

        $insight = new Insight();
        $insight->related_data["url_counts"] = $url_counts;
        $insight->emphashis = Insight::EMPHASIS_HIGH;
        $insight->filename = basename(__FILE__, ".php");
        $insight->slug = $slug;
        $insight->instance_id = $instance->id;
        $insight->date = $this->insight_date;

        $counts = array_values($url_counts);

        if($counts[0] != $counts[1]) {
            $tweets = $tweet_counts[$popular_url];
            $retweets = $retweet_counts[$popular_url];
            if ($tweets == 0) {
                $a = "It was the best of tabs, it was the worst of tabs. %username %retweeted "
                    . "%rnum %rlinks to %site_title this month &mdash; more than to any other site.";
                $b = "%username shared links to %site_title this in %rnum %retweet"
                    . ($retweet_counts[$popular_url] == 1?'':'s')." this month &mdash; more than to any other site.";
            } else if ($retweets == 0) {
                $a = "It was the best of tabs, it was the worst of tabs. %username %posted %pnum %plinks "
                    . "to %site_title this month &mdash; more than to any other site.";
                $b = "%username shared links to %site_title in %pnum %post"
                    . ($tweet_counts[$popular_url] == 1?'':'s')." this month &mdash; more than to any other site.";
            } else {
                $a = "It was the best of tabs, it was the worst of tabs. %username %posted %pnum %plinks and %retweeted"
                    . " %rnum %rlinks to %site_title this month &mdash; more than to any other site.";
                $b = "%username shared links to %site_title in %pnum "
                    . "%post".($tweet_counts[$popular_url] == 1?'':'s')." and %rnum "
                    . "%retweet".($retweet_counts[$popular_url] == 1?'':'s')." this month &mdash; "
                    . "more than to any other site.";
            }
            $text = $this->getVariableCopy(array($a, $b), array(
                'pnum' => $tweet_counts[$popular_url],
                'plinks' => $tweet_counts[$popular_url] == 1 ? 'link' : 'links',
                'rnum' => $retweet_counts[$popular_url],
                'rlinks' => $retweet_counts[$popular_url] == 1 ? 'link' : 'links',
                'site_title' => $popular_url
            ));
        } else {
            $num = 2;
            if (isset($counts[3]) && $counts[3] == $counts[0]) {
                $num = 4;
            } else if (isset($counts[2]) && $counts[2] == $counts[0]) {
                $num = 3;
            }
            $sites = array_slice(array_keys($url_counts), 0, $num);
            $sites[count($sites)-1] = 'and '.$sites[count($sites)-1];
            $sites = join(count($sites)==2 ? ' ' : ', ', $sites);
            $text ="It's good to spread the link love. ".$this->username." shared links equally to "
                . $sites." this month.";
        }
        $headline = $this->username."'s most linked-to site".(count($url_counts)>1 ?'s':'')." this month";
        $insight->headline = $headline;
        $insight->text = $text;
        $this->insight_dao->insertInsight($insight);
    }
}

 $insights_plugin_registrar = PluginRegistrarInsights::getInstance();
 $insights_plugin_registrar->registerInsightPlugin('DiversifyLinksInsight');

<?php
/*
 Plugin Name: Top Words (End of Year)
 Description: Most-used words in posts this year.
 When: December 22
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoytopwords.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Copyright (c) 2014-2016 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 */

class EOYTopWordsInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_top_words';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-22';
    //staging
    //var $run_date = '12-18';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $year = date('Y');
        $regenerate = false;
        //testing
        //$regenerate = true;

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug,
            $instance,
            $insight_date = "$year-$this->run_date",
            $regenerate,
            $day_of_year = $this->run_date
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $instance->network
            );

            $words = $this->getWordsFromPosts($last_year_of_posts);
            $total_top_words = count($words);
            if ($total_top_words == 0) {
                $this->logger->logInfo("Done Generating Insight (no words)", __METHOD__.','.__LINE__);
                return;
            }

            // Get qualified year
            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.")";
                }
            }

            foreach ($words as $key => $word) {
                $words[$key][0] = '&#8220;'.$word[0];
            }

            $first = $words[0][0]. "&#8221;";
            $top_words = array_slice($words, 1, 4);
            $num_words = count($top_words);
            $rest = false;
            if ($num_words == 1) {
                $rest = $top_words[0][0].".&#8221;";
            } else if ($num_words == 2) {
                $top_words[$num_words-1][0] = '&#8221; and '.$top_words[$num_words-1][0];
                $tmp_words = array_map(create_function('$a','return $a[0];'), $top_words);
                $rest = join($num_words > 2 ? ",&#8221; " : "", $tmp_words).".&#8221;";
            } else if ($num_words > 2) {
                $top_words[$num_words-1][0] = 'and '.$top_words[$num_words-1][0];
                $tmp_words = array_map(create_function('$a','return $a[0];'), $top_words);
                $rest = join($num_words > 2 ? ",&#8221; " : "", $tmp_words).".&#8221;";
            }
            if ($instance->network == 'facebook') {
                $text = "How to describe $year? ".$this->username." used <strong>$first ".number_format($words[0][1]).
                    " times</strong> on Facebook this year$qualified_year. That's more than any other word";
                if (!$rest) {
                    $text .= '.';
                } else {
                    $text .= " &mdash; followed by $rest Sound about right?";
                }
                $headline = $this->username."'s most-used word".($total_top_words==1?'':'s')." on Facebook, $year";
            } else {
                $network = ucfirst($instance->network);
                $text = "Would you say it's been a $first year? " . $this->username . " might. " . $this->username .
                    " mentioned <strong>$first ".number_format($words[0][1]).
                    " times</strong> on $network in $year$qualified_year. That's more than any other word this year";
                if (!$rest) {
                    $text .= '.';
                } else {
                    $text .= " &mdash; followed by $rest";
                }
                $headline = $this->username."'s most-used word".($total_top_words==1?'':'s')." on $network, $year";
            }

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";
            $insight->headline = $headline;
            $insight->text = $text;
            $insight->header_image = $user->avatar;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $rows = array();
            foreach ($words as $word) {
                $rows[] = array('c'=>array(array('v'=>str_replace('&#8220;','',$word[0])), array('v' => $word[1])));
            }
            $insight->setBarChart(array(
                'cols' => array(
                    array('label' => 'Word', 'type' => 'string'),
                    array('label' => 'Occurences', 'type' => 'number'),
                ),
                'rows' => $rows
            ));

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Cretae a list of most-used words from posts
     * @param $posts arr Array of posts to analyze
     * @return arr The most-used words
     */
    private function getWordsFromPosts($posts) {
        $top_words_insight = new TopWordsInsight();
        $all_words = array();
        foreach ($posts as $p) {
            $words = preg_split('/\s+/', html_entity_decode($p->post_text));
            $words = array_filter($words, create_function('$a', 'return !in_array(substr($a, 0, 1), array("@","#"));'));
            $words = array_map(create_function('$a', "return preg_replace('/^[^a-zA-Z0-9]+/', '', \$a);"), $words);
            $words = array_map(create_function('$a', "return preg_replace('/[^a-zA-Z0-9]+$/', '', \$a);"), $words);
            $words = array_filter($words, create_function('$a', 'return strlen($a);'));
            foreach ($words as $word) {
                if (in_array(strtolower($word), $top_words_insight->stop_words)) {
                    continue;
                }
                $stem = Utils::stemWord(strtolower($word));
                if (!isset($all_words[$stem])) {
                    $all_words[$stem] = array();
                }
                $all_words[$stem][] = $word;
            }
        }
        uasort($all_words, create_function('$a,$b', '$a=count($a); $b=count($b); return $a==$b?0:$a>$b?-1:1;'));
        $top_word_lists = array_slice($all_words, 0, 20);
        $top_words = array();
        foreach ($top_word_lists as $list) {
            if (count($list) < 3) {
                continue;
            }
            $counts = array();
            foreach ($list as $w) {
                if (!isset($counts[$w])) $counts[$w] = 0;
                $counts[$w]++;
            }
            arsort($counts);
            $keys = array_keys($counts);
            if (count($keys)) {
                $top_words[] = array($keys[0], count($list));
            }
        }
        return $top_words;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYTopWordsInsight');

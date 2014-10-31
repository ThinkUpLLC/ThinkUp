<?php
/*
 Plugin Name: Top Words
 Description: What words did you use the most this week/month
 When: Weekly, Saturday for Twitter, Friday for Facebook and Monthly, 25th for Facebook, 27th for Twitter
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/topwords.php
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
class TopWordsInsight extends InsightPluginParent implements InsightPlugin {
    var $stopwords = array(
        // MySQL stop word list
        "a","able","about","across","after","all","almost","also","am","among","an","and","any","
        are","as","at","be","because","been","but","by","can","cannot","could","dear","did","do","does","either",
        "else","ever","every","for","from","get","got","had","has","have","he","her","hers","him","his","how","
        however","i","if","in","into","is","it","its","just","least","let","like","likely","may","me","might","most",
        "must","my","neither","no","nor","not","of","off","often","on","only","or","other","our","own","rather","said",
        "say","says","she","should","since","so","some","than","that","the","their","them","then","there","these",
        "they","this","tis","to","too","twas","us","wants","was","we","were","what","when","where","which","while",
        "who","whom","why","will","with","would","yet","you","your","ain't","aren't","can't","could've","couldn't",
        "didn't","doesn't","don't","hasn't","he'd","he'll","he's","how'd","how'll","how's","i'd","i'll","i'm","i've",
        "isn't","it's","might've","mightn't","must've","mustn't","shan't","she'd","she'll","she's","should've",
        "shouldn't","that'll","that's","there's","they'd","they'll","they're","they've","wasn't","we'd","we'll",
        "we're","weren't","what'd","what's","when'd","when'll","when's","where'd","where'll","where's","who'd",
        "who'll","who's","why'd","why'll","why's","won't","would've","wouldn't","you'd","you'll","you're","you've",

        // Our list
        "rt","is","are");

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $monthly = 0;
        $weekly = 0;
        if ($instance->network == 'twitter') {
            $weekly = 7;
            $monthly = 27;
        } else if ($instance->network == 'facebook') {
            $weekly = 6;
            $monthly = 25;
        } else if ($instance->network == 'test_no_monthly') {
            $monthly = 0;
            $weekly = 2;
        }

        $did_monthly = false;
        $post_dao = DAOFactory::getDAO('PostDAO');
        if ($monthly && self::shouldGenerateMonthlyInsight('top_words_month', $instance, 'today', false, $monthly)) {
            $day = 60 * 60 * 24;
            $month_days = date('t');
            $last_month_days = date('t', time() - ($month_days * $day));
            $old_posts = $post_dao->getPostsByUserInRange($instance->network_user_id, $instance->network,
                date('Y-m-d', time() - ((($month_days + $last_month_days) * $day))),
                date('Y-m-d', time() - ($month_days * $day)));
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
                $count=0, $order_by="pub_date", $in_last_x_days = date('t'), $iterator = false, $is_public = false);
            $this->generateForPeriod($instance, $posts, $old_posts, 'month');
            $did_monthly = true;
        }

        $do_weekly = $weekly && !$did_monthly;
        if ($do_weekly && self::shouldGenerateWeeklyInsight('top_words_week', $instance, 'today', false, $weekly)) {
            $day = 60 * 60 * 24;
            $old_posts = $post_dao->getPostsByUserInRange($instance->network_user_id, $instance->network,
                date('Y-m-d', time() - (14 * $day)),
                date('Y-m-d', time() - (7 * $day)));
            $this->generateForPeriod($instance, $last_week_of_posts, $old_posts, 'week');
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function getWordsFromPosts($posts) {
        $all_words = array();
        foreach ($posts as $p) {
            $words = preg_split('/\s+/', $p->post_text);
            $words = array_filter($words, create_function('$a', 'return substr($a, 0, 1) != "@";'));
            $words = array_map(create_function('$a', "return preg_replace('/[^a-zA-Z0-9\\' -]/', '', \$a);"), $words);
            $words = array_filter($words, create_function('$a', 'return strlen($a) && !preg_match("/[^a-z]/i", $a);'));
            foreach ($words as $word) {
                if (in_array(strtolower($word), $this->stopwords)) {
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
        $top_word_lists = array_slice($all_words, 0, 5);
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
                $top_words[] = $keys[0];
            }
        }
        return $top_words;
    }

    private function generateForPeriod($instance, $posts, $old_posts, $period) {
        $top_words = $this->getWordsFromPosts($posts);
        $old_words = $this->getWordsFromPosts($old_posts);

        if (count($top_words) == 0) {
            return;
        }

        foreach ($top_words as $key => $word) {
            $top_words[$key] = '&quot;'.$word.'&quot;';
        }

        $text = $this->username." mentioned <b>".$top_words[0]."</b> more than anything else on ".ucfirst($instance->network)
            . " this $period";
        array_shift($top_words);
        $num_words = count($top_words);
        if ($num_words == 1) {
            $text .= ", followed by ".$top_words[0].".";
        }
        else if ($num_words > 1) {
            $top_words[$num_words-1] = 'and '.$top_words[$num_words-1];
            $text .= ", followed by ".join($num_words > 2 ? ", " : " ", $top_words).".";
        } else {
            $text .= '.';
        }

        if (count($old_words)) {
            foreach ($old_words as $key => $word) {
                $old_words[$key] = '&quot;'.$word.'&quot;';
            }
            $text .= " That's compared to last $period, when ".$this->username."'s most-used word"
                . (count($old_words)==1?" was":"s were")." ";
            $num_words = count($old_words);
            if ($num_words == 1) {
                $text .= $old_words[0].".";
            } else {
                $old_words[$num_words-1] = 'and '.$old_words[$num_words-1];
                $text .= join($num_words > 2 ? ", " : " ", $old_words).".";
            }
        }


        $insight = new Insight();
        $insight->slug = 'top_words_'.$period;
        $insight->instance_id = $instance->id;
        $insight->date = $this->insight_date;
        $insight->headline = "Your most-used word".(count($top_words)==1?'':'s')." last $period";
        $insight->text = $text;
        $insight->emphasis = $period == 'week' ? Insight::EMPHASIS_MED : Insight::EMPHASIS_HIGH;
        $insight->filename = basename(__FILE__, ".php");
        $this->insight_dao->insertInsight($insight);
    }
}

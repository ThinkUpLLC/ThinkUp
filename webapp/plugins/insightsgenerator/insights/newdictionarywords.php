<?php
/*
 Plugin Name: New Dictionary-Word Count
 Description: New dictionary-word counts.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/newdictionarywords.php
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
class NewDictionaryWordsInsight extends InsightPluginParent implements InsightPlugin {

    public function getSchedule() {
        return array(
            'oxford_august_2014' => array(
                'words' => array(
                    'baller', 'binge-watch', 'clickbait', 'cray', 'dox', 'FML', 'hot mess', 'humblebrag',
                    'ICYMI', 'mansplain', 'neckbeard', 'side-eye', 'SMH', 'subtweet', 'YOLO',
                ),
                'start' => '2014-08-25',
                'end' => '2014-09-25',
                'headline' => 'Before "%word" went legit',
                'single_template' => '%username used the word "%word" %total_times since %first_mention, '
                    . 'and it appears to have caught on: It\'s just been added to the Oxford English Dictionary.',
                'multiple_template' => 'The Oxford English Dictionary just added %word_list to their online dictionary, '
                    . 'but no one has to explain them to %username. Since %first_mention, %username used '
                    . '%word_times_list.',
                'image' => array(
                    'img_link' => 'http://www.flickr.com/photos/bethanyking/822518337',
                    'alt_text' => 'New words',
                    'credit' => 'Bethany King: http://www.flickr.com/photos/bethanyking/',
                    'url' => 'https://www.thinkup.com/assets/images/insights/2014-08/new_dictionary_words.jpg'
                )
            ),
        );
    }

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        foreach ($this->getSchedule() as $baseline_slug=>$data) {
            $now = TimeHelper::getTime();
            if ($now >= strtotime($data['start']) && $now <= strtotime($data['end'])) {
                $baseline = $baseline_dao->getMostRecentInsightBaseline($baseline_slug, $instance->id);
                if (!$baseline) {
                    $found = $this->runInsightForConfig($data, $instance);
                    $baseline_dao->insertInsightBaseline($baseline_slug, $instance->id, $found);
                }
            }
        }


        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function runInsightForConfig($config, $instance) {
        $regex = '/\b('.join('|', array_map('preg_quote', $config['words'])).')\b/i';
        $usage = array_fill_keys(array_map('strtolower', $config['words']), 0);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameIterator($instance->network_username, $instance->network);
        $first_date = time();
        foreach ($posts as $post) {
            if (preg_match_all($regex, $post->post_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $usage[strtolower($match)]++;
                }
                if (strtotime($post->pub_date) < $first_date) {
                    $first_date = strtotime($post->pub_date);
                }
            }
        }
        $usage = array_filter($usage);
        if (count($usage)) {
            $formatted_usage = array();
            foreach ($usage as $word => $times) {
                foreach ($config['words'] as $formatted_word) {
                    if (strtolower($formatted_word) == $word) {
                        $formatted_usage[$formatted_word] = $times;
                        break;
                    }
                }
            }
            arsort($formatted_usage);

            $insight = new Insight();
            $insight->slug = 'new_dictionary_words';
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_MED;
            if (!empty($config['image'])) {
                $insight->setHeroImage($config['image']);
            }

            if (count($formatted_usage) == 1) {
                $words = array_keys($formatted_usage);
                $template = $config['single_template'];
                $params = array(
                    'first_mention' => date('F Y', $first_date),
                    'word' => $words[0],
                    'total_times' => $this->terms->getOccurrencesAdverb($formatted_usage[$words[0]])
                );
            }
            else {
                $formatted_usage = array_slice($formatted_usage, 0, 5, true);
                $words = array_keys($formatted_usage);
                $template = $config['multiple_template'];
                $params = array('first_mention' => date('F Y', $first_date));
                $times = array();
                $quoted_words = array();
                foreach ($formatted_usage as $word => $t) {
                    $times[] = '"'.$word.'" '.$this->terms->getOccurrencesAdverb($t);
                    $quoted_words[] = '"'.$word.'"';
                }
                $last = count($times) - 1;
                $times[$last] = 'and '.$times[$last];
                $quoted_words[$last] = 'and '.$quoted_words[$last];
                $sep = count($times) == 2 ? ' ' : ', ';
                $params['word_times_list'] = join($sep, $times);
                $params['word_list'] = join($sep, $quoted_words);
            }

            $insight->text = $this->getVariableCopy(array($template), $params);
            $insight->headline = $this->getVariableCopy(array($config['headline']), array('word' => $words[0]));

            $this->insight_dao->insertInsight($insight);
        }
        return array_sum($usage);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('NewDictionaryWordsInsight');

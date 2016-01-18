<?php
/*
 Plugin Name: Top Stories (End of Year)
 Description: Show the user their relevance by mentioning which major news stories they wrote about this year.
 When: December 18
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoytopstories.php
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

class EOYTopStoriesInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_top_stories';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-18';
    //staging
    //var $run_date = '12-16';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        // if ($instance->network != 'facebook') {
        //     $this->logger->logInfo("Done generating insight (Skipped Non-Facebook)", __METHOD__.','.__LINE__);
        //     return;
        // }

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

            $topics = array(
                'the U.S. Presidential election' => array('trump','clinton', 'sanders', 'jeb bush'),
                'the Syrian refugee crisis' => array('Syria', 'refugee'),
                'terror attacks' => array(' ISIS ', 'daesh', ' ISIL ', ' Paris ', 'hebdo', 'jesuischarlie'),
                'the Nepal earthquake' => array('Nepal', 'earthquake'),
                'marriage equality' => array('marriage equality', 'gay marriage', 'obergefell', 'lovewins'),
                'the Planned Parenthood attack' => array('planned parenthood', 'abortion', 'colorado springs'),
                '#BlackLivesMatter' => array('Freddie Gray', 'Baltimore', 'blacklivesmatter'),
                'Charleston' => array('Charleston', 'confederate', 'emanuel'),
                'Ahmed Mohamed' => array('Ahmed Mohamed', 'istandwithahmed'),
                'Floyd Mayweather, Jr.' => array('mayweather'),
                'Manny Pacquiao' => array('pacquiao'),
                'Ronda Rousey' => array('ronda rousey', 'RondaRousey'),
                'Tom Brady' => array('tom brady'),
                'Stephen Curry' => array('stephen curry'),
                'Serena Williams' => array('serena'),
                'Ed Sheeran' => array('sheeran'),
                'Taylor Swift' => array('taylor swift', 'taylorswift13'),
                'Kanye West' => array('kanye'),
                'Caitlyn Jenner' => array('jenner', 'Caitlyn_Jenner'),
                'Star Wars' => array('star wars', 'force awakens', 'episode vii', 'episode 7', 'bb8',
                    'starwarstheforceawakens', 'starwars', 'theforceawakens'),
                'Avengers: Age of Ultron' => array('avengers'),
                'Mad Max: Fury Road' => array('mad max', 'fury road', 'imperator', 'furiosa'),
                'Magic Mike XXL' => array('magic mike'),
                'Game of Thrones' => array('game of thrones', 'jon snow', 'seven hells'),
                'The Walking Dead' => array('walking dead'),

            );

            $matches = array();
            foreach ($last_year_of_posts as $post) {
                foreach ($topics as $key => $strings) {
                    foreach ($strings as $string) {
                        if (stristr($post->post_text, $string) !== FALSE) {
                            $matches[$key] = array('term'=>$key, 'post'=>$post);
                            $this->logger->logInfo("Matched  ".$string." with \"".$post->post_text."\"",
                                __METHOD__.','.__LINE__);
                            unset($topics[$key]);
                            break;
                        }
                    }
                }
            }

            if (count($matches) == 0) {
                if ($instance->network == 'facebook') {
                    $insight = new Insight();

                    $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
                    $qualified_year = "";
                    if (date('Y', strtotime($earliest_pub_date)) == date('Y')) {
                        if (date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                            //Earliest post was this year; figure out what month we have data since this year
                            $since = date('F', strtotime($earliest_pub_date));
                            $qualified_year = " (at least since ".$since.")";
                        }
                    }
                    $headline = $this->username." didn't rehash 2015's top news on Facebook";
                    $insight_text = "No Trump or Syrian refugee crisis here. "
                        . $this->username." broke away from the herd and avoided talking about 2015's biggest stories "
                        ."on Facebook this year" . $qualified_year. '.';
                    $posts = null;
                    //Show avatar if there are no posts
                    $insight->header_image = $user->avatar;
                    //Show button if there are no posts
                    $insight->setButton(array(
                        'url' => 'http://newsroom.fb.com/news/2015/12/2015-year-in-review/',
                        'label' => "See Facebook's Year in Review"
                    ));
                }
            } else {
                $insight = new Insight();

                $headline = $this->username." was part of $year's biggest trends";
                $posts = array();
                $mentioned = array();
                foreach ($matches as $m) {
                    if (count($posts) < 12) {
                        $posts[] = $m['post'];
                    }
                    $mentioned[] = $m['term'];
                }
                $num = count($mentioned);
                if ($num > 1) {
                    $mentioned[$num-1] = 'and '.$mentioned[$num-1];
                }
                $mention_string = join($num==2?' ':', ', $mentioned);
                $thatwas = $num == 1 ? 'That was one' : 'Those were some';
                $insight_text = $this->username."'s $year included $mention_string. $thatwas of "
                    . '<a href="http://newsroom.fb.com/news/2015/12/2015-year-in-review/">Facebook\'s top topics of '
                    . "the year</a>.";
            }

            if (isset($insight)) {
                $insight->instance_id = $instance->id;
                $insight->slug = $this->slug;
                $insight->date = "$year-$this->run_date";
                $insight->headline = $headline;
                $insight->text = $insight_text;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_HIGH;
                if ($posts) {
                    $link_dao = DAOFactory::getDAO('LinkDAO');
                    foreach ($posts as $post) {
                        $post->links = $link_dao->getLinksForPost($post->post_id, $post->network);
                    }
                    $insight->setPosts($posts);
                }

                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYTopStoriesInsight');

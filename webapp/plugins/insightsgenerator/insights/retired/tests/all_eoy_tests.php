<?php
/**
 *
 * ThinkUp/tests/all_plugin_tests.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gina Trapani
 */
include 'init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;
$eoy_insight_tests = new TestSuite('EOY insight tests');

//EOY insight tests
$eoy_insight_tests->add(new TestOfEOYMostFavlikedPostInsight());
$eoy_insight_tests->add(new TestOfEOYAllAboutYouInsight());
$eoy_insight_tests->add(new TestOfEOYMostTalkativeDayInsight());
$eoy_insight_tests->add(new TestOfEOYPopularPicInsight());
$eoy_insight_tests->add(new TestOfEOYExclamationCountInsight());
$eoy_insight_tests->add(new TestOfEOYFBombCountInsight());
$eoy_insight_tests->add(new TestOfEOYBiggestFansInsight());
$eoy_insight_tests->add(new TestOfEOYGenderAnalysisInsight());
$eoy_insight_tests->add(new TestOfEOYMostRetweetedPostInsight());
$eoy_insight_tests->add(new TestOfEOYBestieInsight());
$eoy_insight_tests->add(new TestOfEOYMostLinksInsight());
$eoy_insight_tests->add(new TestOfEOYWordCountInsight());
$eoy_insight_tests->add(new TestOfEOYMostConversationInsight());
$eoy_insight_tests->add(new TestOfEOYLOLCountInsight());
$eoy_insight_tests->add(new TestOfEOYPopularLinkInsight());
$eoy_insight_tests->add(new TestOfEOYWhoYouFavedInsight());
$eoy_insight_tests->add(new TestOfEOYLongestStreakInsight());
$eoy_insight_tests->add(new TestOfEOYControversialTopicsInsight());
$eoy_insight_tests->add(new TestOfEOYWhoYouAmplifiedInsight());
$eoy_insight_tests->add(new TestOfEOYTotalPostsInsight());
$eoy_insight_tests->add(new TestOfEOYMostPopularInsight());
$eoy_insight_tests->add(new TestOfEOYTopStoriesInsight());
$eoy_insight_tests->add(new TestOfEOYWordsOfYearInsight());
$eoy_insight_tests->add(new TestOfEOYTopWordsInsight());
$eoy_insight_tests->add(new TestOfEOYMostPopularPerMonthInsight());
$eoy_insight_tests->add(new TestOfThanksgivingWhoYouThankedInsight());
$eoy_insight_tests->add(new TestOfThanksgivingWhoThankedYouInsight());

$tr = new TextReporter();
$eoy_insight_tests->run( $tr );

if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    if (isset($TOTAL_PASSES) && isset($TOTAL_FAILURES)) {
        $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
        $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
    }
}

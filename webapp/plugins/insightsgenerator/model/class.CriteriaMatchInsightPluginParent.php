<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php
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
/**
 * Parent class for insights which track whether posts match certain criteria
 *
 * This parent class handles:
 *     - the creation and tracking of baselines
 *     - ensuring that enough posts are collected for the expected timeframe (as returned by getNumberOfDaysNeeded()
 *     - passing posts through postMatchesCriteria to generate counts
 *     - storing the (potentially) generated insight
 */
abstract class CriteriaMatchInsightPluginParent extends InsightPluginParent {
    /**
     * Determine if this function should generate an insight.
     * In nearly all cases, this should just call shouldGenerateInsight or shouldGenerateWeeklyInsight or
     * shouldGenerateMonthlyInsight
     *
     * @param Instance $instance The instance for which we're generating
     * @param array $last_week_of_posts Array of Post objects
     * @return bool Whether to generate this insight
     */
    public abstract function shouldGenerate(Instance $instance, $last_week_of_posts);

    /**
     * Get the slug for this insight and its baseline
     *
     * @return str The string to use for the slug
     */
    public abstract function getSlug();

    /**
     * Get how many days of posts are needed to generate the insight
     *
     * @return int How many days
     */
    public abstract function getNumberOfDaysNeeded();

    /**
     * Given a post, determine if this matches the criteria we're counting in this insight
     *
     * @param Post $post The Post to check
     * @return bool Does it match?
     */
    public abstract function postMatchesCriteria(Post $post);

    /**
     * Generate an insight based on the counts from this period, last period, and matching posts
     * This function may return null if an insight is not appropriate, but otherwise should instatiate and
     * return an Insight object with the appropriate headline, text, etc filled out.
     *
     * @param int $this_period_count How many posts matched the criteria
     * @param int $last_period_count How many matched last time
     * @param Instance $instance The instance we're generating for
     * @param array $matching_posts An array of Post objects that met the criteria
     * @return Insight The generated insight (or null)
     */
    public abstract function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts);

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($this->shouldGenerate($instance, $last_week_of_posts)) {

            if ($number_days < $this->getNumberOfDaysNeeded()) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
                    $count=0, $order_by="pub_date", $in_last_x_days = $this->getNumberOfDaysNeeded(),
                    $iterator = false, $is_public = false);
            }
            else {
                $posts = $last_week_of_posts;
            }

            $count = 0;
            $matching_posts = array();
            foreach ($posts as $post) {
                $matches = $this->postMatchesCriteria($post);
                if ($matches) {
                    $count++;
                    $matching_posts[] = $post;
                }
            }

            $baseline_name = $this->getSlug(). '_' . 'count';
            $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $last_baseline = $insight_baseline_dao->getMostRecentInsightBaseline($baseline_name, $instance->id);
            if ($last_baseline) {
                $last_count = $last_baseline->value;
            }
            else {
                $last_count = 0;
            }
            $insight_baseline_dao->insertInsightBaseline($baseline_name, $instance->id, $count, $this->insight_date);

            $insight = $this->getInsightForCounts($count, $last_count, $instance, $matching_posts);
            if ($insight) {
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

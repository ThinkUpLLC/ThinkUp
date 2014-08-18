<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Insight.php
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
 * Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Insight {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Instance ID.
     */
    var $instance_id;
    /**
     * @var str Identifier for a type of statistic.
     */
    var $slug;
    /**
     * @var str Headline of the insight content.
     */
    var $headline;
    /**
     * @var text Text content of the alert.
     */
    var $text;
    /**
     * @var text Serialized related insight data, such as a list of users or a post.
     */
    var $related_data;
    /**
     * @var str Optional insight header image.
     */
    var $header_image;
    /**
     * @var date Date of insight.
     */
    var $date;
    /**
     * @var int Level of emphasis for insight presentation.
     */
    var $emphasis;
    /**
     * @var str Name of file that generates and displays insight.
     */
    var $filename;
    /**
     * @var str Date and time when insight was generated.
     */
    var $time_generated;
    /**
     * @var str Time when the insight was last updated.
     */
    var $time_updated;
    /**
     * High emphasis level.
     * @var int
     */
    const EMPHASIS_HIGH = 2;
    /**
     * Medium emphasis level.
     * @var int
     */
    const EMPHASIS_MED = 1;
    /**
     * Low emphasis level.
     * @var int
     */
    const EMPHASIS_LOW = 0;

    public function __construct($row = false) {
        if ($row) {
            if (isset($row["insight_key"])) {
                $this->id = $row["insight_key"];
            } else {
                $this->id = $row['id'];
            }
            $this->instance_id = $row['instance_id'];
            $this->slug = $row['slug'];
            $this->headline = $row['headline'];
            $this->text = $row['text'];
            $this->related_data = $row['related_data'];
            $this->header_image = $row['header_image'];
            $this->date = $row['date'];
            $this->emphasis = $row['emphasis'];
            $this->filename = $row['filename'];
            $this->time_generated = $row['time_generated'];
            $this->time_updated = $row['time_updated'];
        } else {
            //non-null defaults
            if (!isset($this->emphasis)) {
                $this->emphasis = Insight::EMPHASIS_LOW;
            }
            if (!isset($this->time_generated)) {
                $this->time_generated=date("Y-m-d H:i:s");
            }
        }
    }
    /**
     * Add posts to insight's related data.
     * @param arr $posts Array of Post objects
     * @return void
     */
    public function setPosts($posts) {
        $this->related_data["posts"] = $posts;
    }
    /**
     * Add photos to insight's related data.
     * @param arr $photos Array of Photo objects
     * @return void
     */
    public function setPhotos($photos) {
        $this->related_data["photos"] = $photos;
    }
    /**
     * Add line chart to insight's related data.
     * @param arr $line_chart Line chart data.
     * @return void
     */
    public function setLineChart($line_chart) {
        $this->related_data["line_chart"] = $line_chart;
    }
    /**
     * Add bar chart to insight's related data.
     * @param arr $bar_chart Bar chart data.
     * @return void
     */
    public function setBarChart($bar_chart) {
        $this->related_data["bar_chart"] = $bar_chart;
    }
    /**
     * Add pie chart to insight's related data.
     * @param arr $pie_chart Pie chart data.
     * @return void
     */
    public function setPieChart($pie_chart) {
        $this->related_data["pie_chart"] = $pie_chart;
    }
    /**
     * Add people/users to insight's related data.
     * @param arr Array of User objects
     * @return void
     */
    public function setPeople($people) {
        $this->related_data["people"] = $people;
    }
    /**
     * Add links to insight's related data.
     * @param arr Array of Link objects
     * @return void
     */
    public function setLinks($links) {
        $this->related_data["links"] = $links;
    }
    /**
     * Add milestone number to insight's related data.
     * @param arr Array of milestone number data
     * @return void
     */
    public function setMilestones($milestones) {
        $this->related_data["milestones"] = $milestones;
    }
    /**
     * Add button to insight's related data.
     * @param arr Array of button data
     * @return void
     */
    public function setButton($button) {
        $this->related_data["button"] = $button;
    }
    /**
     * Add a hero image with alt text, credit, and a source link to insight's related data.
     * @param arr Array of hero image data. Sample values:
     *  array(
     *     'url' => 'https://www.thinkup.com/assets/images/insights/2014-02/olympics2014.jpg',
     *     'alt_text' => 'The Olympic rings in Sochi',
     *     'credit' => 'Photo: Atos International',
     *     'img_link' => 'http://www.flickr.com/photos/atosorigin/12568057033/'
     *   );
     * @return void
     */
    public function setHeroImage($hero_image) {
        $this->related_data["hero_image"] = $hero_image;
    }
}

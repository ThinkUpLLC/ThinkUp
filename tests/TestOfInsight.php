<?php
/**
 *
 * ThinkUp/tests/TestOfInsight.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of Insight class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsight extends ThinkUpBasicUnitTestCase {
    public function testInsightRelatedDataSetters() {
        $i = new Insight();
        // @TODO Assign and assert that the data is in the array structure it should be
        $i->setPhotos ("this is my list of photos");
        $this->assertEqual($i->related_data["photos"], "this is my list of photos");

        $i->setPosts("my posts");
        $this->assertEqual($i->related_data["posts"], "my posts");

        $i->setLineChart("line chart data goes here");
        $this->assertEqual($i->related_data["line_chart"], "line chart data goes here");

        $i->setBarChart("bar chart data goes here");
        $this->assertEqual($i->related_data["bar_chart"], "bar chart data goes here");

        $i->setPeople("list 'o users");
        $this->assertEqual($i->related_data["people"], "list 'o users");

        $i->setLinks("listoflinks");
        $this->assertEqual($i->related_data["links"], "listoflinks");

        $i->setMilestones("milestones");
        $this->assertEqual($i->related_data["milestones"], "milestones");

        $i->setButton("button");
        $this->assertEqual($i->related_data["button"], "button");

        $i->setHeroImage("Hero Image");
        $this->assertEqual($i->related_data["hero_image"], "Hero Image");
    }
}

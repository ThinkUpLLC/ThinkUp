<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpInsightUnitTestCase.php
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
 * ThinkUp Insight Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpInsightUnitTestCase extends ThinkUpUnitTestCase {

    /**
     * Set up necessary owner, instance, user, and owner_instance data to see fully-rendered insight markup
     * on debug.
     * @param Instance $instance Must have id, network, and network_username set
     * @return arr FixtureBuilders
     */
    protected function setUpPublicInsight(Instance $instance) {
        if (!isset($instance->network_user_id)) {
            $instance->network_user_id = '1001';
        }

        $builders = array();

        //Owner
        $pwd = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', 'salt');
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd, 'pwd_salt'=>'salt'));

        //Public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>$instance->id,
        'network_user_id'=>$instance->network_user_id, 'network_username'=>$instance->network_username ,
        'network'=>$instance->network, 'network_viewer_id'=>'10', 'crawler_last_run'=>'1988-01-20 12:00:00',
        'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11, 'posts_per_week'=>77));

        //Owner instance
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => $instance->id, 'owner_id'=>1) );

        //User
        $builders[] = FixtureBuilder::build('users', array('user_id' => $instance->network_user_id,
            'network'=>$instance->network) );

        return $builders;
    }
}

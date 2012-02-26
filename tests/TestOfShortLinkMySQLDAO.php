<?php
/**
 *
 * ThinkUp/tests/TestOfShortLinkMySQLDAO.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfShortLinkMySQLDAO extends ThinkUpUnitTestCase {

    public function testCreateNewShortLinkDAO() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'ShortLinkMySQLDAO');
    }

    public function testInsert() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://t.co/12');
        $this->assertEqual($result, 1);

        $sql = "SELECT * FROM " . $this->table_prefix . 'links_short';
        $stmt = ShortLinkMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(count($data), 1);
        $data = $data[0];
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['link_id'], 12);
        $this->assertEqual($data['short_url'], 'http://t.co/12');
        $this->assertEqual($data['click_count'], 0);
    }

    public function testGetLinksToUpdate() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://bit.ly/12');
        $result = $dao->insert(11, 'http://bit.ly/11');
        $result = $dao->insert(10, 'http://t.co/10');

        $result = $dao->getLinksToUpdate('http://bit.ly');
        $this->assertIsA($result, 'Array');
        $this->assertEqual(sizeof($result), 2);
    }

    public function testSaveClickCount() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://bit.ly/12');
        $result = $dao->insert(11, 'http://bit.ly/11');
        $result = $dao->insert(10, 'http://t.co/10');

        $result = $dao->saveClickCount('http://bit.ly/12', 100);
        $this->assertEqual($result, 1);
    }
}
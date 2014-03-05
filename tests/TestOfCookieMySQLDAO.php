<?php
/**
 *
 * ThinkUp/tests/TestOfCookieMySQLDAO.php
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
 * Test of CookieMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfCookieMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var CookieMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->DAO = new CookieMySQLDAO();
        $this->builders = self::buildData();
        $this->config = Config::getInstance();
    }

    protected function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('cookies', array('owner_email' => 'me@test.com', 'cookie' => 'chocolate'));
        $builders[] = FixtureBuilder::build('cookies', array('owner_email' => 'me@test.com', 'cookie' => 'gingersnap'));
        $builders[] = FixtureBuilder::build('cookies', array('owner_email' => 'you@test.com', 'cookie' => 'oreo'));

        return $builders;
    }

    public function testGetEmailByCookie() {
        $email = $this->DAO->getEmailByCookie('chocolate');
        $this->assertEqual('me@test.com', $email);
        $email = $this->DAO->getEmailByCookie('gingersnap');
        $this->assertEqual('me@test.com', $email);
        $email = $this->DAO->getEmailByCookie('oreo');
        $this->assertEqual('you@test.com', $email);
        $email = $this->DAO->getEmailByCookie('peanutbutter');
        $this->assertNull($email);
    }

    public function testGenerateForEmail() {
        $cookie = $this->DAO->generateForEmail($em = 'testy@testy.com');
        $this->assertNotNull($cookie);

        $cookie2 = $this->DAO->generateForEmail($em = 'testy@testy.com');
        $this->assertNotNull($cookie2);
        $this->assertNotEqual($cookie, $cookie2);

        $email = $this->DAO->getEmailByCookie($cookie);
        $this->assertEqual($em, $email);

        $email = $this->DAO->getEmailByCookie($cookie2);
        $this->assertEqual($em, $email);
    }

    public function testDeleteByCookie() {
        $cookie = $this->DAO->generateForEmail($em = 'testy@testy.com');
        $this->assertNotNull($cookie);
        $email = $this->DAO->getEmailByCookie($cookie);
        $this->assertEqual($em, $email);

        $this->DAO->deleteByCookie($cookie);
        $email = $this->DAO->getEmailByCookie($cookie);
        $this->assertNull($email);
    }

    public function testDeleteByEmail() {
        $cookie = $this->DAO->generateForEmail($em = 'testy@testy.com');
        $this->assertNotNull($cookie);
        $email = $this->DAO->getEmailByCookie($cookie);
        $this->assertEqual($em, $email);

        $this->DAO->deleteByEmail($em);
        $email = $this->DAO->getEmailByCookie($cookie);
        $this->assertNull($email);
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

}

<?php
/**
 *
 * ThinkUp/tests/TestOfCrawler.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Guillaume Boudreau
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
 * Test Crawler object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/hellothinkup/model/class.HelloThinkUpPlugin.php';

class TestOfCrawler extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test Crawler singleton instantiation
     */
    public function testCrawlerSingleton() {
        $crawler = Crawler::getInstance();
        $this->assertTrue(isset($crawler));
        //clean copy of crawler, no registered plugins, will throw exception
        $this->expectException( new PluginNotFoundException("hellothinkup") );
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");
        //register a plugin
        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");

        //make sure singleton still has those values
        $crawler_two = Crawler::getInstance();
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");
    }

    /**
     * Test Crawler->crawl
     */
    public function testCrawl() {
        $crawler = Crawler::getInstance();

        //        $crawler->registerPlugin('nonexistent', 'TestFauxPluginOne');
        //        $crawler->registerCrawlerPlugin('TestFauxPluginOne');
        //        $this->expectException( new Exception("The TestFauxPluginOne object does not have a crawl method.") );
        //        $crawler->crawl();

        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $crawler->registerCrawlerPlugin('HelloThinkUpPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");

        $builders = $this->buildData();
        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $this->simulateLogin('me@example.com');
        $crawler->crawl();

        Session::logout();
        $this->expectException(new UnauthorizedUserException('You need a valid session to launch the crawler.'));
        $crawler->crawl();
    }

    public function testCrawlUnauthorized() {
        $builders = $this->buildData();
        $crawler = Crawler::getInstance();
        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $crawler->registerCrawlerPlugin('HelloThinkUpPlugin');
        $this->expectException(new UnauthorizedUserException('You need a valid session to launch the crawler.'));
        $crawler->crawl();
    }

    public function testCrawlUpgrading() {
        // up app version
        $config = Config::getInstance();
        $init_db_version = $config->getValue('THINKUP_VERSION');
        $config->setValue('THINKUP_VERSION', $config->getValue('THINKUP_VERSION') + 10); //set a high version num

        $builders = $this->buildData();
        $crawler = Crawler::getInstance();
        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $crawler->registerCrawlerPlugin('HelloThinkUpPlugin');
        $this->simulateLogin('admin@example.com', true);
        $this->expectException(
        new InstallerException('ThinkUp needs a database migration, so we are unable to run the crawler.'));
        $crawler->crawl();
        // reset version
        $config->setValue('THINKUP_VERSION', $init_db_version);
    }

    private function buildData() {
        $admin_owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'admin@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'is_admin' => 1
        ));
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 2,
            'email' => 'me@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1
        ));
        return array($admin_owner_builder, $owner_builder);
    }
}

<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterAPIEndpoint.php
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
 * Test of Twitter API Endpoint
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIEndpoint.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfTwitterAPIEndpoint extends ThinkUpBasicUnitTestCase {
    public function testConstructor() {
        $endpoint = new TwitterAPIEndpoint();
        $this->assertNotNull($endpoint);
        $this->assertIsA($endpoint, 'TwitterAPIEndpoint');
    }

    public function testDecrementNullRemaining() {
        $endpoint = new TwitterAPIEndpoint();
        $this->expectException(Exception);
        $endpoint->decrementRemaining();
    }

    public function testDecrementRemaining() {
        $endpoint = new TwitterAPIEndpoint();
        $endpoint->setRemaining(15);
        $endpoint->decrementRemaining();
        $this->assertEqual(14, $endpoint->getRemaining());
    }

    public function testGetPath() {
        $endpoint = new TwitterAPIEndpoint();
        $this->assertNull($endpoint->getPath());

        $endpoint = new TwitterAPIEndpoint('/test/a/fake/endpoint');
        $this->assertEqual('https://api.twitter.com/1.1/test/a/fake/endpoint.json', $endpoint->getPath());
        $this->assertEqual('/test/a/fake/endpoint', $endpoint->getShortPath());

        $endpoint = new TwitterAPIEndpoint('/test/a/fake/endpoint/with/:id/in/it');
        $this->assertEqual('https://api.twitter.com/1.1/test/a/fake/endpoint/with/ginatrapani/in/it.json',
        $endpoint->getPathWithID('ginatrapani'));
        $this->assertEqual('/test/a/fake/endpoint/with/:id/in/it', $endpoint->getShortPath());
    }

    public function testGetStatus() {
        $endpoint = new TwitterAPIEndpoint();
        $this->assertEqual($endpoint->getStatus(), "API rate limit balance unknown for uninitialized endpoint.");

        $endpoint = new TwitterAPIEndpoint('/test/a/fake/endpoint');
        $this->assertEqual($endpoint->getStatus(), "API rate limit balance unknown for /test/a/fake/endpoint.");

        $endpoint = new TwitterAPIEndpoint('/test/a/fake/endpoint/with/:id/in/it');
        $this->assertEqual($endpoint->getStatus(),
        "API rate limit balance unknown for /test/a/fake/endpoint/with/:id/in/it.");

        $endpoint = new TwitterAPIEndpoint('/test/a/fake/endpoint/with/:id/in/it');
        $endpoint->setRemaining(12);
        $endpoint->setLimit(15);
        $endpoint->setReset(1361069069);
        $this->assertPattern( "/12 calls out of 15 to \/test\/a\/fake\/endpoint\/with\/:id\/in\/it available until/",
        $endpoint->getStatus());
    }

    public function testIsAvailable() {
        $endpoint = new TwitterAPIEndpoint('/test/faker');
        $endpoint->setRemaining(78);
        $endpoint->setLimit(100);
        $this->assertFalse($endpoint->isAvailable(80));

        $endpoint->setRemaining(81);
        $endpoint->setLimit(100);
        $this->assertTrue($endpoint->isAvailable(80));

        $endpoint->setRemaining(180);
        $endpoint->setLimit(180);
        $this->assertTrue($endpoint->isAvailable(80));
    }
}

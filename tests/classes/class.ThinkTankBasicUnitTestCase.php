<?php
/**
 * ThinkTank Basic Unit Test Case
 *
 * Test case for tests without the need for database availability.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkTankBasicUnitTestCase extends UnitTestCase {
    function setUp() {
        parent::setUp();
    }

    function tearDown() {
        Config::destroyInstance();
        if (isset($_SESSION['user'])) {
            $_SESSION['user']=null;
        }
        parent::setUp();
    }
}

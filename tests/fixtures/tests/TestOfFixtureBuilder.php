<?php
/**
 *
 * ThinkUp/tests/fixtures/tests/TestOfFixtureBuilder.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 */

require_once 'tests/init.tests.php';
require_once 'webapp/_lib/class.Loader.php';
require_once 'webapp/_lib/extlib/simpletest/autorun.php';
require_once 'webapp/config.inc.php';
require_once 'tests/config.tests.inc.php';
require_once 'webapp/_lib/class.Config.php';
require_once 'tests/fixtures/class.FixtureBuilder.php';

class TestOfixtureBuilder extends UnitTestCase {

    const TEST_TABLE = 'thinkup_tests';

    public function setUp() {
        global $TEST_DATABASE;
        $this->config = Config::getInstance();
        $this->config->setValue('db_name', $TEST_DATABASE);
        if ($this->config->getValue('timezone')) {
            date_default_timezone_set($this->config->getValue('timezone'));
        }

        //add prefix to the test table
        $this->test_table = Config::getInstance()->getValue('table_prefix') . self::TEST_TABLE;

        // build test table
        $this->builder =  new FixtureBuilder();
        $this->pdo = FixtureBuilder::$pdo;
        $this->pdo->query('CREATE TABLE ' . $this->test_table . '(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' .
            'test_name varchar(20),' .
            'test_city varchar(20) not null default "",' .
            'test_id int(11),' .
            "fav_color enum('red', 'blue', 'green')," .
            "fav_food enum('apple''s', 'hotdog', 'roll') not null default 'roll' ," .
            'unique key test_id_idx (test_id),' .
            'date_created timestamp default CURRENT_TIMESTAMP,' .
            'date_updated datetime,' .
            'birthday date,' .
            'numeric_ip_address int default 2015153756,' .
            'a_point point,' .
            'a_polygon polygon,' .
            'worth decimal(11,2)  default 12.99,' .
            'a_float float(11,2)  default 11.33' .
            ')');
    }

    public function tearDown() {
        $this->pdo->query('drop table ' . $this->test_table);
    }

    public function testBuildData() {
        $builder = FixtureBuilder::build(self::TEST_TABLE, array('test_id' => 1), true );
        // auto inc id
        $this->assertEqual(1, $builder->columns['last_insert_id'], 'our id is 1');

        // test_name is a string?
        $this->assertTrue(is_string($builder->columns['test_name']), 'we have a name string');

        // test_city is a string?
        $this->assertTrue(is_string($builder->columns['test_city']), 'we have a city string');
        $this->assertTrue(strlen($builder->columns['test_city']) > 0, 'we have a city string');

        // test_id is an int?
        $this->assertEqual(1, $builder->columns['test_id'], 'we have a test_id');

        // a_float is a float?
        $this->assertPattern('/[11][.][0-9][0-9]/', $builder->columns['a_float'], 'we have a float');

        // test fav_food enum
        $enum_array = array('red', 'blue', 'green');
        $this->assertTrue($this->_testEnum($enum_array, $builder->columns['fav_color']), 'we have a valid enum value '.
        $builder->columns['fav_color']);

        // test fav_food enum
        $enum_array = array("apple''s", 'hotdog', 'roll');
        $this->assertEqual($builder->columns['fav_food'], 'roll', 'we have a default enum value: roll');

        // test point gen
        $this->assertPattern("/GeometryFromText\('Point\(\d+ \d+\)'\)/",$builder->columns['a_point']);
        $this->assertPattern("/PolygonFromText\('Polygon\(\d+ \d+ \d+\)'\)/",$builder->columns['a_polygon']);

        $builder2 = FixtureBuilder::build(self::TEST_TABLE, array('test_id' => 2, 'fav_food' => 'hotdog'), true );
        // auto inc id
        $this->assertEqual(2, $builder2->columns['last_insert_id'], 'our id is 2');

        // test_name is a string?
        $this->assertTrue(is_string($builder2->columns['test_name']), 'we have a name string');

        // test_id is an int?
        $this->assertEqual(2, $builder2->columns['test_id'], 'we have a test_id');

        // test fav_color enum
        $enum_array = array('red', 'blue', 'green');
        $this->assertTrue($this->_testEnum($enum_array, $builder2->columns['fav_color']), 'we have a valid enum value '.
        $builder->columns['fav_color']);

        // test fav_food enum
        $this->assertEqual($builder2->columns['fav_food'], 'hotdog', 'we have a enum value: hotdog');

        //test date fields
        $date_time = new DateTime($builder2->columns['date_created']);
        $this->assertTrue(is_a( $date_time , 'DateTime'), 'we have a date');
        $date_time = new DateTime($builder2->columns['date_updated']);
        $this->assertTrue(is_a( $date_time , 'DateTime'), 'we have a date');
        $date_time = new DateTime($builder2->columns['birthday']);
        $this->assertTrue(is_a( $date_time , 'DateTime'), 'we have a date');

        // set dates
        $date_fixture_data = array('test_id' => 3, 'date_created' => '+1d', 'birthday' => '1978-06-20');
        $builder3 = FixtureBuilder::build(self::TEST_TABLE, $date_fixture_data);
        $mysql_date = strtotime( $builder3->columns['date_created'] );
        $match_date = time() + (60 * 60 * 24);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');
        $this->assertEqual('1978-06-20', $builder3->columns['birthday'], 'birthday set properly');
        $this->assertEqual('12.99', $builder3->columns['worth'], 'worth 12.99');
        $stmt = $this->pdo->query( 'select * from ' . $this->test_table . ' where id = 3');
        $data = $stmt->fetch();
        $this->assertEqual('1978-06-20', $data['birthday'], 'birthday set properly');
        $this->assertEqual('12.99', $data['worth'], 'worth 12.99');

        // mysql functions
        $date_fixture_data = array('test_id' => 4,
        'numeric_ip_address' =>  array("INET_ATON('127.0.0.1')") );
        try {
            $builder3a = FixtureBuilder::build(self::TEST_TABLE, $date_fixture_data);
            $this->fail("should throw exception");
        } catch(FixtureBuilderException $fbe) {
            $this->pass("caught FixtureBuilderException");
        }

        $date_fixture_data = array('test_id' => 4,
        'numeric_ip_address' =>  array("function" => "INET_ATON('127.0.0.1')"));
        $builder4 = FixtureBuilder::build(self::TEST_TABLE, $date_fixture_data);
        $stmt = $this->pdo->query( 'select * from ' . $this->test_table . ' where id = 4');
        $data = $stmt->fetch();
        $this->assertEqual(2130706433, $data['numeric_ip_address']);

        //set points
        $date_fixture_data = array('test_id' => 5, 'a_point' => "GeometryFromText('Point(27.1 20.2)')");
        $builder5 = FixtureBuilder::build(self::TEST_TABLE, $date_fixture_data);
        $mysql_date = strtotime( $builder3->columns['date_created'] );
        $match_date = time() + (60 * 60 * 24);
        $stmt = $this->pdo->query( 'select t.*, AsText(a_point) as text_point from ' . $this->test_table .
        ' as t where id = 5');
        $data = $stmt->fetch();
        $this->assertEqual('POINT(27.1 20.2)', $data['text_point']);

        //set polygon
        $date_fixture_data = array('test_id' => 6, 'a_polygon' =>
        "PolygonFromText( 'Polygon((-0.213503 51.512805,-0.105303 51.512805,-0.105303 51.572068,-0.213503 51.572068, ".
        "-0.213503 51.512805))')");
        $builder6 = FixtureBuilder::build(self::TEST_TABLE, $date_fixture_data);
        $mysql_date = strtotime( $builder3->columns['date_created'] );
        $match_date = time() + (60 * 60 * 24);
        $stmt = $this->pdo->query( 'select t.*, AsText(a_polygon) as text_polygon from ' . $this->test_table .
        ' as t where id = 6');
        $data = $stmt->fetch();
        $this->assertEqual('POLYGON((-0.213503 51.512805,-0.105303 51.512805,-0.105303 51.572068,-0.213503 51.572068,'.
        '-0.213503 51.512805))', $data['text_polygon']);
    }

    public function testDestroyData() {
        $builder = FixtureBuilder::build(self::TEST_TABLE, array('test_id' => 1) );
        $stmt = $this->pdo->query( "select count(*) as count from " . $this->test_table );
        $data = $stmt->fetch();
        $this->assertEqual(1, $data['count'], 'we have one row');

        $builder = null;
        // builder is now out of scope, so _destruct should have deleted our data
        $stmt = $this->pdo->query( "select count(*) as count from " . $this->test_table );
        $data = $stmt->fetch();
        $this->assertEqual(0, $data['count'], 'we have no rows');
    }


    public function testTruncateTable() {
        // bad table name
        try {
            FixtureBuilder::truncateTable('notable');
            $this->fail("should throw FixtureBuilderException");
        } catch(FixtureBuilderException $e) {
            $this->assertPattern('/Unable to truncate table "tu_notable"/', $e->getMessage());
        }
        //add a row, query it, and count should be one
        $this->pdo->query( sprintf("insert into %s (test_name, test_id) values ('mary', 1)", $this->test_table) );
        $stmt = $this->pdo->query( "select count(*) as count from " . $this->test_table );
        $data = $stmt->fetch();
        $this->assertEqual(1, $data['count'], 'we have one row');

        //truncate row, and count should be 0
        FixtureBuilder::truncateTable(self::TEST_TABLE);
        $stmt = $this->pdo->query( "select count(*) as count from " . $this->test_table );
        $data = $stmt->fetch();
        $this->assertEqual(0, $data['count'], 'we have a truncated table');
    }


    public function testDescribeTable() {
        try {
            $this->builder->describeTable('notable');
        } catch(FixtureBuilderException $e) {
            $this->assertPattern('/Unable to describe table "tu_notable"/', $e->getMessage());
        }
        $columns = $this->builder->describeTable(self::TEST_TABLE);
        $this->assertEqual(count($columns), 14, 'column count valid');
    }


    public function testGendata() {

        // test enum
        $enum_array = array("apple''s",'hotdog','roll');
        $value = $this->builder->genEnum( "enum('apple''s','hotdog','roll')");
        $this->assertTrue($this->_testEnum($enum_array, $value), 'we have a valid enum value ' . $value);

        //test int gen
        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genInt();
            if ( $int > $this->builder->DATA_DEFAULTS['int'] ) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genInt()"); }

        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genInt(2);
            if ( $int > 2 ) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genInt(2) $fail"); }

        //test
        //  bigint gen
        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genBigInt();
            if ( $int > $this->builder->DATA_DEFAULTS['bigint']) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genBigInt() $fail"); }

        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genBigInt(3);
            if ( $int > 3) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genBigInt(3) $fail"); }

        //test tiny int gen
        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genTinyInt();
            if ( $int > $this->builder->DATA_DEFAULTS['tinyint']) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genTinyInt() $fail"); }

        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $int = $this->builder->genTinyInt(3);
            if ( $int > 3) { $fail++; }
        }
        if ($fail > 0) { $this->fail("failed genTinyInt(3) $fail"); }


        //test varchars
        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $text = $this->builder->genVarchar();
            if (strlen($text) > $this->builder->DATA_DEFAULTS['varchar']) {
                $fail++;
            }
        }
        $fail = 0;
        for($i = 0; $i < 1000; $i++) {
            $text = $this->builder->genVarchar(2);
            if (strlen($text) > 2) {
                $fail++;
            }
        }
        if ($fail > 0) { $this->fail("failed $fail genVarchar(2) tests"); }

        // test dates  3 days
        $date_text = $this->builder->genDate('+3d');
        $mysql_date = strtotime( $date_text );
        $match_date = time() + (3 * 60 * 60 * 24);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');
        // test dates  -3 days
        $date_text = $this->builder->genDate('-3d');
        $mysql_date = strtotime( $date_text );
        $match_date = time() - (3 * 60 * 60 * 24);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');

        // test dates  1 hour
        $date_text = $this->builder->genDate('+1h');
        $mysql_date = strtotime( $date_text );
        $match_date = time() + (60 * 60);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');
        // test dates  -1 hour
        $date_text = $this->builder->genDate('-1h');
        $mysql_date = strtotime( $date_text );
        $match_date = time() - (60 * 60);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');

        // test dates  17 minutes
        $date_text = $this->builder->genDate('+17m');
        $mysql_date = strtotime( $date_text );
        $match_date = time() + (17 * 60);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');
        // test dates  -342minutes
        $date_text = $this->builder->genDate('-346m');
        $mysql_date = strtotime( $date_text );
        $match_date = time() - (346 * 60);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');

        // test dates  4 seconds
        $date_text = $this->builder->genDate('+4s');
        $mysql_date = strtotime( $date_text );
        $match_date = time() + (4);
        $this->assertTrue($this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 4 seconds');
        // test dates  -766minutes
        $date_text = $this->builder->genDate('-766m');
        $mysql_date = strtotime( $date_text );
        $match_date = time() - (766 * 60);
        $this->assertTrue(  $this->_testDatesAreClose($mysql_date, $match_date), 'dates are within 2 seconds');

        // test date string passed in value
        $this->assertEqual($this->builder->genDate('2010-06-20 16:22:25'), '2010-06-20 16:22:25', 'date matches');

        // test genDecimal
        $fail = null;
        for($i = 0; $i < 1000; $i++) {
            $dec = $this->builder->genDecimal('decimal(3,2)');
            $values = preg_split('/\./', $dec);
            if ($values[0] >= 1000) { $fail =  "left value is not less than 1000 - " . $values[0]; break;}
            if ($values[1] >= 100) { $fail = "right value is not less than 100 - " . $values[1]; break; }
        }
        if ($fail) {
            $this->fail($fail);
        }

        // test genPoint
        $fail = null;
        for($i = 0; $i < 1000; $i++) {
            $point = $this->builder->genPoint();
            $values = preg_split('/\./', $point);
            $matches = null;
            preg_match("/\((\d+) (\d+)\)/", $subject, $matches, PREG_OFFSET_CAPTURE, 3);
            if ($values[0] > 0 && $values[0] < 101) { $fail =  "left value is not correct - " . $values[0]; break;}
            if ($values[1] > 0 && $value[1] < 101) { $fail = "right value is not correct - " . $values[1]; break; }
        }
        if ($fail) {
            $this->fail($fail);
        }

        // test genPolygon
        $fail = null;
        for($i = 0; $i < 1000; $i++) {
            $polygon = $this->builder->genPolygon();
            $values = preg_split('/\./', $polygon);
            $matches = null;
            preg_match("/\((\d+) (\d+)\)/", $subject, $matches, PREG_OFFSET_CAPTURE, 3);
            if ($values[0] > 0 && $values[0] < 101) { $fail =  "left value is not correct - " . $values[0]; break;}
            if ($values[1] > 0 && $value[1] < 101) { $fail = "right value is not correct - " . $values[1]; break; }
        }
        if ($fail) {
            $this->fail($fail);
        }
    }

    public function _testDatesAreClose($date1, $date2) {
        $date_diff = $date1 - $date2;
        return ($date_diff < 2 && $date_diff > - 2);
    }

    public function _testEnum($enum_array, $value) {
        $pass = false;
        for($i = 0; $i < count($enum_array); $i++) {
            if ( $value == $enum_array[$i] ) { $pass = true; }
        }
        return $pass;
    }
}

<?php
/**
 *
 * ThinkUp/tests/classes/class.TestFauxDAO.php
 *
 * Copyright (c) 2009-2013 Christoffer Viken, Gina Trapani, Mark Wilkie
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
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Christoffer Viken, Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__) . '/interface.TestDAO.php';

/*
 * Faux TestDAO implementation for TestOfDAOFactory test
 */
class TestFauxDAO implements TestDAO {

    // test select query using $stmt directly...
    public function getUserCount($min_id, $user_name) {
    }

    // test insert and parent getInsertCount()
    public function insertDataGetCount($name, $id) {
    }

    // test insert and parent getInsertId()
    public function insertDataGetId($name, $id) {
    }

    // test multi insert and parent getInsertCount()
    public function insertMultiDataGetCount($insert_data) {
    }

    // test bad sql
    public function badSql() {
    }

    // test too-lengthy content
    public function insertTooLongContent() {
    }

    // test bad binds...
    public function badBinds() {
    }

    // test update test_name record
    public function update($name, $id) {
    }

    // test update test_name record(s)
    public function updateMulti($name, $id) {
    }

    // test select one record
    public function selectRecord($id) {
        $test_data = new TestData();
        $test_data->test_name = 'Mojo Jojo';
        $test_data->test_id = '2001';
        return $test_data;
    }

    //select one record array
    public function selectRecordAsArray($id) {
    }

    // test select many records
    public function selectRecords($id) {
    }

    public function selectRecordsWithLimit($limit) {
    }

    // test select many records as array
    public function selectRecordsAsArrays($id) {
    }

    // test delete records
    public function delete($id) {
    }

    // test record exist
    public function isExisting($id) {
    }

    //test BoolToDB
    public function testBoolToDB($val) {
    }
}

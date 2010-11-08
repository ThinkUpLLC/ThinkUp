<?php
/**
 *
 * ThinkUp/tests/classes/interface.TestDAO.php
 *
 * Copyright (c) 2009-2010 Christoffer Viken, Mark Wilkie
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
 *
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Christoffer Viken, Mark Wilkie
 */

class TestData {
    var $id;
    var $test_name;
    var $test_id;
}

interface TestDAO {

    // test select query using $stmt directly...
    public function getUserCount($min_id, $user_name);

    // test insert and parent getInsertCount()
    public function insertDataGetCount($name, $id);

    // test insert and parent getInsertId()
    public function insertDataGetId($name, $id);

    // test multi insert and parent getInsertCount()
    public function insertMultiDataGetCount($insert_data);

    // test bad sql
    public function badSql();

    // test bad binds...
    public function badBinds();

    // test update test_name record
    public function update($name, $id);

    // test update test_name record(s)
    public function updateMulti($name, $id);

    // test select one record
    public function selectRecord($id);

    //select one record array
    public function selectRecordAsArray($id);

    // test select many records
    public function selectRecords($id);

    // test select many records wth limit
    public function selectRecordsWithLimit($limit);

    // test select many records as array
    public function selectRecordsAsArrays($id);

    // test delete records
    public function delete($id);

    // test record exist
    public function isExisting($id);

    //test BoolToDB
    public function testBoolToDB($val);

}
?>
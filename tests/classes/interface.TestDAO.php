<?php

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
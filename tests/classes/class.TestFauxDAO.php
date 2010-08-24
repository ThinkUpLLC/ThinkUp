<?php
require_once 'tests/classes/interface.TestDAO.php';

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

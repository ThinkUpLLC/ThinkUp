<?php
/**
* DAOFactory
* 
* inits a dao based on the think tank config db_type and $dao_mapping definitions.

* db_type is defined in webapp/config.inc.php as:
* 
*     $THINKTANK_CFG['db_type'] = 'somedb';
* 
* Example: DAOFactory::getDAO('SomeDAO');
* 
* @author Mark Wilkie
*/
class DAOFactory {

    /**
     * maps DAO from db_type and defines class names and path for initialization
     */
    static $dao_mapping = array (
        // our test dao
        'TestDAO' => array( 
            'mysql' => array( 'class' => 'TestMysqlDAO', 'path' =>  'tests/classes/class.TestMysqlDAO.php'),
            'faux' => array( 'class' => 'TestFauxDAO', 'path' =>  'tests/classes/class.TestFauxDAO.php'),
        )
    );

    /*
     *  Creates a DAO instance and returns it
     *
     * @param string - the name of the dao you wish to init
     * @returns object - a concrete dao instance
     */
    public static function getDAO($dao_key) {
        $db_type = self::getDBType();
        if(! isset(self::$dao_mapping[$dao_key]) ) {
            throw new Exception("No DAO mapping defined for: " . $dao_key);
        }
        if(! isset(self::$dao_mapping[$dao_key][$db_type])) {
            throw new Exception("No db mapping defined for '" . $dao_key . "' with db type: " . $db_type);
        }
        $class_info = self::$dao_mapping[$dao_key][$db_type];
        require_once($class_info['path']);
        $dao = new $class_info['class'];
        return $dao;
    }

    /**
     * gets the db_type for our configured think tank instance, defaults to mysql,
     * db_type can optionally be defined in webapp/config.inc.php as:
     * 
     *     $THINKTANK_CFG['db_type'] = 'somedb';
     * 
     * @return string db_type, will default to 'mysql' if not defined
     */
    public static function getDBType() {
        $type = Config::getInstance()->getValue('db_type');
        $type = is_null($type) ? 'mysql' : $type;
        return $type;
    }
}
?>
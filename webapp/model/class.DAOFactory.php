<?php
/**
 * Data Access Object Factory
 *
 * Inits a DAO based on the ThinkTank config db_type and $dao_mapping definitions.

 * db_type is defined in webapp/config.inc.php as:
 *
 *     $THINKTANK_CFG['db_type'] = 'somedb';
 *
 * Example of use:
 *
 * <code>
 *  DAOFactory::getDAO('SomeDAO');
 * </code> *
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
    ),
    //Instance MySQL DAO
        'InstanceDAO' => array(
            'mysql' => array( 'class' => 'InstanceMySQLDAO', 'path' => 'model/class.InstanceMySQLDAO.php')
    ),
    //Follow MySQL DAO
        'FollowDAO' => array(
            'mysql' => array( 'class' => 'FollowMySQLDAO', 'path' => 'model/class.InstanceMySQLDAO.php')
    ),
    //Post Error MySQL DAO
        'PostErrorDAO' => array(
            'mysql' => array( 'class' => 'PostErrorMySQLDAO', 'path' => 'model/class.PostErrorMySQLDAO.php')
    ),
    //Post MySQL DAO
        'PostDAO' => array(
            'mysql' => array( 'class' => 'PostMySQLDAO', 'path' => 'model/class.PostMySQLDAO.php')
    ),
    //User MySQL DAO
        'UserDAO' => array(
            'mysql' => array( 'class' => 'UserMySQLDAO', 'path' => 'model/class.UserMySQLDAO.php')
    ),
    //UserError MySQL DAO
        'UserErrorDAO' => array(
            'mysql' => array( 'class' => 'UserErrorMySQLDAO', 'path' => 'model/class.UserErrorMySQLDAO.php')
    )
    );

    /*
     *  Creates a DAO instance and returns it
     *
     * @param string $dao_key the name of the dao you wish to init
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
     * gets the db_type for our configured ThinkTank instance, defaults to mysql,
     * db_type can optionally be defined in webapp/config.inc.php as:
     *
     *<code>
     *     $THINKTANK_CFG['db_type'] = 'somedb';
     *</code>
     *
     * @return string db_type, will default to 'mysql' if not defined
     */
    public static function getDBType() {
        $type = Config::getInstance()->getValue('db_type');
        $type = is_null($type) ? 'mysql' : $type;
        return $type;
    }
}
<?php
/**
 * Data Access Object Factory
 *
 * Inits a DAO based on the ThinkUp config db_type and $dao_mapping definitions.
 * db_type is defined in webapp/config.inc.php as:
 *
 *     $THINKUP_CFG['db_type'] = 'somedb';
 *
 * Example of use:
 *
 * <code>
 *  DAOFactory::getDAO('SomeDAO');
 * </code>
 *
 * @author Mark Wilkie
 */
class DAOFactory {

    /**
     * maps DAO from db_type and defines class names and path for initialization
     */
    static $dao_mapping = array (
        //Test DAO
        'TestDAO' => array( 
            //MySQL Version
            'mysql' => array( 'class' => 'TestMysqlDAO', 'path' =>  'tests/classes/class.TestMysqlDAO.php'),
            //faux Version
            'faux' => array( 'class' => 'TestFauxDAO', 'path' =>  'tests/classes/class.TestFauxDAO.php'),
        ),
        //Instance DAO
        'InstanceDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'InstanceMySQLDAO', 'path' => 'model/class.InstanceMySQLDAO.php')
        ),
        //Follow DAO
        'FollowDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'FollowMySQLDAO', 'path' => 'model/class.FollowMySQLDAO.php')
        ),
        //Post Error DAO
        'PostErrorDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'PostErrorMySQLDAO', 'path' => 'model/class.PostErrorMySQLDAO.php')
        ),
        //Post DAO
        'PostDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'PostMySQLDAO', 'path' => 'model/class.PostMySQLDAO.php')
        ),
        //User DAO
        'UserDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'UserMySQLDAO', 'path' => 'model/class.UserMySQLDAO.php')
        ),
        //UserError DAO
        'UserErrorDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'UserErrorMySQLDAO', 'path' => 'model/class.UserErrorMySQLDAO.php')
        ),
        //Link DAO
        'LinkDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'LinkMySQLDAO', 'path' => 'model/class.LinkMySQLDAO.php')
        ),
        //Owner MySQL DAO
        'OwnerDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'OwnerMySQLDAO', 'path' => 'model/class.OwnerMySQLDAO.php')
        ),
        //OwnerInstance MySQL DAO
        'OwnerInstanceDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'OwnerInstanceMySQLDAO', 'path' => 'model/class.OwnerInstanceMySQLDAO.php')
        ),
        //Plugin MySQL DAO
        'PluginDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'PluginMySQLDAO', 'path' => 'model/class.PluginMySQLDAO.php')
        ),
        //Plugin Option MySQL DAO
        'PluginOptionDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'PluginOptionMySQLDAO', 'path' => 'model/class.PluginOptionMySQLDAO.php')
        ),        
        //Follower Count MySQL DAO
        'FollowerCountDAO' => array(
            //MySQL Version
            'mysql' => array( 'class' => 'FollowerCountMySQLDAO', 'path' => 'model/class.FollowerCountMySQLDAO.php')
        )
    );

    /*
     * Creates a DAO instance and returns it
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
     * Gets the db_type for our configured ThinkUp instance, defaults to mysql,
     * db_type can optionally be defined in webapp/config.inc.php as:
     *
     *<code>
     *     $THINKUP_CFG['db_type'] = 'somedb';
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
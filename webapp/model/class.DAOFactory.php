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
            'mysql' => array( 'class' => 'TestMysqlDAO', 'path' =>  'TestMysqlDAO'),
    //faux Version
            'faux' => array( 'class' => 'TestFauxDAO', 'path' =>  'TestFauxDAO'),
    ),
    //Instance DAO
        'InstanceDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'InstanceMySQLDAO', 'path' => 'InstanceMySQLDAO')
    ),
    //Follow DAO
        'FollowDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'FollowMySQLDAO', 'path' => 'FollowMySQLDAO')
    ),
    //Post Error DAO
        'PostErrorDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'PostErrorMySQLDAO', 'path' => 'PostErrorMySQLDAO')
    ),
    //Post DAO
        'PostDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'PostMySQLDAO', 'path' => 'PostMySQLDAO')
    ),
    //User DAO
        'UserDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'UserMySQLDAO', 'path' => 'UserMySQLDAO')
    ),
    //UserError DAO
        'UserErrorDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'UserErrorMySQLDAO', 'path' => 'UserErrorMySQLDAO')
    ),
    //Location DAO
        'LocationDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'LocationMySQLDAO', 'path' => 'LocationMySQLDAO')
    ),
    //Link DAO
        'LinkDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'LinkMySQLDAO', 'path' => 'LinkMySQLDAO')
    ),
    //Owner MySQL DAO
        'OwnerDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'OwnerMySQLDAO', 'path' => 'OwnerMySQLDAO')
    ),
    //OwnerInstance MySQL DAO
        'OwnerInstanceDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'OwnerInstanceMySQLDAO', 'path' => 'OwnerInstanceMySQLDAO')
    ),
    //Plugin MySQL DAO
        'PluginDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'PluginMySQLDAO', 'path' => 'PluginMySQLDAO')
    ),
    //Plugin Option MySQL DAO
        'PluginOptionDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'PluginOptionMySQLDAO', 'path' => 'PluginOptionMySQLDAO')
    ),
    //Follower Count MySQL DAO
        'FollowerCountDAO' => array(
    //MySQL Version
            'mysql' => array( 'class' => 'FollowerCountMySQLDAO', 'path' => 'FollowerCountMySQLDAO')
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
        //require_once(THINKUP_WEBAPP_PATH.$class_info['path']);
        $dao = new $class_info['path'];
        return $dao;
    }

    /**
     * Gets the db_type for our configured ThinkUp instance, defaults to mysql,
     * db_type can optionally be defined in webapp/config.inc as:
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
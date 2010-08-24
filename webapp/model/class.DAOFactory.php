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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class DAOFactory {

    /**
     * maps DAO from db_type and defines interface names and class implementation
     */
    static $dao_mapping = array (
    //Test DAO
        'TestDAO' => array( 
    //MySQL Version
            'mysql' => 'TestMysqlDAO',
    //faux Version
            'faux' => 'TestFauxDAO' ),
    //Instance DAO
        'InstanceDAO' => array(
    //MySQL Version
            'mysql' => 'InstanceMySQLDAO' ),
    //Follow DAO
        'FollowDAO' => array(
    //MySQL Version
            'mysql' => 'FollowMySQLDAO' ),
    //Post Error DAO
        'PostErrorDAO' => array(
    //MySQL Version
            'mysql' => 'PostErrorMySQLDAO' ),
    //Post DAO
        'PostDAO' => array(
    //MySQL Version
            'mysql' => 'PostMySQLDAO' ),
    //User DAO
        'UserDAO' => array(
    //MySQL Version
            'mysql' => 'UserMySQLDAO' ),
    //UserError DAO
        'UserErrorDAO' => array(
    //MySQL Version
            'mysql' => 'UserErrorMySQLDAO' ),
    //Location DAO
        'LocationDAO' => array(
    //MySQL Version
            'mysql' => 'LocationMySQLDAO' ),
    //Link DAO
        'LinkDAO' => array(
    //MySQL Version
            'mysql' => 'LinkMySQLDAO' ),
    //Owner MySQL DAO
        'OwnerDAO' => array(
    //MySQL Version
            'mysql' => 'OwnerMySQLDAO' ),
    //OwnerInstance MySQL DAO
        'OwnerInstanceDAO' => array(
    //MySQL Version
            'mysql' => 'OwnerInstanceMySQLDAO' ),
    //Plugin MySQL DAO
        'PluginDAO' => array(
    //MySQL Version
            'mysql' => 'PluginMySQLDAO' ),
    //Plugin Option MySQL DAO
        'PluginOptionDAO' => array(
    //MySQL Version
            'mysql' => 'PluginOptionMySQLDAO' ),
    //Follower Count MySQL DAO
        'FollowerCountDAO' => array(
    //MySQL Version
            'mysql' => 'FollowerCountMySQLDAO'),
    //Installer MySQL DAO
        'InstallerDAO' => array (
    //MySQL Version
            'mysql' => 'InstallerMySQLDAO')
    );

    /*
     * Creates a DAO instance and returns it
     *
     * @param string $dao_key the name of the dao you wish to init
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'GMT_offset', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @returns PDODAO A concrete dao instance
     */
    public static function getDAO($dao_key, $cfg_vals=null) {
        $db_type = self::getDBType($cfg_vals);
        if(! isset(self::$dao_mapping[$dao_key]) ) {
            throw new Exception("No DAO mapping defined for: " . $dao_key);
        }
        if(! isset(self::$dao_mapping[$dao_key][$db_type])) {
            throw new Exception("No db mapping defined for '" . $dao_key . "' with db type: " . $db_type);
        }
        $class_name = self::$dao_mapping[$dao_key][$db_type];
        $dao = new $class_name($cfg_vals);
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
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'GMT_offset', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @return string db_type, will default to 'mysql' if not defined
     */
    public static function getDBType($cfg_vals=null) {
        $type = Config::getInstance($cfg_vals)->getValue('db_type');
        $type = is_null($type) ? 'mysql' : $type;
        return $type;
    }
}
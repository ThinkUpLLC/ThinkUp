<?php
/**
 *
 * ThinkUp/webapp/_lib/class.DAOFactory.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class DAOFactory {

    /**
     * Maps DAO from db_type and defines interface names and class implementation
     */
    static $dao_mapping = array (
    //Test DAO
        'TestDAO' => array(
    //MySQL Version
            'mysql' => 'TestMySQLDAO',
    //faux Version
            'faux' => 'TestFauxDAO' ),
    //Instance DAO
        'InstanceDAO' => array(
    //MySQL Version
            'mysql' => 'InstanceMySQLDAO' ),
    //@TODO Figure out a way to let a plugin define its DAOs in the plugin code
    //Twitter Instance DAO
        'TwitterInstanceDAO' => array(
    //MySQL Version
            'mysql' => 'TwitterInstanceMySQLDAO' ),
    //Facebook Instance DAO
        'FacebookInstanceDAO' => array(
    //MySQL Version
            'mysql' => 'FacebookInstanceMySQLDAO' ),
    //Invite DAO
        'InviteDAO' => array(
    //MySQL Version
            'mysql' => 'InviteMySQLDAO' ),
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
    //Export DAO
        'ExportDAO' => array(
    //MySQL Version
            'mysql' => 'ExportMySQLDAO' ),
    //FavoritePost DAO
        'FavoritePostDAO' => array(
    //MySQL Version
            'mysql' => 'FavoritePostMySQLDAO' ),
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
    //Hashtag DAO
        'HashtagDAO' => array(
    //MySQL Version
            'mysql' => 'HashtagMySQLDAO' ),
    //Mention DAO
        'MentionDAO' => array(
    //MySQL Version
            'mysql' => 'MentionMySQLDAO' ),
    //Place DAO
        'PlaceDAO' => array(
    //MySQL Version
            'mysql' => 'PlaceMySQLDAO' ),
    //StreamData DAO
        'StreamDataDAO' => array(
    //MySQL Version
            'mysql' => 'StreamDataMySQLDAO' ),
    //StreamProc DAO
        'StreamProcDAO' => array(
    //MySQL Version
            'mysql' => 'StreamProcMySQLDAO' ),
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
    //Count History MySQL DAO
        'CountHistoryDAO' => array(
    //MySQL Version
            'mysql' => 'CountHistoryMySQLDAO'),
    //Installer MySQL DAO
        'InstallerDAO' => array (
    //MySQL Version
            'mysql' => 'InstallerMySQLDAO'),
    //Option MySQL DAO
        'OptionDAO' => array (
    //MySQL Version
            'mysql' => 'OptionMySQLDAO'),
    //Backup MySQL DAO
        'BackupDAO' => array (
    //MySQL Version
            'mysql' => 'BackupMySQLDAO'),
    //Mutex MySQL DAO
        'MutexDAO' => array (
    //MySQL Version
            'mysql' => 'MutexMySQLDAO'),
    //Group MySQL DAO
        'GroupDAO' => array (
    //MySQL Version
            'mysql' => 'GroupMySQLDAO'),
    //Group Member MySQL DAO
        'GroupMemberDAO' => array (
    //MySQL Version
            'mysql' => 'GroupMemberMySQLDAO'),
    //Group Owner MySQL DAO
        'GroupOwnerDAO' => array (
    //MySQL Version
            'mysql' => 'GroupOwnerMySQLDAO'),
    //TableStats MySQL DAO
        'TableStatsDAO' => array (
    //MySQL Version
            'mysql' => 'TableStatsMySQLDAO'),
    //ShortLink MySQL DAO
        'ShortLinkDAO' => array (
    //MySQL Version
            'mysql' => 'ShortLinkMySQLDAO'),
    //Insight MySQL DAO
        'InsightBaselineDAO' => array (
    //MySQL Version
            'mysql' => 'InsightBaselineMySQLDAO'),
        'InsightDAO' => array (
    //MySQL Version
            'mysql' => 'InsightMySQLDAO'),
    //Instance Hashtag MySQL DAO
        'InstanceHashtagDAO' => array(
    //MySQL Version
            'mysql' => 'InstanceHashtagMySQLDAO' ),
    //Hashtag Post DAO
        'HashtagPostDAO' => array(
    //MySQL Version
            'mysql' => 'HashtagPostMySQLDAO' ),
    //Video DAO
        'VideoDAO' => array(
    //MySQL Version
            'mysql' => 'VideoMySQLDAO' ),
    //Photo DAO
        'PhotoDAO' => array(
    //MySQL Version
            'mysql' => 'PhotoMySQLDAO' ),
        'SessionDAO' => array(
    //MySQL Version
            'mysql' => 'SessionMySQLDAO' ),
        'CookieDAO' => array(
    //MySQL Version
            'mysql' => 'CookieMySQLDAO' )

    );

    /*
     * Creates a DAO instance and returns it
     *
     * @param string $dao_key the name of the dao you wish to init
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @returns PDODAO A concrete dao instance
     */
    public static function getDAO($dao_key, $cfg_vals=null) {
        $db_type = self::getDBType($cfg_vals);
        if (!isset(self::$dao_mapping[$dao_key]) ) {
            throw new Exception("No DAO mapping defined for: " . $dao_key);
        }
        if (!isset(self::$dao_mapping[$dao_key][$db_type])) {
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
     * @param array $cfg_vals Optionally override config.inc.php vals; needs 'table_prefix', 'db_type',
     * 'db_socket', 'db_name', 'db_host', 'db_user', 'db_password'
     * @return string db_type, will default to 'mysql' if not defined
     */
    public static function getDBType($cfg_vals=null) {
        if ($cfg_vals != null) {
            Config::destroyInstance();
        }
        $type = Config::getInstance($cfg_vals)->getValue('db_type');
        $type = is_null($type) ? 'mysql' : $type;
        return $type;
    }
}

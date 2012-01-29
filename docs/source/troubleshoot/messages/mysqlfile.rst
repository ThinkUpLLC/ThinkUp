The MySQL user does not have the proper file permissions to backup/export data
==============================================================================

ThinkUp's MySQL user does not have the proper file permissions to back up or export data. Make sure the MySQL
user has write privileges to ThinkUp's ``backup`` folder under the data directory
defined in ``config.inc.php``'s ``$THINKUP_CFG['datadir_path']`` value.

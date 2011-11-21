The MySQL user does not have the proper file permissions to backup/export data
==============================================================================

The Thinkup MySQL user does not have the proper file permissions to backup or export data. Make Sure the MySQL
server has write privileges to the ThinkUp's ``compiled_view`` directory under the data directory
defined in $THINKUP_CFG['datadir_path'].

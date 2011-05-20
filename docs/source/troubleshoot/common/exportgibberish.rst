Exporting posts outputs gibberish
==================================

This is a problem with non-Latin characters written to the datastore using a latin1 PDO connection but read using
UTF-8. To fix it, make sure 'set_pdo_charset' is set to true in your config file to set the PDO connection to UTF-8.
To convert an existing datastore to UTF-8, do the following:

(Notes: This won't work on Windows due to UTF-8 incompatibilities.)

1. Back up your DB the normal way just in case you need to revert to this.

``mysqldump --opt thinkupDB > thinkup.sql``

2. Back up your DB using latin1.  This will decode the gibberish into proper "text".

``mysqldump --opt --default-character-set=latin1 thinkupDB > thinkup.latin.sql``

3. Remove the following line or change latin1 to utf8 in thinkup.latin.sql. Note: This file is huge. Use a text editor
which is able to handle huge files.

``/*!40101 SET NAMES latin1 */;``

4. Restore the decoded SQL data as UTF8.  (--default-character-set=utf8 is only necessary if UTF8 is not default)

``mysql --default-character-set=utf8 thinkupDB < thinkup.latin.sql``

5. Set Thinkup to use UTF8 by adding the following line to config.inc.php.

``$THINKUP_CFG['set_pdo_charset'] = true;``

`Mailing list thread "Gibberish in TU database" <https://groups.google.com/d/topic/thinkupapp/Ql-zzUOnQmA/discussion>`_

# ThinkUp Database Migrations

Some ThinkUp code changes require modifications to the ThinkUp database structure. Eventually an auto-update mechanism will make this process obselete, but for now, when a commit requires a database schema change, you have to run the migration script listed here.

## Version Migrations

When a new version of ThinkUp gets tagged, all the database migration scripts associated with it get rolled up into a single script that ends with `.sql.migration`. For example, version 0.001 was tagged v0.001 on February 27th. Therefore, the migration associated with it is named `2010-02-27_v0.001.sql.migration`.

## Migrations Since the Last Version Release

If you're downloading the latest development version of ThinkUp, there may be migrations that you need to run it. Each script name has a date and description that coincides with the commit that requires it.

If a commit message starts with `[DB MIGRATION REQ'D]`, after you pull the latest code files, you'll need to run the corresponding migration script on your ThinkUp database before you run the crawler or use the webapp. If you don't, you'll run into errors.

To run a migration script at the command line, substitute in your DB username and path to the ThinkUp files, and use this command:

`mysql -u yourusername -p thinkup < /path/to/thinkup/sql/mysql_migrations/YYY_MM_DD_script-name.sql`

Alternately, if you have web-based access to your database, like via PHPMyAdmin, copy and paste the migration script into the SQL window and run it.

## Migration Script Assumptions

The scripts listed here assume you're using the ThinkUp default table prefix `tu_`. 

If you are not, manually modify the script to use your prefix or delete it entirely to match your setup before you run it (or else you'll get table not found errors).

# ThinkUp Database Migrations

Some ThinkUp code changes require modifications to the ThinkUp database structure. When a commit requires a database
schema change, the application must run the relevant migration script(s) contained in this directory.

## Version Migrations

Up to beta 15, when a new version of ThinkUp got tagged, all the database migration scripts associated with it got
rolled up into a single script that ends with `.sql.migration`. For example, version 0.001 was tagged v0.001 on
February 27th. Therefore, the migration associated with it is named `2010-02-27_v0.001.sql.migration`.

After beta 15, a version could have multiple or no migration scripts associated with it. Released migration
filenames end in their associated version number. For example, a groups migration, written on October 21st, 2011,
got released with beta 17. Its filename is `2011-10-21_groups_v0.17.sql`.

## Migrations Since the Last Version Release

If you're downloading the latest development source code, there may be migrations that have not yet been 
released to users, but are required to run the latest application code. Each of these migration script filenames
have a date and description that coincides with the commit that requires it.

If any commit messages start with `[DB MIGRATION REQ'D]`, after you pull the latest source code, ThinkUp will need to
update its database structure before you can reliably run the crawler or use the webapp. If you don't, you'll run into
errors.

Developers can use the CLI upgrade tool to run any new database migrations using the argument “–with-new-sql”, like
this:

``$ cd install/cli/; php upgrade.php --with-new-sql``

The CLI tool will keep track of any migrations that have already been applied and only run new migrations.
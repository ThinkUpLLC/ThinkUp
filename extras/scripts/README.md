#  ThinkUp Scripts

Bash scripts that automate common use and development tasks in ThinkUp.

## autodeploy (for developers testing file changes)

Automates the process of uploading ThinkUp changes to your webserver when you're editing them locally.

* `autodeploy-scp` - autodeploy via SCP
* `autodeploy-conf` - configuration

## Set up autodeploy

* Copy `autodeploy-conf.sample` to `autodeploy-conf`
* Edit `autodeploy-conf` to match your settings
* Run `autodeploy` script from thinkup root directory

Example: `./extras/scripts/autdeploy-scp`

## generate-distribution (creates user distribution of app)

Generates a user distribution of the web application.

Run this from ThinkUp's parent directory, and make sure a directory named build exists there.
  
## migratedb (for developers changing the database)

Iterates through all database migration files (including any new ones you're testing) and generates the final `build-db_mysql.sql`.
 
* `migratedb` - run through all migrations start to finish and generate `build-db_mysql.sql`
* `migratedb-conf` - configuration
 
### Set up and run migratedb

* Copy `migratedb-conf.sample` to `migratedb-conf`
* Edit `migratedb-conf` to match your settings
* Run `migratedb` script from thinkup root directory

Example: `./extras/scripts/migratedb`
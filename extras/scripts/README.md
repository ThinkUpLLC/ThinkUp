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

Generates a user distribution of the web application called thinkup.zip, and drops it into a thinkup/build directory.

Run this from ThinkUp's parent directory, and make sure a directory named build exists there.

## insight-html-to-server (for developers to share and preview insight test HTML output)

Automates the generation of HTML files for each insight test, and pushing to a webserver via scp.

* `insight-html-to-server-conf` - configuration

### Set up insight-html-to-server

* Copy `insight-html-to-server-conf.sample` to `insight-html-to-server-conf`
* Edit `insight-html-to-server-conf` to match your settings
* (optionally) Symlink insight-html-to-server in to your path
    `ln -s ./insight-html-to-server ~/bin/`
* Run `insight-html-to-server`
  
## migratedb (for developers changing the database)

Iterates through all database migration files (including any new ones you're testing) and generates the final `build-db_mysql.sql`.
 
* `migratedb` - run through all migrations start to finish and generate `build-db_mysql.sql`
* `migratedb-conf` - configuration
 
### Set up and run migratedb

* Copy `migratedb-conf.sample` to `migratedb-conf`
* Edit `migratedb-conf` to match your settings
* Run `migratedb` script from thinkup root directory

Example: `./extras/scripts/migratedb`

## lic_header.py (pre-commit hook script for adding the license header to files)

How to test:

0.  Install Python if necessary. (If you're using Windows, this script has been tested using Cygwin with Python and git installed.)

1. Copy lic_header.sample.py to a new file lic_header.py, e.g.
   cd extras/scripts; cp lic_header.sample.py lic_header.py

2. Edit lic_header.py and change the THINKUP_HOME variable appropriately

3. make lic_header.py executable, e.g.
   chmod 755 lic_header.py

4. Test the script by editing some existing php files, e.g. under webapps/_lib, to remove their headers.
   You can run the script like this from the thinkup home dir:
   % extras/scripts/lic_header.py

  (It has a few options; call it with --help to see them)

5. To test the pre-commit hook part, create a file under the .git directory named .git/hooks/pre-commit, containing the following two lines:

#!/bin/sh
./extras/scripts/lic_header.py

  Make this file executable (important).  It will now run whenever you do a commit. If there were files updated by the script, the commit should not go through.

  Windows/Cygwin troubleshooting: If you get a "fatal error - unable to remap same address as parent", here's how to fix:
  http://www.mylifestartingup.com/2009/04/fatal-error-unable-to-remap-to-same.html
  
## test-pull-postback (post test results for a particular pull request back to that pull's comments on GitHub)

Given a username, branchname, and pull request ID, this script pulls a branch, runs the ThinkUp test suite, and given
a valid GitHub OAuth token, posts the test results to the pull request's comments automatically.
Without a GitHub OAuth token set, the script simply outputs the contents of the test results message.

Example output: https://github.com/ginatrapani/ThinkUp/pull/795

Requires: test-pull-postback-conf (see test-pull-postback-conf-sample for instructions on getting GitHub credentials)

### Set up and run test-pull-postback

* Copy `test-pull-postback-conf.sample` to `test-pull-postback-conf`
* Edit `test-pull-postback-conf` to match your settings
* Run `test-pull-postback` script from thinkup root directory

Example: `./extras/scripts/test-pull-postback`

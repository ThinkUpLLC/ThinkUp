# ThinkUp Tests

All code submitted to the repository should have corresponding tests that pass. Here's how to run and write tests. 

# WARNING

**Do *not* run tests on your live ThinkUp database.** If you run tests without properly configuring a separate database,
you will DESTROY ALL DATA IN YOUR THINKUP INSTANCE. That would be so sad!

To avoid data loss, make sure both your `tests/config.tests.inc.php` and `webapp/config.inc.php` files point to a 
clean, empty tests database.

## Running Tests

First, configure your test environment. 

Copy `tests/config.tests.sample.inc.php` to `tests/config.tests.inc.php` and 
set the appropriate values. You will need a clean, empty database to run your tests. By default, name it 
`thinkup_tests` and set the `$TEST_DATABASE` config variable to that name.

In `webapp/config.inc.php`, in the DEVELOPER CONFIG section, set the name of your tests database, and the username and
password to access it. This database name should match the one you just set in `tests/config.tests.inc.php`.

### Test Assumptions

In order for the tests to pass, you must:

* Have a `tests/config.tests.inc.php` file with the correct values set
* Set the crawler log file in `webapp/config.inc.php` and make that file writable
* Set the test database name to an empty tests database which the tests will destroy each run in `webapp/config.inc.php`
* Set the test database user to a user with all privileges in the test database and global CREATE, DROP, and FILE privs
* Set caching to false in `webapp/config.inc.php`
* Have a local installation of ThinkUp using your test database
* Have a working internet connection

To run a particular test case, like the UserDAO test, in the ThinkUp source code root folder, use this command: 

    $ php tests/TestOfUserDAO.php

To run all the test cases, use:

    $ php tests/all_tests.php

To run a single test, set the TEST_METHOD environment variable. For example:

    $ TEST_METHOD=testIsPluginActive php tests/TestOfPluginMySQLDAO.php

The webapp tests contained in `tests/all_integration_tests.php` make three assumptions:

## Writing Tests

The test suite assumes there is an empty tests database (like `thinkup_tests`) which the default ThinkUp database user 
can access. If your test needs to read and write to the ThinkUp database, extend `ThinkUpUnitTestCase` and run 
`parent::setUp()` in your `setUp()` method, and `parent::tearDown()` in your `tearDown()` method. These methods create 
an empty copy of the ThinkUp database structure to execute a test, then drop all the tables in it when the test is
complete. After you call the parent `setUp()` method in your test's `setUp()`, insert the data your test requires. 

Best practices for writing tests are still getting developed. In the meantime, use existing tests as examples. 

### Model Tests (`all_model_tests.php`)

See `TestOfOwnerInstanceMySQLDAO.php` as an example of a set of DAO tests. Use the FixtureBuilder class to create test
data fixtures to test against.

### Controller Tests (`all_controller_tests.php`)

See `TestOfDashboardController.php` as an example of a set of controller test cases.

### Plugin Tests (`all_plugin_tests.php`)

All plugin-specific tests should live in the `thinkup/webapp/plugins/plugin-name/tests/` directory. Write tests
for the plugin's model objects and controller methods. 

To test consumption of data from web services, mock up the appropriate classes and store test data to local files in 
the format the API would return them in. For example, the `classes/mock.TwitterOAuth.php` class reads Twitter data 
from the files in the `testdata` directory. 

See `/thinkup/webapp/plugins/twitter/tests/` for examples of Twitter crawler plugin tests. 

### Integration Tests (`all_integration_tests.php`)

Add tests for particular pages inside the webapp to an appropriately-named class. See `WebTestOfChangePassword.php` 
for an example. 

Once your tests work, add them to the `all_tests.php` file to run along with the existing tests. 

## Debugging during testing

If you want to print variable valies to the terminal while running tests, there is a ThinkUpWebTestCase::debug method
and a ThinkUpBasicTestCase::debug method. To use it, add a line like this to your test:

`$this->debug("This is my debugging statement which will print during my test run.")`

To see your debug statements, run your test like so:

`TEST_DEBUG=1 php tests/yourtest.php`

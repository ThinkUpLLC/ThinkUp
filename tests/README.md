# ThinkUp Tests

All code submitted to the repository should have corresponding tests that pass. Here's how to run and write tests. 

## Running Tests

First, configure your test environment. Copy `tests/config.tests.sample.inc.php` to `tests/config.tests.inc.php` and 
set the appropriate values. You will need a clean, empty database to run your tests. By default, name it 
`thinkup_tests` and set the `$TEST_DATABASE` config variable to that name.

Then, to run a particular test, like the UserDAO test, in the thinkup source code root folder, use this command: 

    $ php tests/TestOfUserDAO.php

To run all the tests, use:

    $ php tests/all_tests.php

The webapp tests contained in `tests/all_frontend_tests.php` make three assumptions:

* You have a local installation of ThinkUp and that it is using your test database
* Your local installation's `config.inc.php` points to the `thinkup_tests` database
* Your local installation's `config.inc.php` has caching set to false

## Writing Tests

The test suite assumes there is an empty tests database (like `thinkup_tests`) which the default ThinkUp db user 
can access. If your test needs to read and write to the ThinkUp database, extend `ThinkUpUnitTestCase` and run 
`parent::setUp()` in your `setUp()` method, and `parent::tearDown()` in your `tearDown()` method. These methods create 
an empty copy of the ThinkUp database structure to execute a test, then drop all the tables in it when the test is
complete. After you call the parent `setUp()` method in your test's `setUp()`, insert the data your test requires. 

Best practices for writing tests are still getting developed. In the meantime, use some existing tests as examples. 

### Model Tests (`all_model_tests.php`)

See `TestOfOwnerInstanceMySQLDAO.php` as an example of a set of DAO tests. Use the FixtureBuilder class to create test
data fixtures to test against.

### Controller Tests (`all_controller_tests.php`)

See `TestOfPublicTimelineController.php` as an example of a set of controller test cases.

### Plugin Tests (`all_plugin_tests.php`)

All plugin-specific tests should live in the `thinkup/webapp/plugins/plugin-name/tests/` directory. Write tests
for the plugin's model objects and controller methods. 

To test consumption of data from web services, mock up the appropriate classes and store test data to local files in 
the format the API would return them in. For example, the `classes/mock.TwitterOAuth.php` class reads Twitter data from 
the files in the `testdata` directory. 

See `/thinkup/webapp/plugins/twitter/tests/` for examples of Twitter crawler plugin tests. 

### Integration Tests (`all_frontend_tests.php`)

Add tests for particular pages inside the webapp to an appropriately-named class. See `TestOfChangePassword.php` for an
example. 

Once your tests work, add them to the `all_tests.php` file to run along with the existing tests. 

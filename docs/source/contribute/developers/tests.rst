Run ThinkUp's Test Suite
========================

All code submitted to the repository should have corresponding tests
that pass. Here's how to run and write tests.

Configure Your Test Environment
-------------------------------

Copy ``tests/config.tests.sample.inc.php`` to ``tests/config.tests.inc.php`` and set the appropriate values. You will
need a clean, empty database to run your tests. By default, name it ``thinkup_tests`` and set the ``$TEST_DATABASE``
config variable to that name. You will also need a local installation of ThinkUp; set the ``$TEST_SERVER_DOMAIN``
config variable equal to its URL--for example, ``http://localhost``.

In ``webapp/config.inc.php``, in the DEVELOPER CONFIG section, set the name of your tests database, and the username
and password to access it. This database name should match the one you just set in ``tests/config.tests.inc.php``.
Finally, set ``$THINKUP_CFG['source_root_path']`` to the full path of the thinkup source files.

Test Assumptions
----------------

In order for the tests to pass, you must:

-  Have a ``tests/config.tests.inc.php`` file with the correct values
   set
-  Setup the crawler, stream and sql log files in ``webapp/config.inc.php``
   and make those files writable
-  Set the test database name to an empty tests database which the tests
   will destroy each run in ``webapp/config.inc.php``
-  Set the test database user to a user with all privileges in the test
   database and global CREATE, DROP, and FILE privs
-  Set caching to false in ``webapp/config.inc.php``
-  Have a local installation of ThinkUp using your test database
-  Have a working internet connection

To run a particular test suite, like the UserDAO suite, in the ThinkUp
source code root folder, use this command:

::

    $ php tests/TestOfUserMySQLDAO.php

To run all the test suites, use:

::

    $ php tests/all_tests.php

To run a single test, use SimpleTest's -t parameter. For example:

::

    $ php tests/TestOfPluginMySQLDAO.php -t testIsPluginActive

To see all the available options, run:

::

    $ php tests/all_tests.php -help

Writing Tests
-------------

The test suite assumes there is an empty tests database (like
``thinkup_tests``) which the default ThinkUp database user can access.
If your test needs to read and write to the ThinkUp database, extend
``ThinkUpUnitTestCase`` and run ``parent::setUp()`` in your ``setUp()``
method, and ``parent::tearDown()`` in your ``tearDown()`` method. These
methods create an empty copy of the ThinkUp database structure to
execute a test, then drop all the tables in it when the test is
complete. After you call the parent ``setUp()`` method in your test's
``setUp()``, insert the data your test requires.

Best practices for writing tests are still getting developed. In the
meantime, use existing tests as examples.

Model Tests (``all_model_tests.php``)
-------------------------------------

See ``TestOfOwnerInstanceMySQLDAO.php`` as an example of a set of DAO
tests. Use the FixtureBuilder class to create test data fixtures to test
against.

Controller Tests (``all_controller_tests.php``)
-----------------------------------------------

See ``TestOfDashboardController.php`` as an example of a set of
controller test cases.

Plugin Tests (``all_plugin_tests.php``)
---------------------------------------

All plugin-specific tests should live in the
``thinkup/webapp/plugins/plugin-name/tests/`` directory. Write tests for
the plugin's model and controller objects.

To test consumption of data from web services, mock up the appropriate
classes and store test data to local files in the format the API would
return them in. For example, the ``classes/mock.TwitterOAuth.php`` class
reads Twitter data from the files in the ``testdata`` directory.

See ``/thinkup/webapp/plugins/twitter/tests/`` for examples of Twitter
crawler plugin tests.

Integration Tests (``all_integration_tests.php``)
-------------------------------------------------

Add tests for particular pages inside the webapp to an
appropriately-named class. See ``WebTestOfChangePassword.php`` for an
example.

Once your tests pass, add them to the appropriate ``all_tests.php`` file
to run with the existing suites. For example, new model tests should go
in ``all_model_tests.php``, new controller tests should go in
``all_controller_tests.php``, etc.

How to Debug Tests
------------------

To print variable values to the terminal while running tests, use the
ThinkUpWebTestCase::debug method or ThinkUpBasicTestCase::debug method.
For example, you can add a line like this to your test:

``$this->debug("This is my debugging statement which will print during my test run.");``

To print something other than a string in a debug statement, use the
Utils::varDumpToString method, like this:

``$this->debug(Utils::varDumpToString($my_nonstring_object));``

To see your debug statements, run your test like so:

``TEST_DEBUG=1 php tests/yourtest.php``

How to Speed Up Test Runs
-------------------------

You can see how much time test groups take by setting the ``TEST_TIMING`` variable like so:

``TEST_TIMING=1 php tests/all_tests.php``

There are a few ways to speed up the test runs:

1.  Set the environment var ``SKIP_UPGRADE_TESTS``. This will skip the installation upgrade test, and shave a
    few minutes off of the test run.

    ``SKIP_UPGRADE_TESTS=1 php tests/all_tests.php``

2.  On OS X, set up your test database to run in a RAM disk to speed up database I/O during testing.

    You will need to update the ``config.inc.php`` file to reflect the latest test override and test RAM disk option.

    Copy ``./extras/dev/ramdisk/osx_make_ramdisk_db.conf.sample`` to ``./extras/dev/ramdisk/osx_make_ramdisk_db.conf``
    and edit as necessary.

    Finally, run the script to create the RAM disk and the RAM disk database:

    ``sudo sh ./extras/dev/ramdisk/osx_make_ramdisk_db create -v``

    Run the tests with ``RD_MODE`` set to 1:

    ``RD_MODE=1 php tests/all_tests.php``

    When you are done testing you can remove the RAM disk with this command:

    ``sudo sh extras/dev/ramdisk/osx_make_ramdisk_db delete -v``

3.  On Ubuntu, set up your test database to run in a RAM disk to speed up database I/O during testing.

    You will need to run:

    ``sudo ./extras/dev/ramdisk/ubuntu_make_ramdisk_db``

    When you are done you MUST run:

    ``sudo ./extras/dev/ramdisk/ubuntu_remove_ramdisk_db``

    Or your MySQL installation will be destroyed.

I'm getting lots of test failures. Help!
----------------------------------------

Possible reasons for getting a high number of test failures include:

-  An incorrect $TEST\_SERVER\_DOMAIN in tests/config.tests.inc.php.
   Please make sure that this points to the web root of your ThinkUp
   installation. `Relevant
   thread <https://groups.google.com/a/expertlabs.org/group/thinkup-dev/browse_thread/thread/755ac5a5f32666fc/>`_
-  An incorrect value for any of the test database values. Please make
   sure that both config.inc.php and config.tests.inc.php point to an
   existing, empty database.
-  AppArmor which is installed on Ubuntu by default can prevent the ThinkUp test suite backup tests from writing to the
   files it needs to. To fix this add the following to your ``/etc/apparmor.d/usr.sbin.mysqld`` file:

   ``path_to_thinkup/webapp/data/backup/* rw,``

   and then restart AppArmor with:

   ``sudo /etc/init.d/apparmor reload``

If you have double-checked these and everything appears to be intact,
send an email to the mailing list or pop into the IRC channel and we'll
see what we can do to help you out.

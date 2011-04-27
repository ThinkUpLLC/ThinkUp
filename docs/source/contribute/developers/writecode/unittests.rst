How to Write Great Unit Tests
=============================

ThinkUp contributors should use a `test-driven
development <http://en.wikipedia.org/wiki/Test-driven_development>`_
approach.

ThinkUp uses the `SimpleTest unit tester
tool <http://www.simpletest.org/>`_ to create thorough and complete unit
tests for all new and modified code. If your code doesn’t have
corresponding tests, it won’t get merged into the ThinkUp master.

ThinkUp tests are located in the /ThinkUp/tests/ folder.

ThinkUp Testing Best Practices
------------------------------

This list is a work in progress.

-  **DAO Tests:** When testing an insert or update to data, don’t rely
   on the DAO’s get method to verify the update. Instead, use raw SQL to
   retrieve the inserted/updated row and assert it works. `Related
   mailing list
   thread. <http://groups.google.com/group/thinkupapp/browse_thread/thread/cc9ca0fb19378245>`_

-  **Crawler plugin tests:** When testing data returned by a web service
   API, do not query the live API in your tests. Instead, mock a class
   that returns all possible values that you expect from the API, and
   write tests against those values. For example, the `mock TwitterOAuth
   class <http://github.com/ginatrapani/ThinkUp/blob/master/webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php>`_
   reads test Twitter data from files stored in the testdata directory,
   instead of hitting Twitter.com live.

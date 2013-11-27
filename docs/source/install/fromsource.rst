Install ThinkUp from Source
===========================

To run ThinkUp's nightly code with all the latest features and fixes without waiting for the user distribution release,
you can pull that code from the GitHub repository.

To install ThinkUp from source:

1. First, clone ThinkUp's GitHub repository to a publicly-accessible folder your web server
using this command:

::

    $ git clone git@github.com:ginatrapani/ThinkUp.git

2. Visit ThinkUp's location in your web browser, and walk through the application installation process.

3. Finally, run any necessary database migrations using this command in the root directory of ThinkUp's source code:

::

    $ cd install/cli/; php upgrade.php --with-new-sql

Notes
-----

When you run ThinkUp from source, keep in mind you will have:

* A slightly different folder structure than the user distribution. The application code for ThinkUp lives in 
  the webapp folder in the GitHub repository. In the user distribution, the application code is the root folder.
* Many more files than you'll need to run ThinkUp which are not included in the user distribution, including tests,
  test data, developer tools, and more.
* The need to run any database migrations which have occurred since the last user distribution. Instructions on how
  to do that are detailed above. 


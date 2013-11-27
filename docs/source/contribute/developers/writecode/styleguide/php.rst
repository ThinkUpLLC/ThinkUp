PHP Code Style Guide
====================

This is ThinkUp's Code Style guide for PHP. See the :doc:`main page </contribute/developers/writecode/styleguide/index>` 
for guides specific to other languages.

Assume we're using `the Drupal PHP coding style <http://drupal.org/coding-standards>`_ unless otherwise noted here.

Always use <?php to delimit PHP code, not the shorthand, <?, as this is the most used and supported across PHP setups
and the one PHP will continue supporting in the future.

Use PHP5 conventions
--------------------

When in doubt, use PHP5 (not PHP4) coding conventions.

-  Class constructors should be public function \_\_construct (), not
   the PHP4-style class name. Use
   `destructors <http://www.php.net/manual/en/language.oop5.decon.php>`_
   when appropriate.
-  Explicitly declare
   `visibility <http://www.php.net/manual/en/language.oop5.visibility.php>`_
   (public, private, protected) for member variables and methods.
-  Do **NOT** use PHP closing tags for files that contain only PHP code:

    The \?> at the end of code files is purposely omitted. Removing it eliminates the
    possibility of unwanted whitespace at the end of files which can
    cause header already sent errors, XHTML/XML validation issues, and other problems.

Indentation
-----------

Use an indent of 4 spaces, with no tabs.

Braces
------

Always use curly braces, even in situations where they are technically optional. Having them increases readability
and decreases the likelihood of logic errors being introduced as the codebase continues to evolve.

Opening curly braces should never be on their own new line.

Closing curly braces should always be on their own new line.

Avoid multiple blank lines
--------------------------

Separating sections of code with a blank line is okay, but never more than 1 blank line at a time.

Maximum line length: 120 characters
-----------------------------------

The maximum length of any line of code is 120 characters, unless it contains a string that cannot have a break in it.
(This differs from Drupal's 80-character maximum length.)

Tip: If you're using Eclipse, add a ruler to the 120 mark to see where you should break to the next line. See
`Developer Guide: Setting Up Eclipse 
PDT <https://github.com/ginatrapani/ThinkUp/wiki/Developer-Guide:-Setting-Up-Eclipse-PDT>`_ for how.

Include docblocks in all code
-----------------------------

ThinkUp uses PHPDocumentor to ease code maintenance and `auto-generate class documentation 
<http://thinkup.com/reference/>`_. Include PHPDoc-style “docblocks” in all of your PHP code. When writing your
documentation, please use `PHPDocumentor's 
syntax <http://github.com/ginatrapani/ThinkUp/wiki/ThinkUp-and-PHPDocumentor-(PHPDoc)>`_.

Keyword case
------------

Drupal style guide states use of uppercase value keywords (TRUE, FALSE, NULL), ThinkUp user lowercase.

Same-line curly braces
----------------------

Unlike Drupal's style guide, ThinkUp keeps opening and closing curly braces on the same line as the control keyword 
(if, else).

Boolean operator at the beginning of the line
---------------------------------------------

Like the Drupal convention, when constructing multi line IFs, the boolean operator should be at the beginning of the
line, not the end.

MVC architecture
----------------

ThinkUp implements the :doc:`Model-View-Controller </contribute/developers/mvc>`  design pattern. All new PHP code
should follow suit. Read more about ThinkUp's :doc:`MVC implementation </contribute/developers/mvc>`.


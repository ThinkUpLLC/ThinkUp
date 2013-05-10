JavaScript Code Style Guide
===========================

This is the Code Style guide for JavaScript. See the 
:doc:`main page </contribute/developers/writecode/styleguide/index>` for other languages.

Indentation
-----------

Use an indent of 2 spaces, with no tabs.

Names
-----

Functions and variables should be named using lowerCamelCase.

Braces
------

Always use curly braces, even in situations where they are technically
optional. Having them increases readability and decreases the likelihood
of logic errors being introduced as the codebase continues to evolve.

Opening curly braces should never be on their own new line.

Closing curly braces should always be on their own new line.

Semicolons
----------

To avoid semicolon insertion (Q: What's that? A: `See
Wikipedia <http://en.wikipedia.org/wiki/JavaScript_syntax#Whitespace_and_semicolons)>`_),
end all statements with a semicolon, except for ``for``, ``function``, ``if``,
``switch``, ``try``, and ``while``.

For the same reason, a function's return value expression must start on
the same line as the return keyword.

Semicolons must also follow functions declared in this manner:

::

    result = function (parameter) {
      // Statements.
    };

and do-while control statements:

::

    do {
      // Statements.
    } while (condition);

Comments
--------

Non-documentation comments (the explanatory “what does this block of code do?" type) are strongly encouraged.
Concerned about performance? Don't worry, comments can be removed by
JavaScript compression utilities for use on production servers.

Comments should use capitalized sentences with punctuation. Comments
should be on a separate line immediately before the code line or block
they reference.

::

    // Unselect all checkboxes.
    result = unselectCheckboxes(myArray);

If each line of a list needs a separate comment, the comments may be
given on the same line and may be formatted to a uniform indent for
readability.

::

    var parameter1 = 'foo';            // Parameter 1 comment goes here.
    var parameter2 = 'barbaz';         // Parameter 2 comment goes here.
    var parameter3 = 'someothervalue'; // Parameter 3 comment goes here.

C style comments (``/* Comment goes here. */``) and C++ style comments (``//
Comment goes here.``) are both fine.

Operators
---------

All binary operators (operators that come between two values), such as
+, -, =, !=, ==, >, &&, \|\|, etc. should have a space before and after
the operator, for readability.

::

    var string = 'Foo' + bar;
    var string += 'Foo';
    if ((someQty < otherQty) && (someBoolean == true)) {
      doStuff();
    }

Unary operators (operators that operate on only one value), such as ++,
!, etc. should not have a space between the operator and the variable or
number they are operating on.

::

    someInt++;
    if (!condition) {
      action();
    }

JavaScript has one ternary operator (operators that operate on three
values) called the conditional operator. The ternary operator should
have a space on either side of the ? and the :.

::

    condition ? result1 : result2;

Control Statements
------------------

Control statements should be made with:
\* one space between the control keyword and opening parenthesis (to
distinguish control statements from function calls);
\* no spaces between the opening parenthesis and the first condition;
\* spacing around any logical operators as previously described;
\* no spaces between the last condition and the closing parenthesis; and
\* one space between the closing parenthesis and the opening curly
brace.

An example if statement:

::

    if (condition1 || condition2) {
      action1();
    }
    elseif (condition3 && condition4) {
      action2();
    }
    else {
      defaultAction();
    }

An example switch statement:

::

    switch (condition) {
      case 1:
        action1();
        break;

      case 2:
        action2();
        break;

      default:
        defaultAction();
    }

An example try statement:

::

    try {
      // Statements.
    }
    catch (variable) {
      // Error handling.
    }
    finally {
      // Statements.
    }

Functions
---------

Functions should be called with:
\* no spaces between the function name, the opening parenthesis, and the
first parameter;
\* spaces between commas and each parameter; and
\* no space between the last parameter, the closing parenthesis, and the
semicolon.

::

    myVar = myFunction(parameter1, parameter2, parameter3);

Functions should be defined using the same spacing as function calls,
except that there should be a single space between the function name and
the opening parenthesis. This avoids confusion when dealing with
anonymous functions (function (e) {}).

::

    function myFunction (parameter1, parameter2) {
      alert("This JS file does fun message popups.");
      return false;
    }

Arrays
------

Arrays should be formatted with a space separating each element and
assignment operator, if applicable. If the line spans longer than 80
characters, each element should be broken into its own line, and
indented one level.

::

    var shortArray = ['hello', 'world'];
    var longArray = [
      'hello',
      'world',
      'foo',
      'bar',
      'baz'
    ];

jQuery Snippet Formatting
-------------------------

TODO (some of the above style rules for straight JavaScript might not
make sense for jQuery snippets.)
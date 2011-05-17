CSS Code Style Guide
====================

This is the Code Style guide for CSS. See the :doc:`main page </contribute/developers/writecode/styleguide/index>` for
other languages.

Indentation
-----------

Use an indent of 2 spaces, with no tabs.

Names & Capitalization
----------------------

ID names should be in lowerCamelCase.

::

    #pageContainer {

Class names should be in lowercase, with words separated by underscores.

::

    .my_class_name {

HTML elements should be in lowercase.

::

    body, div {

Use External Stylesheets
------------------------

Do not write inline styles or embedded styles unless unavoidable.
Inlining or embedding styles is most likely avoidable — ask the ThinkUp
mailing list if you're not sure.

For performance reasons (see `Steve Souder's
blog <http://www.stevesouders.com/blog/2009/04/09/dont-use-import/)>`_,
always link to external stylesheets using the ``<link>`` syntax rather
than the ``import`` syntax.

::

    <link rel="stylesheet" href="a.css"> <!-- Okay -->
    <style type="text/css">@import url("a.css");</style> <!-- Not Okay -->

Write Valid CSS
---------------

ThinkUp's CSS should be valid to the `CSS 2.1
specification <http://www.w3.org/TR/CSS2/>`_. CSS 3.0 rules are
acceptable as long as they degrade gracefully.

Run any CSS you write through `the W3C
validator <http://jigsaw.w3.org/css-validator/>`_ and ensure it passes
before submitting a pull request.

Comments
--------

Comments are strongly encouraged. Concerned about performance? Don't
worry, comments can be removed by CSS minification utilities for use on
production servers.

Comments that refer to selector blocks should be on a separate line
immediately before the block to which they refer. Short inline comments
may be added after a property-value pair, preceded with a space.

::

    /* Comment about this selector block. */
    selector {
      property: value; /* Comment about this property-value pair. */
    }

Only C style comments (/\* Comment goes here. \*/) are valid for CSS
code. Do not use C++ style comments (// Comment goes here.).

Selectors
---------

Selectors should be on a single line, with a space after the last
selector, followed by an opening brace. A selector block should end with
a closing curly brace that is unindented and on a separate line. A blank
line should be placed between each selector block. Selectors should
never be indented.

::

    selector {
    }

    selector {
    }

Multiple selectors should each be on a single line, with no space after
each comma.

::

    selector1,
    selector2,
    selector3,
    selector4 {
    }

When selecting HTML elements, write the selector in lowercase.

::

    div { /* Okay */
    DIV { /* Not okay */

Property-Value Pairs
--------------------

Property-value pairs should be listed starting on the line after the
opening curly brace. Each pair should:
\* be on its own line;
\* be indented one level;
\* have a single space after the colon that separates the property name
from the property value; and
\* end in a semicolon.

::

    selector {
      name: value;
      name: value;
    }

Multiple property-value pairs should be listed in alphabetical order by
property.

::

    /* Not okay */
    body {
      font-weight: normal;
      width: 500px;
      background: #000;
    }

    /* Okay */
    body {
      background: #000;
      font-weight: normal;
      width: 500px;
    }

For properties with multiple values, separate each value with a single
space following the comma (s).

::

      font-family: Helvetica, sans-serif;

If a single value contains any spaces, that value must be enclosed
within double quotation marks.

::

      font-family: "Lucida Grande", Helvetica, sans-serif;

Colors
------

When denoting color using hexadecimal notation, use all capital letters.
Both three-digit and six-digit hexadecimal notation are acceptable; if
it's possible to specify the desired color using three-digit hexadecimal
notation, do so as you'll save the end-user a few bytes of download
time.

::

      color: #FFF;    /* Okay */
      color: #FE9848; /* Okay */
      color: #fff;    /* Not okay */

Dimensions
----------

When denoting the dimensions - that is, the width or height -
of an element or its margins, borders, or padding, specify the units in
either em, px, or %. If the value of the width or height is 0, do not
specify units.

::

      width: 12px; /* Okay */
      width: 12%;  /* Okay */
      width: 12em; /* Okay */
      width: 12;   /* Not okay */
      width: 0;    /* Okay */
      width: 0px;  /* Not okay */

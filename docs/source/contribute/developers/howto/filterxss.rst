How to Clean External Data Before Displaying It in Your View
============================================================

Any time you display data in ThinkUp which comes from an outside source--whether that's Twitter.com or user input on
the registration form--you must remove any HTML or JavaScript from that content. If you don't, you make ThinkUp
vulnerable to `Cross-site scripting attacks <http://en.wikipedia.org/wiki/Cross-site_scripting>`_ (or XSS for short).

ThinkUp's application code includes a filter for cleaning data displayed in its views. To use it, pipe your view
data through a Smarty filter called ``filter_xss``.

What Not To Do
--------------

For example, to display the text of a post inside paragraph tags, the following Smarty template markup 
will include any JavaScript or HTML markup contained in that post text:

::

    <p>{$post->text}</p>

**DO NOT DO THIS.** 

What To Do
----------

Instead, clean that post text and display it inside paragraph tags within your Smarty template file
by using:

::

    <p>{$post->text|filter_xss}</p>

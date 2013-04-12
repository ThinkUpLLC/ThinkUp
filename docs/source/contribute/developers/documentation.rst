ThinkUp and PHPDoc
==================

ThinkUp uses `PHPdocumentor <http://phpdoc.org>`_ to keep track of the
documentation for our code. Though it creates fairly clean documentation
without any special intervention, it's possible to create even better
docs using some of the special features of PHPDoc.

Basic commenting
----------------

First thing to remember about PHPDoc is that all user comments need to
be enclosed in C-style comments with two leading asteriks, like so:

::

    /**
     * Hi, these are my comments
     * .
     * .
     * .
     */

Any other user comments are ignored by PHPDoc.

Secondly, your user comments must precede the code you're adding
comments to. For example, if you wanted to add some general comments to
a class declaration, here's what you'd do:

::

    /**
     * These are user generated comments
     * about the following class:
     */

    class myClass {
    ...
    }

You can precede class declarations, function declarations, even variable
declarations and PHPDoc will be able to organize your comments
correctly.

PHPDoc Tags (@)
---------------

Sometimes you'll want to add meta information to your comments. You can do this by using PHPDoc Tags, which start with an @
symbol, like this:

::

    @myMetaTag my meta information

Here are a few useful tags:

@package packagename
--------------------

The package that your code belongs to. In almost every case in our project, this will be ThinkUp

@param datatype $paramname description
--------------------------------------

As you've probably guessed, this tag allows you to document the
parameters of your function. Say you have a string parameter named
myParam in your function. Here's what you'd enter in your documentation:

::

    @param string myParam This is the description of my parameter

@return datatype description
----------------------------

No surprise, the @return tag is used to document the return value of
functions or methods.

@author authorname

The @author tag is used to document the author of any element that can
be documented. Also, phpDocumentor will take any text between angle
brackets (< and >) and try to parse it as an email address. If
successful, it will be displayed with a mailto link in the page. For
example:

::

    @author Generic Person <gperson@fakedomain.com>

The style guide requires you to append a email address wit the @author
tag, if you want to obscure it use gperson[at]fakedomain[dot]com

Example
-------

Here's a file that Gina's already added PHPDocumentor comments to:
https://github.com/ginatrapani/ThinkUp/blob/master/webapp/_lib/model/class.Post.php

This is what the documentation page that PHPDoc created looks like:
http://www.thinkupapp.com/reference/ThinkUp/Post.html
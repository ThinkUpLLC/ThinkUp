How to inline styles in the HTML Insights email
===============================================

HTML rendering in email clients is pretty poor, but it does pretty well if you use inline styles. Since it's a pain
to write your CSS rules inline, we don’t; we use software.

The precompiled HTML email template lives at ``extras/dev/precompiledtemplates/email/_email.insights_html.tpl``
and is written to ``webapp/plugins/insightsgenerator/view/_email.insights_html.tpl``.

Dependencies
------------

In order to compile, you need to have NodeJS and Ruby installed on your machines. This has been tested on a Mac, but
should work on any machine that supports Ruby and Node. We'll cover installation below, but these are the required
packages.

NodeJS modules
~~~~~~~~~~~~~~

* grunt-cli
* grunt
* grunt-contrib-watch
* grunt-premailer


Ruby gems
~~~~~~~~~

* hpricot
* premailer

Dependency Installation
-----------------------

1.  If you haven't done so already, install `NodeJS <http://nodejs.org/>`_ via homebrew `Homebrew <http://brew.sh/>`_
    (brew install node) or `their installer <http://nodejs.org/download/>`_.

2.  Assuming you have ruby on your system, install the two required gems with ``gem install hpricot premailer``.

3.  Before we install the node modules, we need to create a directory for them and symlink to it from the ThinkUp root
    directory. This isn't standard Node behavior, but it's the only way our tests will work. So, anywhere you'd like
    (though we recommend in the directory adjacent to ThinkUp), create a directory called "thinkup_node_modules". Create a
    "node_modules"symlink to that directory from the root directory of your ThinkUp repository.

4.  Navigate to the root directory of your ThinkUp repo and run ``npm install``. This should install all of the NodeJS
    modules we use in ThinkUp. It's actually more than you'll need, but there's a good chance you'll want all of them if
    you've come this far.

5.  In order to run the inliner, you'll need the grunt command line interfact. Install that with
    ``npm install -g grunt-cli``.

Usage
-----

After all that, using the inliner is incredibly easy.

1. Make your changes to the precompiled template (location is listed above).
2. From the root directory of your repository, run ``grunt html_email``.

Now, your CSS styles should be precompiled. Good work!
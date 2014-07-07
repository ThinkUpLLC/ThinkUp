How to use Grunt to process front-end templates
===============================================

`Grunt <http://gruntjs.com/>`_ is a task runner for front-end development. ThinkUp uses it for a few things.

1.  Our Javascript is written in `CoffeeScript <http://coffeescript.org/>`_. Grunt compiles theses files into JS.
2.  Our CSS is written in `LESS <http://lesscss.org/>`_. Again, Grunt handles this compilation.
3.  HTML emails require CSS to be included inline, not linked in the head. Grunt (and Premailer) do this for us.

Below you’ll find instructions on installing Grunt and using it for these tasks.


Installing Grunt and its dependencies
-------------------------------------

In order to compile JS and CSS, you need to have NodeJS installed on your machine. If you’re compiling HTML emails,
you’ll need Ruby too. This has been tested on a Mac, but should work on any machine that supports Ruby and Node.
We'll cover installation below, but these are the required packages.

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

Install
~~~~~~~

1.  If you haven't done so already, install `NodeJS <http://nodejs.org/>`_ via homebrew `Homebrew <http://brew.sh/>`_
    (brew install node) or `their installer <http://nodejs.org/download/>`_.

2.  Assuming you have Ruby on your system, install the two required gems with ``gem install hpricot premailer``. (You
    can skip this step if you’re not compiling HTML emails).

3.  Before we install the node modules, we need to create a directory for them and symlink to it from the ThinkUp root
    directory. This isn't standard Node behavior, but it's the only way our tests will work. So, anywhere you'd like
    (though we recommend in the directory adjacent to ThinkUp), create a directory called "thinkup_node_modules". Create a
    "node_modules"symlink to that directory from the root directory of your ThinkUp repository.

4.  Navigate to the root directory of your ThinkUp repo and run ``npm install``. This should install all of the NodeJS
    modules we use in ThinkUp.

5.  In order to run Grunt, you'll need the command line interface. Install that with ``npm install -g grunt-cli``.


Inlining CSS for HTML emails
----------------------------

HTML rendering in email clients is pretty poor, but it does pretty well if you use inline styles. Since it's a pain
to write your CSS rules inline, we don’t; we use software.

The precompiled HTML email template for our insights lives at
``extras/dev/precompiledtemplates/email/_email.insights_html.tpl`` and is written to
``webapp/plugins/insightsgenerator/view/_email.insights_html.tpl``.

Once you've gotten through the installation, using the inliner is incredibly easy.

1.  Make your changes to the precompiled template (location is listed above).
2.  From the root directory of your repository, run ``grunt html_email``. You can optionally run ``grunt watch`` and
    leave the process open as you work on your template. Everytime you save the source template, it will recompile.


Compiling LESS to CSS and CoffeeScript to Javascript
----------------------------------------------------

The key thing to understand is that ThinkUp CSS development is done in LESS, then compiled to CSS. This means working
directly with ThinkUp CSS files means your changes might be overwritten. The same is true for CoffeeScript
and Javascript.

Our LESS files live in ``extras/dev/assets/less/`` and get compiled to ``webapp/assets/css/``. Similarly, our
Coffeescript files live in ``extras/dev/assets/coffee/`` and get compiled to ``webapp/assets/js/``.

Just like inlining CSS for emails, whenever you save a LESS or Coffeescript file, you can run ``grunt less`` or
``grunt coffee``. You can also run ``grunt watch`` and it will compile the files automatically on save.
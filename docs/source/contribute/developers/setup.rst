Set Up Your Environment
=======================

Every developer has a text editor or IDE of choice, and you should use what you're most comfortable with while writing
code for ThinkUp. If you're on the lookout for a new PHP dev environment, `Eclipse PDT (PHP Development
Toolkit) <http://www.eclipse.org/pdt/downloads/>`_ is a free, cross-platform, full-featured PHP IDE that offers class
browsing, code formatting, and method completion. If you're using Eclipse, here's how to set it up for ThinkUp
development.

Set up Eclipse PDT to work with ThinkUp
---------------------------------------

- **Set indentation to spaces:** To easily generate code that complies
  with ThinkUp's indentation-by-spaces style (as per the :doc:`Code Style Guide </contribute/developers/writecode/styleguide/index>`), 
  in your project's preferences panel, under *PHP > Code
  Style > Formatter*, set the 'Tab Style' to spaces, and indentation size to 4. 

- **Add word wrap:** Insane but true: Eclipse does not support word
  wrap natively. `Use this experimental add-on to enable
  it <http://ahtik.com/blog/2006/06/18/first-alpha-of-eclipse-word-wrap-released/>`_
  (via `Stack
  Overflow <http://stackoverflow.com/questions/97663/how-can-i-get-word-wrap-to-work-in-eclipse-pdt-for-php-files>`_).
  Once the plugin is installed and you've restarted, right-click on a file
  and select 'Virtual word wrap' to enable it.

- **Show whitespace:** See whether you're using tabs or spaces; go to
  *Window > Customize Perspective* and under 'Editor Presentation', check off
  'Show Whitespace Characters.' A button will appear on your toolbar that
  you can press to show spaces and tabs.

- **Set your author name:** In *Preferences... > PHP > Code Style > Code
  Templates*, expand *Comments* and choose 'Types.' Click the edit button to
  set what the @author tag auto-fills when you're commenting your code.

- **Show a max line length ruler:** In 
  *Preferences > General > Editors > Text Editors*, check 'Show print margin' and
  enter the maximum line length (currently 120 as per the style guide).

- **Run regression tests in Eclipse:** Here's how to `install the
  SimpleTest
  plugin <http://www.thetricky.net/php/php-unit-testing-in-eclipse>`_ in
  Eclipse to run tests in the IDE. (Note from Gina: this plugin doesn't
  work on my Mac or PC; when I choose 'Run as > SimpleTest' nothing
  happens.)
  
- **Install Mylyn plugin for GitHub:** If you want to browse and update
  issues in Eclipse, install `this
  plugin <https://github.com/dgreen99/org.eclipse.mylyn.github/wiki>`_.

  - Server: `http://github.com/ginatrapani/ThinkUp <http://github.com/ginatrapani/ThinkUp>`_
  - Find your GitHub API Key in `Applications <https://github.com/settings/applications>`_ under **Account settings**.

Useful Keyboard Shortcuts
-------------------------

-  **Ctrl+Shift+F** to format your code
-  **Ctrl+/** to comment a block of code (hit it again to uncomment)
-  **Ctrl+O** to hop to member
-  **Ctrl+L** to go to line number
-  **Ctrl+Spacebar** for method completion
-  `More Eclipse keyboard
   shortcuts <http://www.rossenstoyanchev.org/write/prog/eclipse/eclipse3.html>`_
-  `Boosting your productivity in Eclipse using
   shortcuts <http://refactor.se/2007/07/05/boosting-you-productivity-in-eclipse-using-shortcuts/>`_

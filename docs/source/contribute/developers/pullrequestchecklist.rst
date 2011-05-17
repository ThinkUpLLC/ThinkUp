Pull Request Checklist
======================

Now that you've mastered working with ThinkUp and git, you've made
changes to the application code you want Gina to merge into the master
development tree. Awesome! We're thrilled to have you as a contributor.
This page lists a few things you should know.

To increase the chances of your contribution getting accepted into the
master development tree quickly and easily, before you issue a pull
request, make sure that:

#. **Your changes adhere to the ThinkUp coding standards.** Check out
   our :doc:`Code Style Guide </contribute/developers/writecode/styleguide/index>` for specifics on what your code
   should look like.
#. **Your code is thoroughly documented.** We use PHPDoc to
   auto-generate class documentation. Make sure all your classes and
   methods are documented using PHPDoc standards. Here's more on
   :doc:`ThinkUp and PHPDoc </contribute/developers/documentation>`.
#. **All existing unit tests pass.** Gina won't merge any code into the
   master development trunk that makes existing tests in
   ``/thinkup/tests/`` fail. Check out the `tests
   README <http://github.com/ginatrapani/thinkup/blob/master/tests/README.md>`_
   for more on how to set up, run, and write tests.
#. **You've added regression tests for your new code.** If you've fixed
   a bug, you should have added a test which fails in the current
   development tree, but passes in yours because of your fix. If you've
   added a new feature or new object methods or a new plugin, make sure
   you've also added thorough and complete tests that demonstrate that
   it works.
#. **You've rebased your work on the current state of the master
   development tree.** Help us keep ThinkUp's commit trees clean. `Use
   git-rebase <devfromsource.html#whats-git-rebase>`_
   to base your changes on the latest state of the development tree.
   This puts the onus of resolving conflicts that may have come up
   between the time you started your changes and the time you finished
   on you. This is a good thing, because you know better what your new
   code does as compared to the existing code than Gina does. `Here's
   how to use git-rebase before you issue a pull
   request <devfromsource.html#whats-git-rebase>`_.

General Guidelines for Code Commits
-----------------------------------

Help keep ThinkUp's commit history clean and descriptive. A few general
tips:

-  **Each commit should represent one type of change.** If you're
   working on re-formatting ThinkUp code, for example, don't commit the
   files you work on one at a time. Edit them all, then add them all to
   one commit, with the commit message “Code formatting.”
-  **Make your commit message as descriptive as possible.** Include as
   much information as you can. Explain anything that the file diffs
   themselves won't make apparent.
-  **Consolidate multiple commits into a single commit when you
   rebase.** If you've got several commits in your local repository that
   all have to do with a single change, you can `squash multiple commits
   into a single, clean, descriptive commit when using
   git-rebase <http://www.gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html>`_.
   When you do, good karma is yours.

Thanks for contributing to ThinkUp!
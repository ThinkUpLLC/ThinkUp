Develop from Source
===================

If you're a developer who wants to work on the ThinkUp source code and
submit your changes for consideration to be merged into the master
branch, here's how.

Quickfire Do's and Don't's
--------------------------

If you aren't familiar with git and GitHub, try reading the 
`ThinkUp Beginner's Guide <https://github.com/ginatrapani/ThinkUp/wiki/Developer-Guide%3A-ThinkUp-for-Beginners-by-a-Beginner>`_
and the `GitHub bootcamp documentation <http://help.github.com/#github_bootcamp>`_. If you're
familiar with git and GitHub, here's the short version of what you need
to know. Once you fork and download the ThinkUp code:

-  **Don't develop on the master branch.** Always create a development
   branch specific to `the
   issue <http://github.com/ginatrapani/thinkup/issues>`_ you're working
   on. Name it by issue # and description. For example, if you're
   working on Issue #100, a retweet bugfix, your development branch
   should be called 100-retweet-bugfix. If you decide to work on another
   issue mid-stream, create a new branch for that issue-don't work on
   both in one branch.

-  **Do not merge** the upstream master with your development branch;
   **rebase** your branch on top of the upstream master.

-  **A single development branch should represent changes related to a
   single issue.** If you decide to work on another issue, create
   another branch.

-  **Squash your commits.** After you rebase your work on top of the
   upstream master, you can squash multiple commits into one. Say, for
   instance, you've got three commits in related to Issue #100. Squash
   all three into one with the message "Issue #100 Description of the
   issue here." Gina won't accept pull requests for multiple commits
   related to a single issue; it's up to you to squash and clean your
   commit tree. (Remember, if you squash commits you've already pushed
   to GitHub, you won't be able to push that same branch again. Create a
   new local branch, squash, and push the new squashed branch.)

-  **Keep .gitignore clean.** Don't add test files to .gitignore that
   are specific to your ThinkUp setup. Only working config files, dot
   files, compiled view files, cache files, and logs should be listed in
   .gitignore.

Workflow Diagram
----------------

A visual representation of what a ThinkUp contributor's GitHub/git
workflow should look like. (Click to enlarge.)

.. image :: http://farm6.static.flickr.com/5103/5650265962_6bb7e8bf6d_o.png
   :width: 100%
   :target: http://www.flickr.com/photos/unruthless/5650265962/sizes/o/
   :alt: ThinkUp Workflow Diagram

Step-by-step (the short version)
--------------------------------

#. Fork on GitHub. (Click the Fork button.)
#. Clone to your server (``$ git clone git@github.com:you/ThinkUp.git``)
#. Set up remote upstream (``$ git remote add upstream git://github.com/ginatrapani/ThinkUp.git``)
#. Run any outstanding database migrations (``$ cd install/cli/; php upgrade.php --with-new-sql``)
#. Branch for new issue (``$ git branch ###-description; git checkout ###-description``) and develop on issue branch. 
#. As time passes, the upstream ThinkUp repository accumulates new commits. Keep your working copy's master branch
   and issue branch up to date by periodically rebasing: fetch upstream, rebase master, rebase issue branch.
#. When development is complete, rebase one more time, then branch from dev branch to release candidate branch. Squash 
   all X commits that pertain to the issue into one clean, descriptive commit (``$ git rebase -i HEAD-X``)
#. Push release candidate branch to GitHub (``$ git push origin ###-description-rc``)
#. Issue pull request on GitHub. (Click the Pull Request button.)

If you're new to git and GitHub, here's the longer version of these
instructions.

Fork the Repository to Contribute Code
--------------------------------------

Here's how to fork the ThinkUp repository to begin working on it.

Create an account on GitHub and establish connectivity between your
GitHub account and your hosting server.

#. Create a free account on GitHub.

#. Fork the project from
   `ginatrapani/thinkup <http://github.com/ginatrapani/thinkup>`_

#. Make sure you've got an SSH public key created on your server and
   recorded in your GitHub account. You can see your SSH Public Keys on
   the Account Overview section of your github account. Here's a `good
   guide <http://github.com/guides/providing-your-ssh-key>`_.

#. To test the GitHub authentication run

   ::

     $ ssh git@github.com

Clone your GitHub fork to your development server and install ThinkUp.

#.  Create a directory on your development server outside your web root (probably one level up) called thinkup and cd
    into that directory.

#.  Run a clone command against your github fork. It will look something like this except that it will use your GitHub
    account name in the place of dash30

    ::

      $ git clone git@github.com:dash30/ThinkUp.git

That will download all your forked GitHub files to a git repository on
your development server. If you have problems, check the permissions on
the newly created thinkup directory on your server.

Install a running instance of ThinkUp on your development server using
any of a number of installation guides.

Running nightly code from ThinkUp's  git repository will require you to :doc:`catch up on necessary database 
migrations </install/fromsource>`.

Create an Issue-Specific Development Branch
-------------------------------------------

Before you start working on a new feature or bugfix, create a new branch
dedicated to that one change named by issue number and description. If
you're working on Issue #100, a retweet bugfix, create a new branch with
the issue number and description, like this:

::

    $ git branch 100-retweet-bugfix
    $ git checkout 100-retweet-bugfix

Edit and test the files on your development server. When you've got
something the way you want and established that it works, commit the
changes to your branch on your development server's git repo.

::

    $ git add <filename>
    $ git commit -m 'Issue #100: Some kind of descriptive message'

You'll need to use git add for each file that you created or modified.
There are ways to add multiple files, but I highly recommend a more
deliberate approach unless you know what you're doing.

Then, you can push your new branch to GitHub, like this (replace
100-retweet-bugfix with your branch name):

::

    $ git push origin 100-retweet-bugfix

You should be able to log into your GitHub account, switch to the
branch, and see that your changes have been committed. Then click the
Pull button to request that your commits get merged into the ThinkUp
development trunk.

**IMPORTANT**: Before you issue a pull request, make sure it gets
accepted by running through the [[Developer Guide: Pull Request
Checklist]] first.

Keep Your Repository Up to Date
-------------------------------

In order to get the latest updates from the development trunk do a
one-time setup to establish the main GitHub repo as a remote by
entering:

::

    $ git remote add upstream git://github.com/ginatrapani/ThinkUp.git

Verify you've now got "origin" and "upstream" remotes by entering:

::

    $ git remote

You'll see upstream listed there.

Rebase Your Development Branch on the Latest Upstream
-----------------------------------------------------

To keep your development branch up to date, rebase your changes on top
of the current state of the upstream master. See the **What's
git-rebase?** section below to learn more about rebasing.

If you've set up an upstream branch as detailed above, and a development
branch called 100-retweet-bugfix, you'd update upstream, update your
local master, and rebase your branch from it like so:

::

    $ git fetch upstream
    $ git checkout master
    $ git rebase upstream/master
    $ git checkout 100-retweet-bugfix
    [make sure all is committed as necessary in branch]
    $ git rebase master

You may need to resolve conflicts that occur when a file on the
development trunk and one of your files have both been changed. Edit
each file to resolve the differences, then commit the fixes to your
development server repo and test. Each file will need to be "added"
before running a "commit."

Conflicts are clearly marked in the code files. Make sure to take time
in determining what version of the conflict you want to keep and what
you want to discard.

::

    $ git add <filename>
    $ git commit

To push the updates to your GitHub repo, replace 100-retweet-bugfix with
your branch name and run:

::

    $ git push origin 100-retweet-bugfix

Squash All Commits Related to a Single Issue into a Single Commit
-----------------------------------------------------------------

Once you have rebased your work on top of the latest state of the
upstream master, you may have several commits related to the issue you
were working on. Once everything is done, squash them into a single
commit with a descriptive message, like "Issue #100: Retweet bugfix."

To squash four commits into one, do the following:

::

    $ git rebase -i HEAD-4

In the text editor that comes up, replace the words "pick" with "squash"
next to the commits you want to squash into the commit before it. Save
and close the editor, and git will combine the "squash"'ed commits with
the one before it. Git will then give you the opportunity to change your
commit message to something like, "Issue #100: Fixed retweet bug."

Important: If you've already pushed commits to GitHub, and then squash them locally, you will not be able to push that same branch to GitHub again.
Create a new branch-like 100-retweet-bug-squashed or 100-retweet-bug-rc1
(for release candidate 1)-and squash your commits there. Once everything
is squashed and ready, push the new squashed branch to GitHub and send
your pull request to Gina.

Helpful hint: You can always edit your last commit message by using:

::

    $ git commit --amend

Some gotchas
------------

Be careful not to commit any of your configuration files, logs, or throwaway test files to your GitHub repo.
These files can contain information you wouldn't want publicly viewable
and they will make it impossible to merge your contributions into the
main development trunk of ThinkUp.

Most of these special files are listed in the .gitignore file and won't
be included in any commit, but you should carefully review the files you
have modified and added before staging them and committing them to your
repo. The git status command will display detailed information about any
new files, modifications and staged.

::

    $ git status

One thing you do not want to do is to issue a git commit with the -a option. This automatically stages and commits every modified file that's not expressly defined in .gitignore, including your crawler logs.

::

    $ git commit -a

What's git-rebase?
------------------

Using git-rebase helps create clean commit trees and makes keeping your
code up-to-date with the current state of the upstream master easy.
Here's how it works.

Let's say you're working on Issue #212 a new plugin in your own branch
and you start with something like this:

::

              1---2---3 #212-my-new-plugin
             /
        A---B #master

You keep coding for a few days and then pull the latest upstream stuff
and you end up like this:

::

              1---2---3 #212-my-new-plugin
             /
        A---B--C--D--E--F #master

So all these new things (C,D,..F) have happened since you started.
Normally you would just keep going (let's say you're not finished with
the plugin yet) and then deal with a merge later on, which becomes a
commit, which get moved upstream and ends up grafted on the tree
forever.

A cleaner way to do this is to use rebase to essentially rewrite your
commits as if you had started at point F instead of point B. So just do:

::

    git rebase master 212-my-new-plugin

git will rewrite your commits like this:

::

                          1---2---3 #212-my-new-plugin
                         /
        A---B--C--D--E--F #master

It's as if you had just started your branch. One immediate advantage you
get is that you can test your branch now to see if C, D, E, or F had any
impact on your code (you don't need to wait until you're finished with
your plugin and merge to find this out). And, since you can keep doing
this over and over again as you develop your plugin, at the end your
merge will just be a fast-forward (in other words no merge at all).

So when you're ready to send the new plugin upstream, you do one last
rebase, test, and then merge (which is really no merge at all) and send
out your pull request. Then in most cases, Gina has a simple fast
forward on her end (or at worst a very small rebase or merge) and over
time that adds up to a simpler tree.

More info on the git man page here:
`Git rebase: man
page <http://www.kernel.org/pub/software/scm/git/docs/git-rebase.html>`_


TODO

git pull

reference how to upgrade/manually run db migrations

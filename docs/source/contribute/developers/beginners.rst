ThinkUp for Beginners by a Beginner
====================================================

For a beginner developer, finding an open source project to work on can
be difficult and daunting. Lots of the projects you know and love are
already well established with large communities of developers and their
own best practices that might seem alien to you. This is where ThinkUp
starts to look really good. ThinkUp has a small developer base (at the
time of writing this, `26 developers
total <https://github.com/ginatrapani/ThinkUp/contributors>`_) and a well
designed and executed development process.

I'm going to try and walk you through all of the things you need to know
before diving into the code. I am a beginner developer myself and have
been contributing to ThinkUp for a few months so I know what it feels
like at first!

Who is this page aimed at?
--------------------------

This page is aimed at the beginners. People who have not done much open
source work or group coding and people unfamiliar with the ThinkUp code
base. Sections of this page may apply to some but not to others. That's
okay, you're free to skip over parts you feel comfortable with. For
example, if you're happy with how Git works then you can skip over that
part. If you know how the MVC design pattern works you're probably cool
to just breeze over that, too.

This page does, however, assume that you're familiar with object
orientation, inheritance, abstraction, version control and the purpose
of ThinkUp. If any of these concepts look alien to you, I recommend you
look them up before continuing.

Getting The Code and Understanding Open Source
----------------------------------------------

First things first, you need to get yourself a copy of the code. I won't
go through this because there is already a fantastic guide written for
it which can be found
:doc:`here </contribute/developers/devfromsource>`.
I will make a few notes for beginners, though.

When I first started developing for ThinkUp the scariest thing was the
version control. I had never used Git before and it was totally alien to
me (parts of it still are, but that's for the next section). I also
didn't understand how open source software was developed so here is a
brief run down for you.

You may be wondering: How does open source software get developed with
lots of people making changes at the same time without descending into
total chaos? The answer is very simple: version control and branching.
Examples of version control packages are Subversion, Mercurial, CVS and,
of course, Git. A branch is simply a part of the project that is being
developed on by someone. For example, one of my branches is
"429-wordpress-plugin-cleanup", the 429 is the issue number related to
it and the rest is a description of what I'm doing on the branch.

If you're hopelessly confused as to what version control software is,
it's quite easy to understand. It's literally just some software that
helps you manage source code by keeping numerous copies of files so that
you can roll back to certain points in time. It also allows you to
create branches of code which are just exact copies of the code that you
can develop on and, if you no longer like what you're doing, you can
delete the branch safely without affect the main code base.

A lot of open source software uses the branch model and the way it works
is that there is a branch called "master" which is the main branch of
the project (sometimes this is called the "trunk"). All changes that are
made will eventually end up in there (provided they pass review). You,
as a developer, create your own branch of development, write your own
code on it and, when you think it's ready, you submit it to Gina for
review and she provides you with feedback if changes need to be made. If
it's all good and doesn't need changing, Gina will merge your changes
into the master branch and you'll end up on the Contributor list. Cool,
huh?

Version (difficult-to) Control with Git
---------------------------------------

While version control sounds like a fantastic idea, it is the opposite
of beginner friendly. It is best to do it from the command line which
sounds scary if you haven't used the command line before but once you
get used to it, it's really not that bad. I'm assuming at this point
that you've read the :doc:`guide to getting the ThinkUp source
code </contribute/developers/devfromsource>`
and know some basic command line skills such as navigating directories.
I'm also assuming that you have navigated to the ThinkUp directory as
none of the following commands will work unless you're in the directory
you fetched the Git source code to.

The key to developing on ThinkUp is branching. All of the commits you do
will go onto your own branch that you create. What you have to do is
create a branch, commit changes to it, send it to Gina. Here's how you
do it.

Creating a branch is easy but there are a few checks you need to do
before you do it. Most of your branches will branch off from the
"master" branch. When you create a branch in git via the command line,
the branch is created as an off-shoot from the branch you're currently
on. This may or may not be the master branch. To check, use the
``git status`` command. This will display the branch you are on and what
changes exist.

So make sure you're on the master branch using the ``git status``
command. In the event that you aren't on the master branch, execute the
following command:

``git checkout master``

And that should switch you to the master branch (if you want to make
sure, you know how!). Branches usually correspond to issues from the
`issues list <https://github.com/ginatrapani/ThinkUp/issues>`_. Every
issue has a number and this number is what the name of your branch would
start with. For example, if you wanted to tackle `issue 338, broken
links when install directory has a space in
it <https://github.com/ginatrapani/ThinkUp/issues/labels/_minor#issue/338>`_
you would create your branch like this:

``git branch 338-fix-broken-link-when-install-dir-contains-space``

Or something along those lines. Then you need to checkout (switch to)
that branch to start work on it.

``git checkout 338-fix-broken-link-when-install-dir-contains-space``

Now you're ready. If you do some work on the code and you're *not* on
the right branch don't worry, you can just checkout the branch you were
meant to be working on and everything will be fine. The problem arises
(and I assure you everyone has done this at one point) when you commit
to the wrong branch but first we need to cover commits.

What's a commit? When you've written some code and you get to a point
where you feel you're done or you've got to a point where you're happy
and ready to have the code looked at by other people you do what's
called "committing". This saves the changes you've made to the branch
along with a little message that explains what you've done. Before you
commit you need to "add" files to the commit. If you do a ``git status``
after making changes to the code, you should see a list of files that
you've edited with "modified" to their left and under the heading
"Changed but not updated". These files need to be added to what's called
the "stage", to demonstrate this let's imagine we edited the
"class.AppConfig.php" file in the "model" folder. Listed in our modified
files would be ``webapp/_lib/model/class.AppConfig.php``, so let's add
that to the "stage":

``git add webapp/_lib/model/class.AppConfig.php``

That should do it. Now when you do ``git status`` the file should be
listed under "Changes to be committed". This means that when you commit,
this file will be part of the changes you submit to the main ThinkUp
project. Make sure they work! When you're ready:

``git commit -m "Issue 338: Fixed the broken flux capacitor."``

Obviously your commit message won't include references to time travel
but rather references to what the commit achieves. Now, what if you file
your commit only to realise you accidentally did it on the master
branch? Not to worry! There's a command for that:

``git reset --soft HEAD^``

That command will undo the last commit on the current branch in "soft"
mode, meaning that all of the changes get put back into the "changes to
be committed" list. You may be thinking, what's all this HEAD lark? And
why is there a caret after it? It's easy to explain: HEAD is a keyword
that refers to the latest commit on a given branch. The caret after
means the commit before the HEAD commit. 2 caret characters would mean 2
commits before HEAD, 3 carets would mean 3 and so on. I think this ends
at 3 because if you want to go back 4 commits you would do this:

``git reset --soft HEAD~4``

The tilde symbol ~ with a number after it means go back that number of
commits. There are other parameters you can pass to reset but I've never
actually had to use them before. One of them is --hard which sounds
pretty brutal. I have no idea what it does. When I do I'll be sure to
add it to this guide.

More or less everything else you need to know about Git with ThinkUp can
be found in the brilliant :doc:`Getting the Source Code and Keeping it Up to
Date </contribute/developers/devfromsource>`
developer's guide. it covers thinks like squashing commits (which is on
the :doc:`pull request
checklist </contribute/developers/pullrequestchecklist>`),
rebasing (which makes life easier for just about everyone) and adding
remote Git repositories.

One last thing before I move on to the next section. You might be
wondering how to send your changes to Gina. The way this is done is
through a process called "pulling". What this means is that once you've
finished writing your code and it passes all of the pull request
checklist items, you send a "pull request" to ask Gina to merge your
code into the master branch. This then opens a kind of thread and alerts
Gina that a new pull request has been issued, she looks at this thread
(which contains a nice diff of all of the changes you've made) and
she'll either make comments on how to improve it if she feels there's
more to be done or she'll merge it if she thinks it's all good. But how
is this done?

First, you need to push your changes to GitHub (after you've finished
all of your code and rebasing etc.). Doing this is quite simple:

``git push origin 338-fix-broken-link-when-install-dir-contains-space``

You can replace the
"338-fix-broken-link-when-install-dir-contains-space" with the name of
your branch. Leaving that argument blank will push the master branch to
GitHub which isn't what we want. "Origin" refers to the place that you
downloaded the Git source from (you can see this by executing a
``git remote -v`` command). Then your new branch will appear in your
ThinkUp fork on your profile. To navigate to it, go to your ThinkUp fork
on your profile, mouse over "Switch Branches" in the top left and select
the branch you just pushed from the drop down. When you've loaded the
branch page, click on "Pull Request" in the top right and you'll be
taken to a page where you can type up the changes you have made and
anything you feel Gina should know about your new branch.

That's about all I can say about Git. This section got pretty long
pretty fast but it's all stuff that's well worth knowing. If you do get
stuck on anything, the mailing list or IRC channel would be more than
happy to try and help you out as best they can. There are tons of guides
out there for Git, too. I recommend you take a peek at one or two if
you're still not quite understanding what's going on.

Phew! That was a lot of reading. If you're feeling confident and want to
learn some more nitty gritty stuff about Git, there are some fantastic
screencasts at `GitCasts <http://gitcasts.com/>`_.

Test Driven Development
-----------------------

ThinkUp follows a "test driven development" model. It's less scary than
it sounds. Essentially all that it means is for every bit of code that
is written, there is a corresponding test to make sure it works. For
example, my first commit on this project was modifying the installer
process so you don't need a database set up before installing it. The
installer would check if the database existed and create it if it
didn't.

Along with this code I needed to write tests for all possible test
cases: existing database names, insufficient privileges, invalid
database names, invalid database log in credentials, the works. But
because these tests exist, the project is far easier to maintain. Every
change that is made has the potential to break another part of the code
base and the unit tests (the tests are called unit tests) will highlight
what part is broken.

If you've used JUnit in Java or any other unit testing package in any
other language they all follow more or less the same syntax and
ideology. ThinkUp already has a good guide on :doc:`how to write unit
tests </contribute/developers/writecode/unittests>`
but I'll highlight the basic idea:

You create a test case. For example, with the installer I would test
making a connection to the database with false credentials (I'm fairly
sure I used "localcheese" instead of "localhost" as the database
server). Then I would analyse the page that returned from that to search
for a specific error string. For some simple example tests, check out
the TestOfConfig.php in the tests/ directory.

Model View Controller... what?
------------------------------

Yeah, it was a pretty alien concept to me when I started, too. It makes
a lot of sense, though, and you'll come to love it in time. ThinkUp has
a brief page on their :doc:`movel-view-controller
implementation </contribute/developers/mvc>`
which links off to the Wikipedia page on MVC which isn't very helpful to
a new developer so I'll do my best to explain.

The basic principal behind MVC is separating programming logic from the
design and user interface. The models handle programming logic (the PHP
part) and the views handle the user interface (the HTML part). The
controllers handle deciding which models to use with which views and
what information should be sent to the views.

The views are the part that really interested me. They're Smarty .tpl
files which are really good at combining simple PHP with HTML. Here's a
really cool `crash course <http://www.smarty.net/crash_course>`_ in how
to use Smarty .tpl files. (Note: In that example they use the assign()
method to send data to .tpl files but as far as my ThinkUp development
goes, I've only seen the addToView() method used. They seem to do the
same thing.)

The purpose of the controllers is to use the models to generate data to
send to the views. Every exposed page (page that you access in ThinkUp
such as index.php, install.php etc.) is generated by calling the go()
method of a controller. That go() method will handle all the programming
logic and send a bunch of key-value pairs to a .tpl file which will be
parsed, the PHP in it will be evaluated, and it will be displayed on the
page. Elegant separation of all programming logic from user interface.
As a rule, there is no HTML in any file apart from .tpl files (though
there is the occasional exception).

Getting stuck in
----------------

Getting started writing code is difficult. You first need to read and
understand the existing code surrounding the issue you want to work on.
I recommend picking a fairly simple issue, something that shouldn't take
too much knowledge of the code base because it's very easy to get
overwhelmed by the amount of code there is.

The first issue I worked on was to do with the install process. ThinkUp
used to require you to have an existing database already set up and
waiting for the installer to populate it with the ThinkUp tables. If the
database you typed in during the installation did not exist, the install
failed. I wrote code that created the database if it did not exist. On
the surface, not too much of a challenge, however, this small issue
still required a lot of time and effort to code due to the fact it was
my first look at the code and I had no knowledge of the code base.

Reading code is as much of a skill as writing code is. I will not lie to
you, it isn't easy. Every line, every method call and every variable
needs your attention and needs you to understand what it's doing. This
process will take you a while and I recommend you dedicate large blocks
of time to it. It's not something that can be achieved in half an hour.

Don't let me put you off. In time, you will be able to read and
understand code with relative ease. Like most skills, practice makes
perfect and you will be up and running before you know it.

You're talking a different language, I'm still confused!
--------------------------------------------------------

That's totally okay. ThinkUp has both a mailing list and IRC channel for
this exact scenario! We have an IRC channel and a mailing list, details for
both can be found :doc:`here </contact>`. You
may not get a reply straight away but anyone who sees your query will
endeavor to help you as much as they can!
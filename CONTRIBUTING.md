Thanks for wanting to contribute!

Below is a quick run down on the workflow, dos & don'ts, and a checklist for developers
wanting to contribute to ThinkUp.

## Contributor workflow

This is a quick summary, but you can read the full version at
[Develop from Source - ThinkUp Documentation](http://thinkupapp.com/docs/contribute/developers/devfromsource.html)

1. Fork the [project from GitHub](https://github.com/ginatrapani/ThinkUp/)

2. Clone the fork to your server

        $ git clone git@github.com:username/ThinkUp.git

3. Set up a remote upstream so that you can keep up-to-date with the source repo changes

        $ git remote add upstream git://github.com/ginatrapani/ThinkUp.git

    You can verify you've correctly set up the remote by running `$ git remote -v` to
    list the names and their URIs.

4. Create a specific feature branch to develop on

        $ git checkout -b ###-description-of-feature-or-bugfix

    `###` will refer to an open issue ticket on GitHub.

5. Edit and test the changes on your development server

6. Rebase (and fix any conflicts) against the upstream master branch

        $ git fetch upstream
        $ git checkout master
        $ git rebase upstream/master
        $ git checkout ###-description
        [make sure all is committed as necessary in branch]
        $ git rebase master

    Get in the habit of continually rebasing your fork against the upstream.

7. Squash all X commits that pertain to the issue into one clean, descriptive commit

        $ git rebase -i HEAD-X

8. Push the release candidate branch to GitHub

        $ git push origin ###-description-rc

9. Issue pull request on GitHub.


## Pull request checklist

To increase the chances your contribution will be accepted into the master branch, make sure:

- Changes adhere to the ThinkUp coding standards - Check the
[Code Style Guide](http://thinkupapp.com/docs/contribute/developers/writecode/styleguide/index.html)
on what it should look like.

- Code is thoroughly documented - Make sure your classes and methods follow PHPDoc standards.

- All existing unit tests pass - Any code that makes existing tests in `/tests` fail will not be
merged. Read the [tests README](http://github.com/ginatrapani/thinkup/blob/master/tests/README.md)
for more on how to set up, run, and write tests.

- Add regression tests for new code - If you've fixed a bug, add a test that fails in the
current development tree, but passes due to your fix. If it's a new feature, object method
or plugin, make sure you've also added thorough and complete tests that demonstrate that
it works.

- Rebase your work against the current state of the development tree - Help keep the
ThinkUp commit trees clean.


## Dos and don'ts

- Don't develop on the master branch - Always create a development branch specific to
the issue
- Don't merge the upstream master with your development branch - Rebase your branch on top
of master
- Do create branches for each issue you are working on
- Do squash multiple commits for a clean commit history and before pushing
- Do keep .gitignore clean - Don't add test files that are specific to your setup.
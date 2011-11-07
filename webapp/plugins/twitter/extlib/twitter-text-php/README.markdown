# Twitter Text (PHP Edition) #

A library of PHP classes that provide auto-linking and extraction of usernames,
lists, hashtags and URLs from tweets.  Originally created from twitter-text-rb
and twitter-text-java projects by Matt Sanford and ported to PHP by Mike
Cochrane, this library has been improved and made more complete by Nick Pope.

## Features ##

### Autolink ##

 - Add links to all matching Twitter usernames (no account verification).
 - Add links to all user lists (of the form @username/list-name).
 - Add links to all valid hashtags.
 - Add links to all URLs.
 - Support for international character sets.

### Extractor ###

 - Extract mentioned Twitter usernames (from anywhere in the tweet).
 - Extract replied to Twitter usernames (from start of the tweet).
 - Extract all user lists (of the form @username/list-name).
 - Extract all valid hashtags.
 - Extract all URLs.
 - Support for international character sets.

### Hit Highlighter ###

 - Highlight text specifed by a range by surrounding with a tag.
 - Support for highlighting when tweet has already been autolinked.
 - Support for international character sets.

### Validation ###

 - Validate different twitter text elements.
 - Support for international character sets.

## Examples ##

For examples, please see `tests/example.php` which you can view in a browser or
run from the command line.

## Conformance ##

You'll need the test data which is in YAML format from the following
repository:

    https://github.com/twitter/twitter-text-conformance

It has already been added as a git submodule so you should just need to run:

    git submodule init
    git submodule update

As PHP has no native support for YAML you'll need to checkout spyc from svn
into `tests/spyc`:

    svn checkout https://spyc.googlecode.com/svn/trunk/ tests/spyc

There are a couple of options for testing conformance:

1. Run `phpunit` in from the root folder of the project.
2. Run `tests/runtests.php` from the command line.
3. Make `tests/runtests.php` accessible on a web server and view it in your
   browser.

## Thanks & Contributions ##

The bulk of this library is from the heroic efforts of:

 - Matt Sanford (https://github.com/mzsanford): For the orignal Ruby and Java implementions.
 - Mike Cochrane (https://github.com/mikenz): For the initial PHP code.
 - Nick Pope (https://github.com/ngnpope): For the bulk of the maintenance work to date.

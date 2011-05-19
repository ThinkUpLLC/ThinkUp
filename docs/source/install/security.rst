ThinkUp Security and Data Privacy
=================================

The ThinkUp development team takes security and data privacy very seriously. This document describes what data ThinkUp
stores, what security measures the application puts in place to protect that data, what you can do to keep your ThinkUp
installation secure, and how to report potential security and privacy bugs in the software.

What Data ThinkUp Stores
------------------------

ThinkUp does store:

* ThinkUp user email addresses and encrypted ThinkUp account passwords
* API keys to access social networks and other web services
* Social network authorization (OAuth) keys
* Public and private posts on social networks
* Public and private user data on social networks

ThinkUp does not store:

* Passwords to log into social networks
* Direct messages or private messages on social networks

How ThinkUp Handles Private Data
--------------------------------

ThinkUp's official distribution adheres to a set of rules and standards for handling sensitive data, such as:

**Passwords.** The only password that ThinkUp stores in its database is each user's ThinkUp account password. This
password is not stored in clear text; ThinkUp stores a salted `SHA-1 <http://en.wikipedia.org/wiki/SHA1>`_ hash
of the password. To prevent brute force attacks which attempt to guess this password, ThinkUp enforces a 
:doc:`failed login attempt cap </userguide/accounts/index>`.

**Social network credentials.** ThinkUp and its core plugins do not store passwords to social networks like Facebook
or Twitter. Instead, ThinkUp stores OAuth credentials to access these networks. This gives users the ability to easily
revoke ThinkUp's access to their data on the originating network's settings.

**Private post and user details.** While ThinkUp collects private posts and data its authorized users have access to on
the originating network, ThinkUp does not make those posts available to anyone not logged into ThinkUp.

**Facebook data assumptions.** ThinkUp's current Facebook support is a work in progress and Facebook's access
permissions system is complex. As such, ThinkUp marks all posts to a Facebook user's profile private; ThinkUp marks
all posts to a Facebook page as public. ThinkUp assumes all Facebook users are private.

Only plugins which adhere to these standards will be accepted into the official ThinkUp distribution.

.. warning::
    If you install third-party plugins which are not included in the ThinkUp distribution, you are taking the risk
    that they don't adhere to these guidelines.

How to Secure Your ThinkUp Installation
---------------------------------------

Since users install ThinkUp on their own web servers, there are a number of security measures a ThinkUp administrator
can take to secure the application and the data it stores.

The ThinkUp development team strongly urges all users to:

**Use strong, unique passwords** for your ThinkUp user account as well as all your social network accounts.

**Use an encrypted connection.** Run ThinkUp on a web server with https/SSL or only access your ThinkUp installation
through a VPN or secure proxy, so that no one can "sniff" your ThinkUp password when you log in.

**Limit your MySQL user access** to ONLY your ThinkUp database. Never use 'root' or a database user with unlimited
access permissions to all your MySQL databases. Set up a ThinkUp-specific database user which can only access your
ThinkUp database, not any others.

**Make sure no ThinkUp files are writable** except the ones :doc:`required by the application </install/perms>`.

How to Report a Security Bug
----------------------------

If you find a security bug in ThinkUp, send an email with a descriptive subject line to 
**thinkup-security[at]expertlabs.org**. If you think you've found a serious vulnerability, please do not file a public
issue or post to ThinkUp's public mailing lists.

Your report will go to the core ThinkUp development team. You will receive acknowledgement of the report in 24-48
hours, and what our next steps will be to release a fix. If you don't get a report acknowledgement in 48 hours,
`contact Gina Trapani <http://www.google.com/profiles/u/0/ginatrapani/contactme>`_ or 
`Anil Dash <http://dashes.com/anil>`_ directly.

A working list of public, `known security-related issues can be found in the issue
tracker <https://github.com/ginatrapani/ThinkUp/issues?labels=security>`_.

Thanks for your help.
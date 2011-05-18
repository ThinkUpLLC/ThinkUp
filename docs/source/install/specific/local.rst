Installing ThinkUp Locally
==========================

ThinkUp works most smoothly on a public web server, but it’s best not to
develop on or expose alpha code to the public web.

The Problem: Public Authorization URLs
--------------------------------------

The Twitter plugin requires a public URL to OAuth-authorize your ThinkUp
installation. If you’re installing ThinkUp on a local computer that’s
behind a firewall, your ThinkUp callback URL won’t work because it’s not
reachable on a public web server.

The Workaround Solution: Redirect from a Public Page
----------------------------------------------------

A page on your public web server can redirect Twitter’s OAuth token to
your local computer. If you’ve got a local ThinkUp installation, save
`this one page <http://gist.github.com/588936>`_ on your public web
server and edit it to redirect to your local instance.

::

    <?php
        header( 'Location: http://localhost/thinkup/webapp/plugins/twitter/auth.php?oauth_token='.$_GET['oauth_token'] ) ;
        // http://localhost/thinkup is the URL to access thinkup on your local server.
    ?>

`Relevant mailing list
thread <http://groups.google.com/group/thinkupapp/browse_thread/thread/94cb157df7ed1169/fe069f976189ba6a>`_
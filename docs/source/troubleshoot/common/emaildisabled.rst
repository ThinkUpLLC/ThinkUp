My web server cannot send email
===============================

We strongly recommend running ThinkUp on a web server which can send email.

If your web host is unable to send email via `PHP's mail function <http://php.net/manual/en/function.mail.php>`_, 
several ThinkUp functions are affected: 

* You won't receive the initial account activation email during ThinkUp installation
* New users will not receive account activation email when they fill out the registration form
* You won't receive the authorization link to upgrade your ThinkUp installation when updating the application
* Users won't receive an email to reset their password when using the "Forgot Password" link

You can manually activate user accounts by setting the is_activated field equal to 1 in ThinkUp's owners table. Here's
how to :doc:`directly access your ThinkUp database to make changes to it </troubleshoot/common/advanced/directdb>`.
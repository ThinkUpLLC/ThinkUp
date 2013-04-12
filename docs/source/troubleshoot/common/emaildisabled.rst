Didn't receive an account activation email
==========================================

If you didn't receive an account activation email to the address you entered during installation, double-check your
spam folder. If the email didn't arrive, check with your hosting provider about whether or not the server is able to 
send email via `PHP's mail function <http://php.net/manual/en/function.mail.php>`_.

ThinkUp must run on a web server which can send email.

If your web host is unable to send email via `PHP's mail function <http://php.net/manual/en/function.mail.php>`_, 
several ThinkUp functions are affected: 

* You won't receive the initial account activation email during ThinkUp installation
* You won't receive the authorization link to upgrade your ThinkUp installation when updating the application
* Users won't receive an email to reset their password when using the "Forgot Password" link
* New users will not receive an account activation email when they fill out the registration form
* You won't get other important email alerts, like when your Facebook connection expires.

ThinkUp will continue to add email notifications to the application in the future.

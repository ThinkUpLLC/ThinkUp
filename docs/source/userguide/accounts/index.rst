ThinkUp Users
=============

To use ThinkUp, first you must create a ThinkUp user account either during installation or using the registration
form. 

Create an Account
-----------------

To create a new ThinkUp user account, in ThinkUp's status bar click on the "Log In" link. Then click on "Register." 
If the installation administrator :doc:`has opened registration to new users </userguide/settings/application>`, fill
out the form and click on the "Register" button to create a new user account. You will receive an email with a link to
activate your new account. (Note that if 
:doc:`the web server is unable to send email </troubleshoot/common/emaildisabled>`, an
administrator will have to activate your new account manually.)

If the installation administrator has not opened registration to new users, when you click on the Register link, you
will see the :doc:`Sorry, registration is closed on this ThinkUp installation </troubleshoot/messages/regclosed>`
message.

Log In to ThinkUp
-----------------

To log in to ThinkUp, click on the "Log In" link on the right side of ThinkUp's status bar. Then, enter the email
address and password you used when you created your user account and click on the "Log In" button.

.. admonition:: On the Roadmap

    You must log in every time you visit ThinkUp. We hope to offer a "Remember me" checkbox on the log in page soon.

To log out of ThinkUp, click on the "Log out" link on the right side of ThinkUp's status bar when you're logged in.

Login Lockout
-------------

You can only log in to ThinkUp if you provide the correct email address and password, and your user account is
activated.

To prevent brute force attacks which attempt to guess a user's ThinkUp password, ThinkUp enforces a failed login
attempt cap. After 10 failed login attempts, ThinkUp deactivates the user account. An administrator must reactivate
it. (If the administrator account is deactivated, you can 
:doc:`manually reactivate it </troubleshoot/common/admindeactivated>`.)

Forgot Password
---------------

If you've forgotten your ThinkUp password, on the Log In page, click on "Forgot password." Enter the email address
associated with your user account and click on the "Send Reset" button. If 
:doc:`the web server is able</troubleshoot/common/emaildisabled>`, ThinkUp will send you an email which contains a 
link to reset your password.

User Permissions
----------------

Currently there are two levels of user permissions in ThinkUp: user-level and administrator-level permissions.

*   **Users** cannot enable, disable, or configure plugins, or see a list of users for a given ThinkUp installation.
*   **Administrators** can enable, disable, and configure plugins, see a list of users on a given ThinkUp installation,
    see all the views for social media accounts registered on the installation, deactivate ThinkUp user accounts, and
    configure global application settings like whether or not registration is open to new users.

Every ThinkUp installation must have at least one administrator account. A ThinkUp installation can have any 
number of user accounts, and it may have multiple administrator accounts.

When an administrator with the email address user@example.com is logged into ThinkUp, the text on the right side of
ThinkUp's status bar reads "Logged in as admin: user@example.com." Otherwise, it simply reads 
"Logged in as: user@example.com."
Application (Administrators only)
=================================

Administrators can change application-level settings in ThinkUp. 

Default service user
--------------------

Choose the public service user which will appear by default when a non-logged in user visits the ThinkUp dashboard.
By default, this is set to the last updated service user, that is, the service user which was last crawled 
successfully.

If you set this to a public service user which becomes private, this setting will fall back to its default, the last
updated service user.

Open registration to new ThinkUp users
--------------------------------------

Allow new users to register for accounts on your ThinkUp installation. When this box is checked, ThinkUp's register link
will present a registration form. Otherwise, ThinkUp displays a message that registration is closed. The default value
is registration closed (unchecked).

Enable Developer Log
--------------------

Check this box if you want to see the :doc:`verbose, unformatted developer
log </troubleshoot/common/advanced/crawlerlog>` on the "Capture Data" screen, instead of the quieter, formatted user log.
Once you change this setting, go back to the Dashboard and click on "Capture Data" to see the change in action.

Enable reCAPTCHA
----------------

Configure `reCAPTCHA <http://www.google.com/recaptcha>`_ in the ThinkUp user registration form. 

By default, ThinkUp generates a CAPTCHA image using the `GD <http://php.net/manual/en/book.image.php>`_ library. 
However, reCAPTCHA helps digitize books, and works without GD. To enable reCAPTCHA, get reCAPTCHA API keys, then 
check the Enable ReCAPTCHA box and enter the keys. 

If you do not have the `GD <http://php.net/manual/en/book.image.php>`_ library installed on your server, 
reCAPTCHA is a good alternative CAPTCHA solution.

Enable beta upgrades
--------------------

Get notified when there is a new ThinkUp beta version available, and have the option to upgrade to it using the 
web-based upgrader. **Proceed at your own risk!** ThinkUp betas are unstable versions for testers only. Some may
include database migrations that you must run manually (using ``$ cd install/cli/; php upgrade.php --with-new-sql``).

Disable the JSON API
--------------------

Check this box if you don't want to allow users or third-party applications access to public data via the 
:doc:`ThinkUp API </userguide/api/index>`. When this box is checked, every API request will get 
an :doc:`APIDisabledException </userguide/api/errors/apidisabled>`.

Disable thread embeds
---------------------

Check this box if you don't want to allow users to 
:doc:`embed ThinkUp threads on third-party web sites </userguide/listings/all/post_listings>` using a JavaScript
embed code. When this box is checked, the code will not be available for use.

Disable usage reporting
-----------------------

ThinkUp sends usage information to `thinkup.com <http://thinkup.com>`_ when it checks if there's
a new version available. Collecting this usage information enables ThinkUp's :doc:`core development team </core>` to
gain insight into what features are in use, and make data-informed decisions about how to improve the application.

The information collected about individual ThinkUp installations is not public; it is only available to ThinkUp's
:doc:`core development team </core>`. From time to time, the team may publish usage statistics in aggregate.

The usage information includes:

*    The location and version of the ThinkUp installation
*    How many and which service users have been added to the installation
*    The last time an administrator logged into the ThinkUp installation

Check this box to disable usage reporting on your ThinkUp installation.


Back Up and Export Data
------------------------

Click on the appropriate link to :doc:`back up or export data from your ThinkUp installation</install/backup>`.
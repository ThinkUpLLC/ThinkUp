Application (Administrators only)
=================================

Administrators can change application-level settings in ThinkUp. 

Open registration
-----------------

Allow new users to register for accounts on your ThinkUp installation. When this box is checked, ThinkUp's register link
will present a registration form. Otherwise, ThinkUp displays a message that registration is closed. The default value
is registration closed (unchecked).

Enable reCAPTCHA
----------------

Configure `reCAPTCHA <http://www.google.com/recaptcha>`_ in the ThinkUp user registration form. 

By default, ThinkUp generates a CAPTCHA image using the `GD <http://php.net/manual/en/book.image.php>`_ library. 
However, reCAPTCHA helps digitize books, and works without GD. To enable reCAPTCHA, get reCAPTCHA API keys, then 
check the Enable ReCAPTCHA box and enter the keys. 

If you do not have the `GD <http://php.net/manual/en/book.image.php>`_ library installed on your server, 
reCAPTCHA is a good alternative CAPTCHA solution.

Disable the JSON API
--------------------

Check this box if you don't want to allow users or third-party applications access to public data via the 
:doc:`ThinkUp API </userguide/api/index>`. When this box is checked, every API request will get 
an :doc:`APIDisabledException </userguide/api/errors/apidisabled>`.

Disable Thread Embeds
---------------------

Check this box if you don't want to allow users to 
:doc:`embed ThinkUp threads on third-party web sites </userguide/listings/all/post_listings>` using a JavaScript
embed code. When this box is checked, the code will not be available for use.

Default service user
--------------------

Choose the public service user which will appear by default when a non-logged in user visits the ThinkUp dashboard.
By default, this is set to the last updated service user, that is, the service user which was last crawled 
successfully.

If you set this to a public service user which becomes private, this setting will fall back to its default, the last
updated service user.
How to Add CSRF Protection to Application Actions
=================================================

ThinkUp's application code offers several helper methods for easily adding protection against `Cross-site request
forgery <http://en.wikipedia.org/wiki/CSRF>`_ attacks. These methods add a unique token to all requests which modify the
contents of the database. That token gets validated when ThinkUp receives the request, as per `The Open Web 
Application Security Project's recommendation 
<https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet>`_.

If you have added a controller which uses GET or POST variables to modify the database, you must use this CSRF token
protection.

Add a CSRF Token to a ThinkUp Request
-------------------------------------

In your controller, enable CSRF token support by disabling caching (i.e., ``$this->disableCaching();``) then
calling ``$this->enableCSRFToken();``.

Then you must add the CSRF token to your view in one of two ways, depending on what kind of request it is. If you
are using a web form, a Smarty modifier can generate a hidden input field with the name csrf_token in your
form. Do so by adding this to your view template file inside the form:

::

    {insert name="csrf_token"}

If you are simply making a request with $_GET URL parameters, add ``&csrf_token=" + window.csrf_token``
to your JavaScript-based request. 

See ``webapp/_lib/view/account.index.tpl`` as an example.

Validate the Incoming CSRF Token
--------------------------------

In the controller's block of code which validates the request inputs, call ``$this->validateCSRFToken();`` to ensure
the token is valid. If it isn't, the controller will throw an InvalidCSRFTokenException.

See ``webapp/_lib/controller/class.AccountConfigurationController.php`` as an example.

Test for Valid and Invalid CSRF Tokens
--------------------------------------

When you write regression tests for code which employs CSRF tokens, include tests for both valid and invalid CSRF
tokens. To test for requests with a CSRF token, add ``$_POST['csrf_token'] = parent::CSRF_TOKEN;`` or 
``$_GET['csrf_token'] = parent::CSRF_TOKEN;`` to your test. To test for
requests without it, omit that line. To simulate logging into ThinkUp with CSRF support enabled, call 
``$this->simulateLogin('user@example.com', false, $use_csrf_token = true);``.

See ``TestOfAccountConfigurationController::testDeleteExistingInstanceNoCSRFToken`` and  
``TestOfAccountConfigurationController::testDeleteExistingInstanceAsAdmin`` for examples of tests without and with
CSRF tokens.
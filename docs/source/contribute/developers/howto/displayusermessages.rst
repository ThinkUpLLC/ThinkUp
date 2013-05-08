How to Show User Messages on Application Pages
==============================================

ThinkUp's application code defines three types of user messages with standard styles throughout the application:
success, error, and informational messages. A developer can assign either a single page-level
message, or multiple field-level messages. Here's how.

Page-level Messages
-------------------

To assign a page level message to a ThinkUp view, use the ThinkUpController's addMessage methods. For example, if
you're working on the LogoutController (a child of ThinkUpController), and you want to add a "You have been logged
out" success message to the view, inside LogoutController, call:

:: 

    $this->addSuccessMessage('You have been logged out');

Then, to display that message, include the ``_usermessage.tpl`` template at the top of your Smarty template page file, 
like so:

:: 

    {include file="_usermessage.tpl"}

A given page can only have a single message of any given type. So if you add a page success message, then add another,
only the last one assigned will appear on-page.

Field-level Messages
--------------------

Sometimes you want to display an error, success, or informational message in a particular area of the page, near a
relevant page element. For example, in the RegistrationController, you might want to display a "Password must be at 
least 5 characters" error message near the password field if the submitted password is only 3 characters. To add
a field-level message to a view, in your controller, specify a field name as well as the message, like so:

:: 

    $this->addErrorMessage('Password must be at least 5 characters', 'password');

To display that error message near the password field, include the message template and pass it the appropriate
field name (in this case, 'password'), like so:

::

    {include file="_usermessage.tpl" field="password"}

That will make the password-specific error message appear at that place in the page.

You can assign multiple field-level messages of every type; they will only display in the view when you include
the message template, passing it the field name as a parameter.
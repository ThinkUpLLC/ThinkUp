Insights Generator
==================

The Insights Generator runs pluggable insight generators and creates ThinkUp's insight stream.

Plugin Settings
---------------

**Mandrill Template Name** (optional) is the name of a template in your Mandrill account. If the template name is blank,
ThinkUp will send the email as plain text.

Set Up Mandrill
---------------

In order to send HTML email, you must configure Mandrill and create an HTML template in your Mandrill account.

**Step 1. Set up the Mandrill API.**

1. Create an account at Mandrill: http://mandrill.com/
2. Click SMTP & API Credentials in the Configuration/Gear menu.
3. Click "+ New API Key."
4. Enter a description, such as "ThinkUp."
5. Click "Create API Key."
6. Modify your ThinkUp config file, ThinkUp/webapp/config.inc.php
7. Edit this line::

	$THINKUP_CFG['mandrill_api_key'] = '<YOUR NEW KEY GOES HERE>';

**Step 2. Create a Mandrill HTML template.**

1. In Mandrill, navigate to Outbound -> Templates.
2. Click "+ Create a Template."
3. You can name it anything, such as "ThinkUp Insights Email Template" and click "Start Coding."
4. In the text area, create your template.  This is an example which illustrates the possible variables::

	<h1>*|app_title|* has Insights For You!</h1>
	Visit <a href="*|app_url|*">*|app_title|*</a>.
	<div>
	   *|insights|*
	</div>
	<hr/>
	Change settings here: *|unsub_url|*

5. Click the "Publish" button.
6. Copy the Template Slug from the left side of the page (``thinkup-insights-email-template`` if you used the
   example title).
7. In ThinkUp, navigate to the Insights Generator settings (Settings -> Plugins -> Insights Generator -> Configure).
8. Open Advanced settings and enter the Mandrill Template Name (``thinkup-insights-email-template`` in our example).
9. Click "Save Settings."

You're done! Now ThinkUp will send your daily and weekly insights email notifications using your HTML template.
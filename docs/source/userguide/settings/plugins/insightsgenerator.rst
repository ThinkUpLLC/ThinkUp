Insights Generator
==================

The Insights Generator runs pluggable insight generators and creates the insight stream.

Plugin Settings
---------------

**Mandrill Template Name** (optional) is the name of a template in the Mandrill Transacitional Email. If this is blank
emails will be sent as plain-text via the normal Mailer.

Setting Up Mandrill
-------------------

In order to send HTML emails, you must configure Mandrill and create an HTML template in the Mandrill System.

**Step 1 - Set up Mandrill API**

1. Create an account with Mandrill: http://mandrill.com/
2. Click SMTP & API Credentials in the Configuration/Gear Menu
3. Click "+ New API Key"
4. Enter a description such as "ThinkUp"
5. Click "Create API Key"
6. Modify your ThinkUp Config file, ThinkUp/webapp/config.inc.php
7. Edit this line::

	$THINKUP_CFG['mandrill_api_key'] = '<YOUR NEW KEY GOES HERE>';

**Step 2 - Setup Mandrill HTML Template**

1. In Mandrill, Navigate to Outbound -> Templates
2. Click "+ Create a Template"
3. You can name it anything, such as "ThinkUp Email Template" and click "Start Coding"
4. In the text area, create your template.  This is an example which illustrates the possible variables::

	<h1>*|app_title|* has Insights For You!</h1>
	Visit <a href="*|app_url|*">*|app_title|*</a>.
	<div>
	   *|insights|*
	</div>
	<hr/>
	Change settings here: *|unsub_url|*

5. Click the "Publish Button"
6. Copy the Template Slug from the right side of the page. (``thinkup-email-template`` if you used the example title)
7. In ThinkUp, navigate to the Insights Generator Settings (Settings -> Plugins -> Insights Generator -> Configure)
8. Open Advanced settings, add in your Mandrill Template Name (``thinkup-email-template`` in our example)
9. Click "Save Settings"
 

<b>Google+ Plugin</b>

{include file="_usermessage.tpl"}

{if $oauth_link}<br><h2 class="subhead">Add a Google+ Account</h2>

Click on this button to authorize ThinkUp to access your Google+ account.
<a href="{$oauth_link}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Authorize ThinkUp on Google+</a>

<div style="clear:all">&nbsp;<br><br><br></div>
{/if}

{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
{if $user_is_admin}
<h2 class="subhead">Configure the Google+ Plugin</h2>
<ol style="margin-left:40px">
<li><a href="http://code.google.com/apis/console#access">Create a project in the Google APIs Console.</a></li>
<li>Click "Services" and switch Google+ API to "On." Next, click "API Access" then "Create an OAuth 2.0 client ID."</li>
<li>Edit the settings for your new Client ID then click "Next." Make sure "Application Type" is set to "Web Application" and set the first line of Authorized Redirect URIs to <pre>http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{if $smarty.server.SERVER_PORT != '80'}:{$smarty.server.SERVER_PORT}{/if}{$site_root_path}account/?p=googleplus</pre></li>
<li>Enter the Google-provided Client ID here.</li></ol>
{/if}
<p>
{$options_markup}
</p>
</div>

{literal}
<script type="text/javascript">
if( required_values_set ) {
    $('#add-account-div').show();
} else {
    if(! is_admin) {
        $('#contact-admin-div').show();
    }
}
{/literal}
</script>
{/if}

<br/>

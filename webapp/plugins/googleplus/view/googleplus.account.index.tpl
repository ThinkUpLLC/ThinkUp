
{include file="_usermessage.tpl"}

    {if count($owner_instances) > 0 }
    <h2 class="subhead">Google+ Accounts</h2>

    {foreach from=$owner_instances key=iid item=i name=foo}
    <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_4 right">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="grid_8 right">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=google%2B"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete account csrf token -->
            <input onClick="return confirm('Do you really want to delete this Google+ account from ThinkUp?');"  type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="delete" /></form></span>
        </div>
    </div>
    {/foreach}
    <br />
    {/if}

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
<li>Edit the settings for your new Client ID then click "Next." Make sure "Application Type" is set to "Web Application" and set the first line of Authorized Redirect URIs to <pre>http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{if $smarty.server.SERVER_PORT != '80'}:{$smarty.server.SERVER_PORT}{/if}{$site_root_path}account/?p=google%2B</pre></li>
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

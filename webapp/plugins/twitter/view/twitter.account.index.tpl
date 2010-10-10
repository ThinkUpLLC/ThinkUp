<div class="append_20">
<h2 class="subhead">Twitter Plugin</h2>

{if count($owner_instances) > 0 }
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_4 right">
            <span id="div{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
            {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="grid_8 right">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=twitter"><input type="hidden" name="instance_id" value="{$i->id}">
            <input onClick="return confirm('Do you really want to delete this Twitter account?');"  type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="delete" /></form></span>
        </div>
        </div>
    {/foreach}
{/if}
</div>

<div id="add-account-div" style="display: none;">
<h2 class="subhead">Add a Twitter account</h2>
<p>Click on this button to authorize ThinkUp to access your Twitter account.</p>
<a href="{$oauthorize_link}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Authorize ThinkUp on Twitter</a>
<br /><br /><br />
</div>

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

<!--<p>Alternately, add a public Twitter username for ThinkUp capture data about:</p>
<form method="get" action="index.php"><input type="hidden" name="p" value="twitter"><input name="twitter_username" /> <input type="submit" value="Add this Public User to ThinkUp"></form>-->


{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
<!-- Configure the Twitter Plugin -->
{if $user_is_admin}
<h2 class="subhead">Configure the Twitter Plugin</h2>
<ol style="margin-left:40px"><li><a href="http://twitter.com/oauth_clients/">Register your ThinkUp application on Twitter</a>.</li>
<li>Set the callback URL to <pre>http://{$smarty.server.SERVER_NAME}{$site_root_path}plugins/twitter/auth.php</pre></li>
<li>Set the application Default Access type to "Read-only".</li>
<li>Enter the Twitter-provided consumer key and secret here.</li></ol>
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


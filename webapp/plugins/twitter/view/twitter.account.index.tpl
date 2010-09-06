<div class="append_20">
<h2 class="subhead">Twitter Configuration</h2>
{if isset($errormsg)}
        <div class="error">
          {$errormsg}
        </div>
      {/if} 
      {if isset($successmsg)}
        <div class="success">
          {$successmsg}
        </div>
      {/if}
{if $owner->is_admin}
	<div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
		As an administrator you can see all accounts in the system.</p>
	</div>
{/if}

{if count($owner_instances) > 0 }
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_8">
            <span id="div{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
            {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
        </div>
        {if $user_is_admin}
        <div class="grid_9">
    		<span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        {/if}
        </div>
    {/foreach}
{else}
    You have no Twitter accounts configured.
{/if}
</div>

<div id="add-account-div" style="display: none;">
<h2 class="subhead">Add a Twitter account</h2>

<p>Click on this button to authorize ThinkUp to access your Twitter account.</p>
<a href="{$oauthorize_link}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Authorize ThinkUp on Twitter</a>
<br /><br /><br />
</div>

<!--<p>Alternately, add a public Twitter username for ThinkUp capture data about:</p>
<form method="get" action="index.php"><input type="hidden" name="p" value="twitter"><input name="twitter_username" /> <input type="submit" value="Add this Public User to ThinkUp"></form>-->


{if $options_markup}
<div style="border: solid gray 1px;padding:10px;margin:20px">
<h2 class="subhead">Configure the Twitter Plugin</h2>
<ol style="margin-left:40px"><li><a href="http://twitter.com/oauth_clients/">Register your ThinkUp application on Twitter</a>.</li>
<li>Set the callback URL to <pre>http://{$smarty.server.SERVER_NAME}{$site_root_path}plugins/twitter/auth.php</pre></li>
<li>Enter the Twitter-provided consumer key and secret here.</li></ol>
<p>
{$options_markup}
</p>
</div>
{literal}
<script type="text/javascript">
if( option_elements['oauth_consumer_key']['value'] && option_elements['oauth_consumer_secret']['value']) {
    $('#add-account-div').show();
}
{/literal}
</script>

{/if}


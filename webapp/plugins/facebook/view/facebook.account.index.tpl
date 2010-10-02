<div class="append_20">

    {if $owner->is_admin}
	<div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
		As an administrator you can see all accounts in the system.</p>
	</div>
    {/if}

    {if count($owner_instances) > 0 }
    <h2 class="subhead">Facebook User Accounts</h2>
    {foreach from=$owner_instances key=iid item=i name=foo}
    <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_8">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_7">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
    </div>
    {/foreach}
    <br />

    <h2 class="subhead">Facebook Pages</h2>
    {if $owner->is_admin}
    <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
        <p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
        As an administrator you can see all accounts in the system.</p>
    </div>
    {/if}
    {if count($owner_instance_pages) > 0 }
    {foreach from=$owner_instance_pages key=iid item=i name=foo}
    <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_8">
            <span id="div{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_7">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
    </div>{/foreach}
    <br />
    {/if}


{foreach from=$owner_instances key=iid item=i name=foo}
  {assign var='facebook_user_id' value=$i->network_user_id}
  {if $user_pages.$facebook_user_id}
    <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            {$i->network_username}&nbsp;likes:
        </div>
        <form name="addpage" action="index.php?p=facebook">
        <div class="grid_8">
            {if $user_pages.$facebook_user_id}
            <input type="hidden" name="instance_id" value="{$i->id}">
            <input type="hidden" name="p" value="facebook">
            <input type="hidden" name ="viewer_id" value="{$i->network_user_id}" />
            <input type="hidden" name ="owner_id" value="{$owner->id}" />
            <select name="facebook_page_id">
                {foreach from=$user_pages.$facebook_user_id key=page_id item=page name=p}
                    <option value="{$page->id}">{if strlen($page->name)>27}{$page->name|substr:0:27}...{else}{$page->name}{/if}</option> <br />
                {/foreach}
             </select>
             {/if}
        </div>
        <div class="grid_7">
             <span id="divaddpage{$i->network_username}"><input type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
addPage"  id="{$i->network_username}" value="add page" /></span>
        </div>
        </form>
    </div>
{/if}
{/foreach}
{/if}
</div> 
<div id="add-account-div" style="display: none;">
    {if $fbconnect_link}<h2 class="subhead">Add a Facebook User</h2>{$fbconnect_link}{/if}
    <div>
    </div>
</div>

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
{if $user_is_admin}
<h2 class="subhead">Configure the Facebook Plugin</h2>
<ol style="margin-left:40px">
<li><a href="http://developers.facebook.com/setup/">Create a ThinkUp Facebook application.</a></li>
<li>Set the Web Site &gt; Site URL to <pre>http://{$smarty.server.SERVER_NAME}{if $smarty.server.SERVER_PORT != '80'}:{$smarty.server.SERVER_PORT}{/if}{$site_root_path}</pre></li>
<li>Enter the Facebook-provided API Key, Application Secret and Application ID here.</li></ol>
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


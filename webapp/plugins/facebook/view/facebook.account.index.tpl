<div class="append_20">
    <h2 class="subhead">Facebook Configuration</h2>
	{if $error}
	<p class="error">
		{$error}
	</p>	
	{/if}
	{if $info}
    <p class="info">
        {$info}
    </p>    
    {/if}
    {if $success}
    <p class="success">
        {$success}
    </p>    
    {/if}
<br />
    <p>Set up the Facebook plugin.</p><br />

	<h2 class="subhead">Facebook User Accounts</h2>
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
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_8">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
        </div>
        <div class="grid_7">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
    </div>{/foreach}
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
            <span id="div{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
        </div>
        <div class="grid_7">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
    </div>{/foreach}
    <br />
    {/if}



        <h2 class="subhead">Add a Facebook Page</h2>
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
                    <option value="{$page.json|escape:'html'}">{if strlen($page.name)>27}{$page.name|substr:0:27}...{else}{$page.name}{/if}</option> <br />
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
    {else}
    You have no Facebook accounts configured.
    {/if}
    
    
</div> {if $fbconnect_link}<h2 class="subhead">Add a Facebook User</h2>{$fbconnect_link}{/if}
<div id="offlineAccess">
    <fb:prompt-permission perms="read_stream,publish_stream,offline_access" next_fbjs="save_session()">
        Click here to grant offline access!
    </fb:prompt-permission>
</div>
<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript">
</script>
<script type="text/javascript">
                FB.init("{$fb_api_key}", "{$site_root_path}plugins/facebook/xd_receiver.php", {literal}{
                    permsToRequestOnConnect: "read_stream,offline_access",
                });
</script>
<script>
    function save_session(){
        session = FB.Facebook.apiClient.get_session();
        var sessionKey = session.session_key;
        $.ajax({
            type: "GET",
            url: '{/literal}{$site_root_path}{literal}plugins/facebook/auth.php',
            data: {
                sessionKey: sessionKey
            },
            dataType: "json",
            async: false,
            time: 10,
            success: function(msg){
            
            }
        })
    };
</script>

<script src="{/literal}{$site_root_path}{literal}plugins/facebook/assets/js/fbconnect.js" type="text/javascript">
</script>
<script type="text/javascript">
    window.onload = function(){
        facebook_onload(true);
    };
</script>
{/literal}
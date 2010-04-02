<div class="append_20">
    <h2 class="subhead">Facebook Configuration</h2>
	{if $error}
	<p class="error">
		{$error}
	</p>	
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
            <a href="{$cfg->site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_8">
            <span id="div{$i->network_username}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
{if $i->is_public}btnPriv{else}btnPub{/if}"   id="{$i->network_username}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
        </div>
        <div class="grid_7">
            <span id="divactivate{$i->network_username}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->network_username}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
    </div>{/foreach}
    {else}
    You have no Facebook accounts configured.
    {/if}
</div> {if $fbconnect_link}<h2 class="subhead">Add a Facebook account</h2>{$fbconnect_link}{/if}
<div id="offlineAccess">
    <fb:prompt-permission perms="read_stream,publish_stream,offline_access" next_fbjs="save_session()">
        Click here to grant offline access!
    </fb:prompt-permission>
</div>
<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript">
</script>
<script type="text/javascript">
                FB.init("{$fb_api_key}", "{$cfg->site_root_path}account/xd_receiver.php", {literal}{
                    permsToRequestOnConnect: "read_stream,offline_access",
                });
</script>
<script>
    function save_session(){
        session = FB.Facebook.apiClient.get_session();
        var sessionKey = session.session_key;
        $.ajax({
            type: "GET",
            url: '{/literal}{$cfg->site_root_path}{literal}account/fbsavesession.php',
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

<script src="{/literal}{$cfg->site_root_path}{literal}cssjs/fbconnect.js" type="text/javascript">
</script>
<script type="text/javascript">
    window.onload = function(){
        facebook_onload(true);
    };
</script>
{/literal}
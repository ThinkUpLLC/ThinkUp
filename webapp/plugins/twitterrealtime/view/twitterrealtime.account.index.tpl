<div class="append_20 alert helpful">
{insert name="help_link" id='twitterrealtime'}
<h2>Twitter Realtime Plugin </h2>

{if count($owner_instances) > 0 }
    <p>The Twitter Realtime Plugin requires the <a href="./?p=twitter">Twitter plugin</a> to be configured with your
    Twitter app's authorization information. If this is not set up, the Twitter Realtime Plugin will not run
    successfully. Currently, this plugin is only initiated from the command line.</p>
    
</div>

<div class="append_20">
    <p>Pausing a given account disables the crawling performed by the <a href="./?p=twitter">Twitter plugin</a> for that
    account, as well as pausing the Twitter Realtime streaming. For streaming, the changes will take effect next
    time streaming is launched.</p>
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="linkbutton {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling and streaming{else}start crawling and streaming{/if}" /></span>
        </div>

        </div>
    {/foreach}
{/if}
</div>


<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
<p>
<b>Note:</b> You can use redis for the realtime data queue. You will need PHP 5.3 or greater, and a redis server 
running  on the local host. If those two requirements are met you will see an option to enable the redis queue.
</p>

<p>{$auth_from_twitter}</p>
{/if}

{if $options_markup}
<p>
{$options_markup}
</p>
{/if}

{if $user_is_admin}</div>{/if}

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


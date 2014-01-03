<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='twitterrealtime'}</span>
    <h1>
        <i class="fa fa-twitter text-muted"></i>
        Twitter Realtime
    </h1>

{if count($owner_instances) > 0 }
    <p>The Twitter Realtime Plugin requires the <a href="./?p=twitter">Twitter plugin</a> to be configured with your
    Twitter app's authorization information. If this is not set up, the Twitter Realtime Plugin will not run
    successfully. Currently, the only way to 
    <a href="http://thinkup.com/docs/userguide/settings/plugins/twitterrealtime.html">initiate this plugin is at the command line</a>.</p>
    
</div>

<div class="append_20">
    <p>Pausing a given account disables the crawling performed by the <a href="./?p=twitter">Twitter plugin</a> for that
    account, as well as pausing the Twitter Realtime streaming. For streaming, the changes will take effect next
    time streaming is launched.</p>
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="row-fluid">
            <div class="col-md-9">
                <a href="{$site_root_path}?u={$i->network_username}">{$i->network_username}</a>
            </div>
            <div class="col-md-9">
                <span id="divactivate{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling and streaming{else}start crawling and streaming{/if}" /></span>
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

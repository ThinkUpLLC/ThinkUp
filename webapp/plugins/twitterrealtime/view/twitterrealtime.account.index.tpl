<div class="append_20">
<h2 class="subhead">Twitter Realtime Plugin</h2>

{if count($owner_instances) > 0 }
    <p>The Twitter Realtime Plugin requires the <a href="./?p=twitter">Twitter plugin</a> to be configured with your
    Twitter app's authorization information. If this is not set up, the Twitter Realtime Plugin will not run
    successfully. Currently, this plugin is only initiated from the command line.</p>
    <p>Pausing a given account disables the crawling performed by the <a href="./?p=twitter">Twitter plugin</a> for that
    account, as well as pausing the Twitter Realtime streaming. For streaming, the changes will take effect next
    time streaming is launched.</p>
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling and streaming{else}start crawling and streaming{/if}" /></span>
        </div>

        </div>
    {/foreach}
{/if}
</div>


<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

<!--<p>Alternately, add a public Twitter username for ThinkUp capture data about:</p>
<form method="get" action="index.php"><input type="hidden" name="p" value="twitter"><input name="twitter_username" /> <input type="submit" value="Add this Public User to ThinkUp"></form>-->

<p>
<b>Note:</b> You can use redis for the realtime data queue. You will need php 5.3 or greater, and a redis server 
running  on the local host. If those two requirements are met you will see an option to enable the redis queue.
</p>

{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
<!-- Configure the TwitterRealtime Plugin -->
{if $user_is_admin}
<p>{$auth_from_twitter}</p>
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


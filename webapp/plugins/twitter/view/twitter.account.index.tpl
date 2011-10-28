<div class="append_20">
<h2 class="subhead">Twitter Plugin {insert name="help_link" id='twitter'}</h2>

<p>The Twitter plugin captures and display tweets, replies, mentions, retweets, friends, followers, favorites, links, and photos.</p>

{include file="_usermessage.tpl"}

{if $oauthorize_link}
<br>
<div id="add-account-div" style="display: none;">
<a href="{$oauthorize_link}" class="tt-button ui-state-default tt-button-icon-right ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Add a Twitter account</a>
<br /><br />
</div>
{/if}

{if count($owner_instances) > 0 }
<br>
<h2 class="subhead">Twitter Accounts</h2>
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
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=twitter">
            <input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<input
            onClick="return confirm('Do you really want to delete this Twitter account?');"
            type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" 
            value="delete" /></form></span>
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


{if $options_markup}
<div {if $user_is_admin}style="border: solid gray 1px;padding:10px;margin:20px"{/if}>
{if $user_is_admin}
<h2 class="subhead">Set Up the Twitter Plugin</h2>
{include file="_usermessage.tpl" field="setup"}
<ol style="margin-left:40px"><li><a href="https://dev.twitter.com/apps" target="_blank">Create a new application on Twitter for ThinkUp</a>.</li>
<li>
    Set the callback URL to
    <small>
      <code style="font-family:Courier;" id="clippy_2988">
        http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}plugins/twitter/auth.php
      </code>
    </small>
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
              width="100"
              height="14"
              class="clippy"
              id="clippy" >
      <param name="movie" value="{$site_root_path}assets/flash/clippy.swf"/>
      <param name="allowScriptAccess" value="always" />
      <param name="quality" value="high" />
      <param name="scale" value="noscale" />
      <param NAME="FlashVars" value="id=clippy_2988&amp;copied=copied!&amp;copyto=copy to clipboard">
      <param name="bgcolor" value="#FFFFFF">
      <param name="wmode" value="opaque">
      <embed src="{$site_root_path}assets/flash/clippy.swf"
             width="100"
             height="14"
             name="clippy"
             quality="high"
             allowScriptAccess="always"
             type="application/x-shockwave-flash"
             pluginspage="http://www.macromedia.com/go/getflashplayer"
             FlashVars="id=clippy_2988&amp;copied=copied!&amp;copyto=copy to clipboard"
             bgcolor="#FFFFFF"
             wmode="opaque"
      />
    </object>
</li>
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


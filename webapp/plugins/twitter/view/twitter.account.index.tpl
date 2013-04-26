<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='twitter'}</span>
    <h2>
        <i class="icon-twitter icon-muted"></i> Twitter 
    </h2>

</div>

{include file="_usermessage.tpl"}

{if count($owner_instances) > 0 }{include file="_usermessage.tpl" field="user_add"}{/if}

{if count($owner_instances) > 0 }

<table class="table">

    <tr>
        <th><h4 class="pull-left">Account</h4></th>
        <th><i class="icon-lock icon-2x icon-muted"></i></th>
        {if $user_is_admin}<th><i class="icon-refresh icon-2x icon-muted"></i></th>{/if}
        <th><i class="icon-tag icon-2x icon-muted"></i></th>
        <th><i class="icon-trash icon-2x icon-muted"></i></th>
    </tr>
        
    {foreach from=$owner_instances key=iid item=i name=foo}
    <tr>
        <td>
            <h3 class="lead"><i class="icon-twitter icon-muted"></i>&nbsp;<a href="https://twitter.com/intent/user?screen_name={$i->network_username}">@{$i->network_username}</a></h3>
        </td>
        <td class="action-button">
            <span id="div{$i->id}"><input type="submit" name="submit" class="btn
            {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public} Set private{else}Set public{/if}" /></span>
        </td>
        {if $user_is_admin}
        <td class="action-button">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}Pause crawling{else}Start crawling{/if}" /></span>
        </td>
        {/if}
        <td class="action-button">
            <a href="{$site_root_path}account/?p=twitter&u={$i->network_username}&n=twitter" class="btn btn-info btnHashtag">Saved searches</a>
        </td>
        <td class="action-button">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=twitter">
            <input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<input
            onClick="return confirm('Do you really want to delete this Twitter account?');"
            type="submit" name="action" class="btn btn-danger" 
            value="Delete" /></form></span>
        </td>
    </tr>
    {/foreach}

</table>
{/if}


{if $oauthorize_link}
<a href="{$oauthorize_link}" class="btn btn-success add-account"><i class="icon-plus icon-white"></i> Add a Twitter account</a>
{/if}


<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
{include file="_usermessage.tpl" field="setup"}

<p style="padding:5px">To set up the Twitter plugin:</p>
<ol style="margin-left:40px"><li><a href="https://dev.twitter.com/apps/new" target="_blank" style="text-decoration: underline;">Create a new application on Twitter for ThinkUp</a>.</li>
<li>
    Fill in the following settings.<br />
    Name: <span style="font-family:Courier;">{$twitter_app_name}</span><br />
    Description: <span style="font-family:Courier;">My ThinkUp installation</span><br />
    Website: 
    <small>
      <code style="font-family:Courier;" id="clippy_2987">{$thinkup_site_url}</code>
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
      <param NAME="FlashVars" value="id=clippy_2987&amp;copied=copied!&amp;copyto=copy to clipboard">
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
             FlashVars="id=clippy_2987&amp;copied=copied!&amp;copyto=copy to clipboard"
             bgcolor="#FFFFFF"
             wmode="opaque"
      />
    </object>
    <br />
    Callback URL:
    <small>
      <code style="font-family:Courier;" id="clippy_2988">{$thinkup_site_url}account/?p=twitter</code>
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
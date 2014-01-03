{include file="_usermessage.tpl"}
    
<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='foursquare'}</span>
    <h1>
        <i class="fa fa-foursquare text-muted"></i>
        Foursquare
    </h1>
    
    <p>The Foursquare plugin captures your Foursquare checkins, photos, and comments.</p>

</div>


{if $oauth_link}
{include file="_usermessage.tpl" field='authorization'}
<a href="{$oauth_link}" class="btn btn-success add-account"><i class="fa fa-plus icon-white"></i> Add a Foursquare User</a>
{/if}

    {if count($owner_instances) > 0 }
    <h2>Foursquare Accounts</h2>

    {include file="_usermessage.tpl" field='user_add'}

    {foreach from=$owner_instances key=iid item=i name=foo}
    <div class="row-fluid">
        <div class="col-md-3">
            <a href="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="col-md-3">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_public}btnPriv{else}btnPub{/if}" value="Set {if $i->is_public}private{else}public{/if}" /></span>
        </div>
        {if $user_is_admin}
        <div class="col-md-3">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
        </div>
        {/if}
        <div class="col-md-3">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=foursquare#manage_plugin"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete account csrf token -->
            <input onClick="return confirm('Do you really want to delete this Foursquare account from ThinkUp?');"  type="submit" name="action" class="btn btn-danger" value="Delete" /></form></span>
        </div>
    </div>
    {/foreach}
    <br />
    {/if}

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>


{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
{include file="_usermessage.tpl" field="setup"}
<p style="padding:5px">To set up the Foursquare plugin:</p>
<ol style="margin-left:40px">
<li><a href="https://foursquare.com/developers/register" target="_blank">Create a new app at the Foursquare web site</a>.</li>
<li> Set the Application Name to <code>ThinkUp</code>.</li>
<li> Set the Application URL to:<br />
<small>
<code style="font-family:Courier;" id="clippy_2988">{$thinkup_site_url}</code>
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
<li> Set the Callback URL to:<br />
    <small>
<code style="font-family:Courier;" id="clippy_2989">{$thinkup_site_url}account/?p=foursquare</code>
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
      <param NAME="FlashVars" value="id=clippy_2989&amp;copied=copied!&amp;copyto=copy to clipboard">
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
             FlashVars="id=clippy_2989&amp;copied=copied!&amp;copyto=copy to clipboard"
             bgcolor="#FFFFFF"
             wmode="opaque"
      />
    </object>
</li>
<li>Enter the Client ID and Client Secret foursquare provided here.</li></ol>
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
{include file="_usermessage.tpl"}
    
<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='instagram'}</span>
    <h2>
        <i class="icon-instagram icon-muted"></i> Instagram
    </h2>

</div>

{if $fbconnect_link}
{include file="_usermessage.tpl" field="authorization"}
<a href="{$fbconnect_link}" class="btn btn-success add-account"><i class="icon-plus icon-white"></i> Add a Instagram User</a>
{/if}

{if count($instances) > 0 }{include file="_usermessage.tpl" field="user_add"}{/if}

{if count($instances) > 0 }
<div>
    <h2>Users</h2>

    {foreach from=$instances key=iid item=i name=foo}
    <div class="row-fluid">
        <div class="span3">
            {if $i->auth_error}<span class="ui-icon ui-icon-alert" style="float: left; margin:0.25em 0 0 0;" id="instagram-auth-error"></span>{/if}
            <a href="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a>
        </div>
        <div class="span3">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_public}btnPriv{else}btnPub{/if}" value="Set {if $i->is_public}private{else}public{/if}" /></span>
        </div>
        {if $user_is_admin}
        <div class="span3">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
        </div>
        {/if}
        <div class="span3">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=instagram"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete account csrf token -->
            <input onClick="return confirm('Do you really want to delete this Instagram account from ThinkUp?');"  type="submit" name="action" class="btn btn-danger" value="Delete" /></form></span>
        </div>
    </div>
    {/foreach}
</div>

    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }{include file="_usermessage.tpl" field="page_add"}{/if}


    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }
<div>
    <h2>Pages</h2>
    <div class="article">
    {foreach from=$owner_instance_pages key=iid item=i name=foo}
    <div class="row-fluid">
        <div class="span3">
            <a href="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="span3">
            <span id="div{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="Set {if $i->is_public}private{else}public{/if}" /></span>
        </div>
        {if $user_is_admin}
        <div class="span3">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
        </div>
        {/if}
        <div class="span3">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=instagram"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete page csrf token -->
            <input onClick="return confirm('Do you really want to delete this page?');"  type="submit" name="action" class="btn btn-danger" value="Delete" /></form></span>
        </div>
    </div>
    {/foreach}
    </div>
</div>
    {/if}

{/if}

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
{include file="_usermessage.tpl" field="setup"}

<p style="padding:5px">To set up the Instagram plugin:</p>
<ol style="margin-left:40px">
<li><a href="http://instagram.com/developer/clients/manage/" target="_blank" style="text-decoration: underline;">Go to the Instagram Developers Clients page</a> and click the "Create New App" button</li>
<li>
    In "Application Name" fill <span style="font-family:Courier;">{$logged_in_user}ThinkUp</span><br />
</li>
<li>
  In "OAuth redirect_uri" copy and paste the following:<br>
    <small>
      <code style="font-family:Courier;" id="clippy_2988">{$thinkup_site_url}account/?p=instagram</code>
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
    </object><br />
    Click "Save Changes"
</li>
<li>Enter the Instagram-provided <strong>Client ID</strong> and <strong>Client Secret</strong> here.</li>
</ol>

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
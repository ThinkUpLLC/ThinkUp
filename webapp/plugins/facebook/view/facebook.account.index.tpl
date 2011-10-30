
{include file="_usermessage.tpl"}
    
<div class="append_20 alert helpful">
	{insert name="help_link" id='facebook'}
	<h2>Facebook Plugin</h2>
	
	<div class="">
	<p>The Facebook plugin collects posts and status updates for Facebook users and the Facebook pages those users like.</p>
	
	</div>
	

</div>


<div id="add-account-div" style="display: none;">
<br />
    {if $fbconnect_link}
<br>
     {include file="_usermessage.tpl" field="authorization"}
<a href="{$fbconnect_link}" class="tt-button ui-state-default tt-button-icon-right ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Add a Facebook User</a>
    {/if}
    
</div>

{if count($owner_instances) > 0 }{include file="_usermessage.tpl" field="user_add"}{/if}

{if count($owner_instances) > 0 }
<div class="section">
    <h2>Facebook User Profiles</h2>

    {foreach from=$owner_instances key=iid item=i name=foo}
    <div class="clearfix article">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_4 right">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="grid_8 right">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=facebook"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete account csrf token -->
            <input onClick="return confirm('Do you really want to delete this Facebook account from ThinkUp?');"  type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="delete" /></form></span>
        </div>
    </div>
    {/foreach}
</div>

    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }{include file="_usermessage.tpl" field="page_add"}{/if}

<div class="section">

    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }
    <h2>Facebook Pages</h2>
    {foreach from=$owner_instance_pages key=iid item=i name=foo}
    <div class="clearfix article">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$site_root_path}index.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="grid_4 right">
            <span id="div{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="grid_4 right">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="grid_8 right">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=facebook"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete page csrf token -->
            <input onClick="return confirm('Do you really want to delete this page?');"  type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="delete" /></form></span>
        </div>

    </div>{/foreach}
    {/if}
</div>

<div class="section">
<h2>Add a Facebook Page</h2>
{foreach from=$owner_instances key=iid item=i name=foo}
  {assign var='facebook_user_id' value=$i->network_user_id}
  {if $user_pages.$facebook_user_id or $user_admin_pages.$facebook_user_id}
    <div class="clearfix article">
        <div class="grid_4 right" style="padding-top:.5em;">
            {$i->network_username}&nbsp;likes:
        </div>
        <form name="addpage" action="index.php?p=facebook">
        <div class="grid_8">
            <input type="hidden" name="instance_id" value="{$i->id}">
            <input type="hidden" name="p" value="facebook">
            <input type="hidden" name ="viewer_id" value="{$i->network_user_id}" />
            <input type="hidden" name ="owner_id" value="{$owner->id}" />
            <select name="facebook_page_id">
                {if $user_admin_pages.$facebook_user_id}
                    <optgroup label="Pages You Manage">
                        {foreach from=$user_admin_pages.$facebook_user_id key=page_id item=page name=p}
                            <option value="{$page->id}">{if strlen($page->name)>27}{$page->name|substr:0:27}...{else}{$page->name}{/if}</option> <br />
                        {/foreach}
                    </optgroup>
                {/if}
                {if $user_pages.$facebook_user_id}
                    <optgroup label="Pages You Like">
                    {foreach from=$user_pages.$facebook_user_id key=page_id item=page name=p}
                        <option value="{$page->id}">{if strlen($page->name)>27}{$page->name|substr:0:27}...{else}{$page->name}{/if}</option> <br />
                    {/foreach}
                    </optgroup>
                {/if}
             </select>
        </div>
        <div class="grid_7">
             <span id="divaddpage{$i->network_username}"><input type="submit" name="action" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
addPage"  id="{$i->network_username}" value="add page" /></span>
        </div>
        </form>
    </div>
    {else}
    <div class="article">
    To add a Facebook page to ThinkUp, create a new page on Facebook.com or "like" an existing one, and refresh this page.
	</div>
    {/if}
{/foreach}

{/if}
</div>

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>


{if $options_markup}{if $user_is_admin}{include file="_usermessage.tpl" field="setup"}{/if}{/if}

{if $options_markup}
<div {if $user_is_admin}{/if} class="section">
{if $user_is_admin}
<h2>Settings</h2>
<div class="article">
<p style="padding:5px">To set up the Facebook plugin:</p>
<ol style="margin-left:40px">
<li><a href="https://developers.facebook.com/apps" target="_blank">Click the "Create New App" button on Facebook.</a></li>
<li>
    Fill in the following settings.<br />
    App Display Name: <span style="font-family:Courier;">ThinkUp</span><br />
    App Namespace: (leave blank)
</li>
<li>
  At the bottom of the page, click the "Website" section and add the Site URL:
    <small>
      <code style="font-family:Courier;" id="clippy_2988">
          http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{if $smarty.server.SERVER_PORT != '80'}:{$smarty.server.SERVER_PORT}{/if}{$site_root_path}
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
<li>Enter the Facebook-provided App ID and App Secret here.</li></ol>
{/if}
<p>
{$options_markup}
</p>
<br />

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
</div>

{include file="_usermessage.tpl"}
    
<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='facebook'}</span>
    <h2>
        <i class="icon-facebook icon-muted"></i> Facebook 
    </h2>

</div>

{if $fbconnect_link}
{include file="_usermessage.tpl" field="authorization"}
<a href="{$fbconnect_link}" class="btn btn-success add-account"><i class="icon-plus icon-white"></i> Add a Facebook User</a>
{/if}

{if count($instances) > 0 }{include file="_usermessage.tpl" field="user_add"}{/if}

{if count($instances) > 0 }
<div>
    <h2>Facebook User Profiles</h2>

    {foreach from=$instances key=iid item=i name=foo}
    <div class="row-fluid">
        <div class="span3">
            {if $i->auth_error}<span class="ui-icon ui-icon-alert" style="float: left; margin:0.25em 0 0 0;" id="facebook-auth-error"></span>{/if}
            <a href="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a>
        </div>
        <div class="span3">
            <span id="div{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_public}btnPriv{else}btnPub{/if}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="span3">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="span3">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=facebook"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete account csrf token -->
            <input onClick="return confirm('Do you really want to delete this Facebook account from ThinkUp?');"  type="submit" name="action" class="btn btn-danger" value="delete" /></form></span>
        </div>
    </div>
    {/foreach}
</div>

    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }{include file="_usermessage.tpl" field="page_add"}{/if}


    {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }
<div>
    <h2>Facebook Pages</h2>
    <div class="article">
    {foreach from=$owner_instance_pages key=iid item=i name=foo}
    <div class="row-fluid">
        <div class="span3">
            <a href="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username}</a> 
        </div>
        <div class="span3">
            <span id="div{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->id}" value="{if $i->is_public}set private{else}set public{/if}" /></span>
        </div>
        <div class="span3">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" class="btn {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->id}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        <div class="span3">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=facebook"><input type="hidden" name="instance_id" value="{$i->id}">
            {insert name="csrf_token"}<!-- delete page csrf token -->
            <input onClick="return confirm('Do you really want to delete this page?');"  type="submit" name="action" class="btn btn-danger" value="delete" /></form></span>
        </div>
    </div>
    {/foreach}
    </div>
</div>
    {/if}

<div>
<h2>Add a Facebook Page</h2>
{foreach from=$instances key=iid item=i name=foo}
  {assign var='facebook_user_id' value=$i->network_user_id}
  {if $user_pages.$facebook_user_id or $user_admin_pages.$facebook_user_id}
      <div class="row-fluid">
        <div class="span6">
          <form name="addpage" action="index.php?p=facebook">
            <input type="hidden" name="instance_id" value="{$i->id}">
            <input type="hidden" name="p" value="facebook">
            <input type="hidden" name ="viewer_id" value="{$i->network_user_id}" />
            <input type="hidden" name ="owner_id" value="{$owner->id}" />
            <select name="facebook_page_id">
                {if $user_admin_pages.$facebook_user_id}
                    <optgroup label="Pages {$i->network_username} Manages">
                        {foreach from=$user_admin_pages.$facebook_user_id key=page_id item=page name=p}
                            <option value="{$page->id}">{if strlen($page->name)>27}{$page->name|substr:0:27}...{else}{$page->name}{/if}</option> <br />
                        {/foreach}
                    </optgroup>
                {/if}
                {if $user_pages.$facebook_user_id}
                    <optgroup label="Pages {$i->network_username} Likes">
                    {foreach from=$user_pages.$facebook_user_id key=page_id item=page name=p}
                        <option value="{$page->id}">{if strlen($page->name)>27}{$page->name|substr:0:27}...{else}{$page->name}{/if}</option> <br />
                    {/foreach}
                    </optgroup>
                {/if}
             </select>
           <span id="divaddpage{$i->network_username}"><input type="submit" name="action" class="btn addPage"  id="{$i->network_username}" value="add page" /></span>
        </div>
     </div>
    {else}
    <div class="article">
    To add a Facebook page to ThinkUp, create a new page on Facebook.com or "like" an existing one, and refresh this page.
    </div>
    {/if}
{/foreach}

</div>

{/if}

<div id="contact-admin-div" style="display: none;">
{include file="_plugin.admin-request.tpl"}
</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}
{include file="_usermessage.tpl" field="setup"}

<p style="padding:5px">To set up the Facebook plugin:</p>
<ol style="margin-left:40px">
<li><a href="https://developers.facebook.com/apps" target="_blank" style="text-decoration: underline;">Go to the Facebook Developers Apps page</a> and click the "Create New App" button</li>
<li>
    Fill in the following settings.<br />
    <strong>App Display Name:</strong> <span style="font-family:Courier;">{$logged_in_user} ThinkUp</span><br />
    <strong>App Namespace:</strong> [leave blank]<br />
    <strong>Web Hosting:</strong> [Do not check box]<br />
    Click "Continue", enter in the security word, and click "Continue" again
</li>
<li>
  Click "Website with Facebook Login", then next to <strong>Site URL</strong>, copy and paste this:<br>
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
    </object><br />
    Click "Save Changes"
</li>
<li>Enter the Facebook-provided <strong>App ID</strong> and <strong>App Secret</strong> here.</li>
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

{if isset($thinkupllc_endpoint)}

      <div class="container">
        <header>
          <h1>Twitter</h1>
          <h2>Manage accounts and choose which insights everyone (or just you) can see.</h2>
        </header>

        <ul class="list-group list-accounts form-horizontal">
        {if count($owner_instances) > 0 }
          {foreach from=$owner_instances key=iid item=i name=foo}
          <li class="list-group-item list-accounts-item">
            <div class="account-label">
              <img src="http://avatars.io/twitter/{$i->network_username}" class="account-photo img-circle">
              <a href="https://twitter.com/intent/user?screen_name={$i->network_username}">@{$i->network_username}</a>
            </div>
            <div class="account-action account-action-privacy">
              <div class="privacy-toggle fa-over" data-id="{$i->id}"
                data-network="{$i->network}" data-network-name="{$i->network_username}">
                <input type="radio" name="{$i->network_username}-privacy-toggle-control" value="0"
                class="field-privacy-private"
                {if not $i->is_public}checked="checked"{/if} id="field-{$i->network_username}-privacy-private" /><label class="toggle-label" for="field-{$i->network_username}-privacy-private" data-check-field="field-{$i->network_username}-privacy-public"><i class="fa fa-lock icon"></i><span class="text">Just you</span></label>

                <input type="radio" name="{$i->network_username}-privacy-toggle-control" value="1"
                class="field-privacy-public"
                {if $i->is_public}checked="checked"{/if} id="field-{$i->network_username}-privacy-public" /><label class="toggle-label" for="field-{$i->network_username}-privacy-public" data-check-field="field-{$i->network_username}-privacy-private"><i class="fa fa-globe icon"></i><span class="text">Everyone</span></label>
              </div>
            </div>
            <div class="account-action account-action-delete">
              <form method="post" action="{$site_root_path}account/?p=twitter"
                name="{$i->network_username}-delete" class="">
              <input type="hidden" name="instance_id" value="{$i->id}">
              <input type="hidden" name="action" value="Delete">
              {insert name="csrf_token"}
              <a href="javascript:document.forms['{$i->network_username}-delete'].submit();"
                onClick="return confirm('Do you really want to delete the {$i->network_username} account?');">
                <i class="fa fa-minus-circle icon"></i>
              </a>
              </form>
            </div>
          </li>
          {/foreach}
        {/if}
        </ul>

        <div class="account-buttons">
          {if $oauthorize_link}
            <a class="btn btn-default btn-account-add" href="{$oauthorize_link}"><i class="fa fa-twitter icon"></i>Connect a Twitter account</a>
          {/if}
          {if $oauthorize_link and count($owner_instances) > 0}<br>{/if}
          {if count($owner_instances) > 0 }
          <button class="btn btn-transparent btn-account-remove"
          data-label-visible="Cancel account removal" data-label-hidden="Remove an account">Remove an account</button>
          {/if}
        </div>

        <div class="form-notes">
          <p class="accounts-privacy">ThinkUp will never tweet on your behalf.</p>

          {include file="_usermessage.tpl" field="membership_cap"}
        </div>
      </div>
    </div>
{else}


<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='twitter'}</span>
    <h2>
        <i class="fa fa-twitter text-muted"></i> Twitter
    </h2>

</div>

{include file="_usermessage.tpl"}

{if count($owner_instances) > 0 }{include file="_usermessage.tpl" field="user_add"}{/if}

{if count($owner_instances) > 0 }


<table class="table">

    <tr>
        <th><h4 class="pull-left">Account</h4></th>
        <th><i class="fa fa-lock fa-2x icon-muted"></i></th>
        {if $user_is_admin}<th><i class="fa fa-refresh fa-2x icon-muted"></i></th>{/if}
        <th><i class="fa fa-tag fa-2x icon-muted"></i></th>
        <th><i class="fa fa-trash fa-2x icon-muted"></i></th>
    </tr>

    {foreach from=$owner_instances key=iid item=i name=foo}
    <tr>
        <td>
            <h3 class="lead"><i class="fa fa-twitter icon-muted"></i>&nbsp;<a href="https://twitter.com/intent/user?screen_name={$i->network_username}">@{$i->network_username}</a></h3>
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
            <a href="{$site_root_path}account/?p=twitter&amp;u={$i->network_username}&amp;n=twitter#manage_plugin" class="btn btn-info btnHashtag">Saved searches</a>
        </td>
        <td class="action-button">
            <span id="delete{$i->id}"><form method="post" action="{$site_root_path}account/?p=twitter#manage_plugin">
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

{include file="_usermessage.tpl" field="membership_cap"}


{if $oauthorize_link}
<a href="{$oauthorize_link}" class="btn btn-success add-account"><i class="fa fa-plus icon-white"></i> Add a Twitter account</a>
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

{/if}<!-- end if hosted/OSP loop -->

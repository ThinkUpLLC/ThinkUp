    {include file="_usermessage-v2.tpl" field="user_add"}

  <div class="container">
    <header class="container-header">
      <h1>Twitter</h1>
      <h2>Manage accounts and choose which insights everyone (or just you) can see.</h2>
    </header>

    {if count($owner_instances) > 0 }
    <ul class="list-group list-accounts form-horizontal">
      {foreach from=$owner_instances key=iid item=i name=foo}
      <li class="list-group-item list-accounts-item{if !isset($thinkupllc_endpoint)} has-extra-buttons{/if}">
        <div class="account-label">
          <img src="http://avatars.io/twitter/{$i->network_username}" class="account-photo img-circle">
          <a href="https://twitter.com/intent/user?screen_name={$i->network_username}">@{$i->network_username}</a>
        </div>
        <div class="account-action account-action-privacy">
          <div class="privacy-toggle fa-over" data-id="{$i->id}"
            data-network="{$i->network}" data-network-name="{$i->network_username}">
            <input type="radio" name="{$i->network_username}-privacy-toggle-control" value="0"
            class="field-privacy-private"
            {if not $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-private" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-private" data-check-field="field-{$i->network_user_id}-privacy-public"><i class="fa fa-lock icon"></i><span class="text">Just you</span></label>

            <input type="radio" name="{$i->network_username}-privacy-toggle-control" value="1"
            class="field-privacy-public"
            {if $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-public" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-public" data-check-field="field-{$i->network_user_id}-privacy-private"><i class="fa fa-globe icon"></i><span class="text">Everyone</span></label>
          </div>
        </div>

        {if $user_is_admin and !isset($thinkupllc_endpoint)}
        <div class="extra-buttons">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn btn-default {if $i->is_active}btnPause{else}btnPlay{/if} btn-sm" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
            <br>
            <a href="{$site_root_path}account/?p=twitter&amp;u={$i->network_username}&amp;n=twitter#manage_plugin" class="btn btn-info btnHashtag btn-sm">Saved searches</a>
        </div>
        {/if}

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
    </ul>
    {/if}

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
      {if $oauthorize_link}<p class="accounts-privacy">ThinkUp will never tweet on your behalf.</p>{/if}

      {if isset($thinkupllc_endpoint)}
      {include file="_usermessage.tpl" field="membership_cap"}
      {/if}
    </div>

    {if !isset($thinkupllc_endpoint)}
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

    {/if}
  </div>
</div>

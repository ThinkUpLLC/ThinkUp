    {include file="_usermessage-v2.tpl" field="user_add"}

  <div class="container">
    <header class="container-header">
      <h1>Facebook</h1>
      <h2>Manage accounts and choose which insights everyone (or just you) can see.</h2>
    </header>

    {if count($instances) > 0 }
    <ul class="list-group list-accounts">
      {foreach from=$instances key=iid item=i name=foo}
      <li class="list-group-item list-accounts-item{if !isset($thinkupllc_endpoint)} has-extra-buttons{/if}">
        <div class="account-label">
          {if $i->auth_error}<span class="fa fa-warning text-warning" id="facebook-auth-error"></span>{/if}
          <img src="http://avatars.io/facebook/{$i->network_user_id}" class="account-photo img-circle">
          <a href="{$site_root_path}?u={$i->network_username|urlencode}&amp;n={$i->network|urlencode}">{$i->network_username}</a>
        </div>

        <div class="account-action account-action-privacy">
          <div class="privacy-toggle fa-over" data-id="{$i->id}"
            data-network="{$i->network}" data-network-name="{$i->network_username}">
            <input type="radio" name="{$i->network_user_id}-privacy-toggle-control" value="0"
            class="field-privacy-private"
            {if not $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-private" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-private" data-check-field="field-{$i->network_user_id}-privacy-public"><i class="fa fa-lock icon"></i><span class="text">Just you</span></label>

            <input type="radio" name="{$i->network_user_id}-privacy-toggle-control" value="1"
            class="field-privacy-public"
            {if $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-public" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-public" data-check-field="field-{$i->network_user_id}-privacy-private"><i class="fa fa-globe icon"></i><span class="text">Everyone</span></label>
          </div>
        </div>

        {if $user_is_admin and !isset($thinkupllc_endpoint)}
        <div class="extra-buttons">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn btn-default {if $i->is_active}btnPause{else}btnPlay{/if} btn-sm btn-info" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
        </div>
        {/if}

        <div class="account-action account-action-delete">
          <form method="post" action="{$site_root_path}account/?p=facebook"
            name="{$i->network_user_id}-delete">
          <input type="hidden" name="instance_id" value="{$i->id}">
          <input type="hidden" name="action" value="Delete">
          {insert name="csrf_token"}<!-- delete account csrf token -->
          <a href="javascript:document.forms['{$i->network_user_id}-delete'].submit();"
            onClick="return confirm('Do you really want to delete the {$i->network_username} account?');">
            <i class="fa fa-minus-circle icon"></i>
          </a>
          </form>
        </div>
      </li>
      {/foreach}

      {if !isset($thinkupllc_endpoint) and isset($owner_instance_pages) and count($owner_instance_pages) > 0 }
      {foreach from=$owner_instance_pages key=iid item=i name=foo}
      <li class="list-group-item list-accounts-item{if !isset($thinkupllc_endpoint)} has-extra-buttons{/if}">
        <div class="account-label">
          {if $i->auth_error}<span class="fa fa-warning text-warning" id="facebook-auth-error"></span>{/if}
          <img src="http://avatars.io/facebook/{$i->network_user_id}" class="account-photo img-circle">
          <a href="{$site_root_path}?u={$i->network_username|urlencode}&amp;n={$i->network|urlencode}">{$i->network_username}</a>
        </div>

        <div class="account-action account-action-privacy">
          <div class="privacy-toggle fa-over" data-id="{$i->id}"
            data-network="{$i->network}" data-network-name="{$i->network_username}">
            <input type="radio" name="{$i->network_user_id}-privacy-toggle-control" value="0"
            class="field-privacy-private"
            {if not $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-private" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-private" data-check-field="field-{$i->network_user_id}-privacy-public"><i class="fa fa-lock icon"></i><span class="text">Just you</span></label>

            <input type="radio" name="{$i->network_user_id}-privacy-toggle-control" value="1"
            class="field-privacy-public"
            {if $i->is_public}checked="checked"{/if} id="field-{$i->network_user_id}-privacy-public" /><label class="toggle-label" for="field-{$i->network_user_id}-privacy-public" data-check-field="field-{$i->network_user_id}-privacy-private"><i class="fa fa-globe icon"></i><span class="text">Everyone</span></label>
          </div>
        </div>

        {if $user_is_admin and !isset($thinkupllc_endpoint)}
        <div class="extra-buttons">
            <span id="divactivate{$i->id}"><input type="submit" name="submit" id="{$i->id}" class="btn btn-default {if $i->is_active}btnPause{else}btnPlay{/if} btn-sm" value="{if $i->is_active}Pause{else}Start{/if} crawling" /></span>
        </div>
        {/if}

        <div class="account-action account-action-delete">
          <form method="post" action="{$site_root_path}account/?p=facebook"
            name="{$i->network_username}-delete">
          <input type="hidden" name="instance_id" value="{$i->id}">
          <input type="hidden" name="action" value="Delete">
          {insert name="csrf_token"}<!-- delete page csrf token -->
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
    {/if}

    <div class="account-buttons">
      {if $fbconnect_link}
        <a class="btn btn-default btn-account-add" href="{$fbconnect_link}"><i class="fa fa-facebook icon"></i>Connect a Facebook account</a>
      {/if}
      {if $fbconnect_link and count($instances) > 0}<br>{/if}
      {if count($instances) > 0 }
      <button class="btn btn-transparent btn-account-remove"
      data-label-visible="Cancel account removal" data-label-hidden="Remove an account">Remove an account</button>
      {/if}
    </div>

    <div class="form-notes">
      {if $fbconnect_link}<p class="accounts-privacy">ThinkUp will never post on your behalf.</p>{/if}

      {if isset($thinkupllc_endpoint)}
      {include file="_usermessage.tpl" field="membership_cap"}
      {/if}
    </div>

    {foreach from=$instances key=iid item=i name=foo}
        {if isset($i->auth_error)}
          <script>{literal}
          var app_message = {};
          app_message.msg = {/literal}"{$i->network_username}’s Facebook connection expired.  To fix it, <a href=\"{$fb_reconnect_link}\">re-connect</a>."{literal};
          app_message.type = "warning";
          {/literal}</script>
        {/if}
    {/foreach}

    {if !isset($thinkupllc_endpoint)}

      {if isset($owner_instance_pages) && count($owner_instance_pages) > 0 }{include file="_usermessage.tpl" field="page_add"}{/if}

      {foreach from=$instances key=iid item=i name=foo}
        {assign var='facebook_user_id' value=$i->network_user_id}
        {if $user_pages.$facebook_user_id or $user_admin_pages.$facebook_user_id}
      <header class="container-header">
        <h1>Add a Facebook Page</h1>
        <h2>Choose from the pages listed below</h2>
      </header>
                <form name="addpage" action="index.php?p=facebook" class="text-center">
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
                  <input type="submit" name="action" class="btn addPage"  id="{$i->network_username}" value="add page" />
                </form>
          {/if}
      {/foreach}




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
          <strong>App Category:</strong> [Leave as default: Other - Choose a sub-category]<br />
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
    {/if}

  </div>
</div>

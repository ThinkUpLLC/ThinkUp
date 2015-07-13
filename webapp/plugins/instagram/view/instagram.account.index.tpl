{include file="_usermessage-v2.tpl" field="user_add"}

  <div class="container">
    <header class="container-header">
      <h1>Instagram</h1>
      <h2>Manage accounts and choose who can see insights.</h2>
    </header>

    {if count($instances) > 0 }
    <ul class="list-group list-accounts form-horizontal">
      {foreach from=$instances key=iid item=i name=foo}
      <li class="list-group-item list-accounts-item{if !isset($thinkupllc_endpoint)} has-extra-buttons{/if}">
        <div class="account-label">
          {if $i->auth_error}<span class="fa fa-warning text-warning" id="instagram-auth-error"></span>{/if}
          <img src="http://avatars.io/instagram/{$i->network_username}" class="account-photo img-circle">
          <a href="https://instagram.com/{$i->network_username}">{$i->network_username}</a>
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
          <form method="post" action="{$site_root_path}account/?p=instagram"
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
      {if $instaconnect_link}
        {include file="_usermessage.tpl" field="authorization"}
        <a class="btn btn-default btn-account-add" href="{$instaconnect_link}"><i class="fa fa-instagram icon"></i>Connect an Instagram account</a>
      {/if}
      {if $instaconnect_link and count($instances) > 0}<br>{/if}
      {if count($instances) > 0 }
      <button class="btn btn-transparent btn-account-remove"
      data-label-visible="Cancel account removal" data-label-hidden="Remove an account">Remove an account</button>
      {/if}
    </div>

    <div class="form-notes">
      {if $instaconnect_link}<p class="accounts-privacy">ThinkUp will never post on your behalf.</p>{/if}

      {if isset($thinkupllc_endpoint)}
      {include file="_usermessage.tpl" field="membership_cap"}
      {/if}
    </div>

    {foreach from=$instances key=iid item=i name=foo}
        {if isset($i->auth_error)}
          <script>{literal}
          var app_message = {};
          app_message.msg = {/literal}"{$i->network_username}’s Instagram connection expired.  To fix it, <a href=\"{$instaconnect_link}\">re-connect</a>."{literal};
          app_message.type = "warning";
          {/literal}</script>
        {/if}
    {/foreach}

  {if !isset($thinkupllc_endpoint)}
  <div id="contact-admin-div" style="display: none;">
  {include file="_plugin.admin-request.tpl"}
  </div>

  {if $user_is_admin}
  {include file="_plugin.showhider.tpl"}
  {include file="_usermessage.tpl" field="setup"}

  <p style="padding:5px">To set up the Instagram plugin:</p>
  <ol style="margin-left:40px">
  <li><a href="http://instagram.com/developer/clients/manage/" target="_blank" style="text-decoration: underline;">Go to the Instagram Developers Clients page</a> and click the "Register a new Client" button.</li>
  <li>
      In "Application Name", copy and paste the following:<br />
      <small>
        <code style="font-family:Courier;" id="clippy_2987">{$logged_in_user} ThinkUp</code>
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
      </object><br />
  </li>
  <li>
      In "Description", enter  <span style="font-family:Courier;">ThinkUp Instagram access</span><br />
  </li>
  <li>
      In "Website", put <span style="font-family:Courier;">{$thinkup_site_url}</span><br />
  </li>
  <li>
    In "OAuth redirect_uri", copy and paste the following:<br>
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
      Click on "Save Changes".
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

  {/if}

</div>



{if isset($thinkupllc_endpoint)}
  {include file="account.tucom.tpl"}
{else}
{include file="_header.tpl" body_classes="settings menu-open" body_id="settings-main"}
{include file="_navigation.tpl"}


  <div class="container">

  {if $body}
    {include file="_usermessage-v2.tpl" field="account"}
    {$body}
  {else}

    {include file="_usermessage.tpl" field="preferences"}
    <form name="setPreferences" id="setPreferences" class="form-horizontal" action="index.php?m=manage"
      method="POST">

      <header>
        <h1>Settings</h1>
      </header>

      <fieldset class="fieldset-personal">
        <header></header>
        <div class="form-group">
          <label class="control-label" for="notificationfrequency">Insights email</label>
          <div class="form-control picker">
          <i class="fa fa-chevron-down icon"></i>
          <select name="notificationfrequency">
         {foreach from=$notification_options item=description key=key}
             <option value="{$key}" {if $key eq $owner->email_notification_frequency}selected="selected"{/if}>{$description}</option>
         {/foreach}
         </select>
         </div>
        </div>
        <div class="form-group">
          <label class="control-label" for="control-timezone">Time zone</label>
          <div class="form-control picker">
            <i class="fa fa-chevron-down icon"></i>
            <select id="control-timezone" name="timezone">
              <option value=""{if $current_tz eq ''} selected{/if}>Select a time zone:</option>
              {foreach from=$tz_list key=group_name item=group}
                <optgroup label="{$group_name}">
                {foreach from=$group item=tz}
                  <option id="tz-{$tz.display}" value='{$tz.val}'{if $owner->timezone eq $tz.val} selected{/if}>{$tz.display}</option>
                {/foreach}
                </optgroup>
              {/foreach}
            </select>
            {if $owner->timezone eq 'UTC'}
            <script type="text/javascript">
            {literal}
            var tz_info = jstz.determine();
            var regionname = tz_info.name().split('/');
            var tz_option_id = '#tz-' + regionname[1];
            if( $('#timezone option[value="' + tz_info.name() + '"]').length > 0) {
                if( $(tz_option_id) ) {
                    $('#timezone').val( tz_info.name());
                }
            }
            {/literal}
            </script>
            {/if}
          </div>
        </div>

           {insert name="csrf_token"}
      </fieldset>
      <input type="submit" value="Update" name="updatepreferences" class="btn btn-default btn-submit">
    </form>

    <form name="changepass" id="changepass" class="form-horizontal" method="post"
      action="index.php?m=manage#changepass">

      <fieldset class="fieldset-password">
        <header>
          <h2>Change Password</h2>
        </header>
        {include file="_usermessage.tpl" field='password'}
        <div class="form-group">
          <label class="control-label" for="control-password-current">Current</label>
          <input type="password" class="form-control" id="control-password-current" name="oldpass" required>
        </div>
        <div class="form-group">
          <label class="control-label" for="control-password-new">New</label>
          <input type="password" class="form-control" id="control-password-new" name="pass1" required
            data-validation-required-message="You'll need
              a enter a password of at least 8 characters."
            data-validation-pattern-message="Must be at
              least 8 characters, with both numbers &amp; letters.">
        </div>
        <div class="form-group">
          <label class="control-label" for="control-password-verify">Verify New</label>
          <input type="password" class="form-control" id="control-password-verify" name="pass2" required
            data-validation-required-message="Password confirmation is required."
            data-validation-match-match="pass1"
            data-validation-match-message="Make sure this
              matches the password you entered above." >
        </div>
      </fieldset>
      {insert name="csrf_token"}
      <input type="submit" value="Change" name="changepass" class="btn btn-default btn-submit">
    </form>

    <div class="settings-set settings-set-simple">
      <header>
        <h2>Plugins</h2>
      </header>

      {if $installed_plugins}
      <ul class="list-group list-group-plugins">

        {foreach from=$installed_plugins key=ipindex item=ip name=foo}
        <li class="list-group-item">
          <div class="account-label">
            {if !$ip->isConfigured()}
              <i class="icon fa fa-{$ip->icon} fa-fw fa-2x text-danger"></i>
            {else}
              <i class="icon fa fa-{$ip->icon} fa-fw fa-2x text-primary"></i>
            {/if}

            <a href="?p={$ip->folder_name|get_plugin_path}#manage_plugin"
              class="manage_plugin {if !$ip->isConfigured()}text-danger{/if}">
              <span class="plugin-name" id="spanpluginnamelink{$ip->id}">{$ip->name}</span></a>

            <span style="display: none;" class='linkbutton' id="messageactive{$ip->id}"></span>

          </div>

        </li>
        {/foreach}

      </ul>
      {/if}
    </div>

    {if $user_is_admin}
    <div class="settings-set settings-set-simple text-center">
      <header>
        <h2>Backup &amp; Export</h2>
      </header>

      <p>Download a copy of your entire database</p>
      <a href="{$site_root_path}install/backup.php" class="show-section btn btn-default">Back up ThinkUp</a>
      <p>Transfer a single account</p>
      <a href="{$site_root_path}install/exportuserdata.php" class="show-section btn btn-default">Export a ThinkUp account</a>
    </div>

    <form id="app-settings-form" class="form-horizontal" name="app_settings" method="post"
      action="{$site_root_path}session/app_settings.php" onsubmit="return false">

      <header>
        <h1>Application Settings</h1>
      </header>

      <div class="alert alert-error" id="settings_error_message_error" style="display: none;">
        <span class="fa fa-alert"></span>
        <span id="settings_error_message"></span>
      </div>

      <div class="alert alert-success"  id="settings_success" style="display: none;">
        <span class="fa fa-check"></span>
        Settings saved!
      </div>

      <fieldset id="fieldset-test-stuff">
        <header>
          <h2>Test Stuff Out</h2>
        </header>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_subscribed_to_beta">Enable beta upgrades</label>
          <input type="checkbox" class="form-control" name="is_subscribed_to_beta" id="is_subscribed_to_beta" checked value="true">
          <span class="help-block">Test bleeding edge, beta upgrades. May require command line server access. Proceed at your own risk.</span>
        </div>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_log_verbose">Enable developer log</label>
          <input type="checkbox" class="form-control" name="is_log_verbose" id="is_log_verbose" value="true">
          <span class="help-block">See the verbose, unformatted developer log on the Capture Data screen.</span>
        </div>
      </fieldset>

      <fieldset id="fieldset-people">
        <header>
          <h2>Let People In</h2>
        </header>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_registration_open">Open registration to new ThinkUp users</label>
          <input type="checkbox" class="form-control" name="is_registration_open" id="is_registration_open" value="true">
          <span class="help-block">Set whether or not your site's registration page is available and accepts new user registrations.</span>
        </div>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="recaptcha_enable">Enable reCAPTCHA</label>
          <input type="checkbox" class="form-control" name="recaptcha_enable" id="recaptcha_enable" value="true">
          <span class="help-block">Add reCAPTCHA to ThinkUp's registration form. <a href="https://www.google.com/recaptcha">Get your reCAPTCHA keys here</a>.</span>
        </div>

        <div id="recaptcha_enable_deps" style="display: none;">
          <div class="form-group">
            <label class="control-label" for="recaptcha_public_key">Public Key</label>
            <input name="recaptcha_public_key" type="text" id="recaptcha_public_key" class="form-control">
          </div>
          <div class="form-group">
            <label class="control-label" for="recaptcha_private_key">Private Key</label>
            <input name="recaptcha_private_key" type="text" id="recaptcha_private_key" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label" for="default_instance">Default service user</label>
          <div class="form-control picker">
            <i class="fa fa-chevron-down icon"></i>
            <select name="default_instance" id="default_instance">
              <option value="0">Last updated</option>
              {foreach from=$public_instances item=pi}
                <option value="{$pi->id}">{$pi->network_username} - {$pi->network|capitalize}</option>
              {/foreach}
            </select>
          </div>
          <span class="help-block">Set the service user to display by default. {insert name="help_link" id="default_service_user"}</span>
        </div>
      </fieldset>

      <fieldset id="fieldset-privacy">
        <header>
          <h2>Don't Share Data</h2>
        </header>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_api_disabled">Disable the JSON API</label>
          <input type="checkbox" class="form-control" name="is_api_disabled" id="is_api_disabled" value="true">
          <span class="help-block">Set whether or not your site's data is available via ThinkUp's JSON API. <a href="http://thinkup.com/docs/userguide/api/posts/index.html">Learn more...</a></span>
        </div>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_embed_disabled">Disable thread embeds</label>
          <input type="checkbox" class="form-control" name="is_embed_disabled" id="is_embed_disabled" value="true">
          <span class="help-block">Set whether or not a user can embed a ThinkUp thread onto another web site.</span>
        </div>

        <div class="form-group form-group-checkbox">
          <label class="control-label" for="is_opted_out_usage_stats">Disable usage reporting</label>
          <input type="checkbox" class="form-control" name="is_opted_out_usage_stats" id="is_opted_out_usage_stats" value="true">
          <span class="help-block">Usage reporting helps us improve ThinkUp. <a href="http://thinkup.com/docs/userguide/settings/application.html#disable-usage-reporting">Learn more...</a></span>
        </div>
      </fieldset>

      <div style="text-align: center" id="save_setting_image">
          <img id="save_setting_image" src="{$site_root_path}assets/img/loading.gif" width="50" height="50"
          style="display: none; margin: 10px;"/>
      </div>

      <input type="submit" id="app-settings-save" name="Submit" value="Save Settings" class="btn btn-default btn-submit">
    </form>

    <script type="text/javascript"> var site_root_path = '{$site_root_path}';</script>
    <script type="text/javascript" src="{$site_root_path}assets/js/appconfig.js"></script>

    {/if}{* end admin stuff *}

    <header>
      <span class="pull-right">{insert name="help_link" id='rss'}</span>
      <h1>Automate ThinkUp Data Capture</h1>
    </header>

    <div class="settings-set settings-set-simple">
      <header>
        <h2>RSS</h2>
      </header>

      <p>ThinkUp can capture data automatically if you subscribe to this secret RSS feed URL in your favorite newsreader.</p>
      <p><a href="{$rss_crawl_url}" class="btn"><i class="fa fa-rss"></i> Secret ThinkUp Update Feed</a></p>
    </div>

    <div class="settings-set settings-set-simple">
      <header>
        <h2>Scheduling</h2>
      </header>

      <p>Alternately, use the command below to set up a cron job that runs hourly to update your posts. (Be sure to change yourpassword to your real password!)</p>

      <code style="font-family:Courier;" id="clippy_2988">{$cli_crawl_command}</code>

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
    </div>

    <div class="settings-set settings-set-simple">
      <header>
        <h2>Your API Key</h2>
      </header>

      {include file="_usermessage.tpl" field='api_key'}

      <p><strong>Your Current ThinkUp API Key:</strong> <span id="hidden_api_key" style="display: none;">{$owner->api_key}</span>
        <span id="show_api_key" class="btn btn-default">
        <a href="javascript:;" onclick="$('#show_api_key').hide(); $('#hidden_api_key').show();" class="linkbutton">
        Click to view</a>
        </span>
      </p>
      <p>Accidentally share your secret RSS URL?</p>
      <form method="post" action="index.php?m=manage#instances" id="api-key-form">
        <input type="hidden" name="reset_api_key" value="Reset API Key" />
        <span id="apikey_conf" style="display: none;">
        Don't forget! If you reset your API key, you will need to update your ThinkUp crawler RSS feed subscription. This action cannot be undone.
        </span>
        <input type="button" value="Reset Your API Key"
        class="btn btn-warning"
        {literal}
        onclick="if(confirm($('#apikey_conf').html().trim())) { $('#api-key-form').submit();}">
        {/literal}
        {insert name="csrf_token"}<!-- reset api_key -->
      </form>
    </div>

      {if $user_is_admin}
        <div class="section" id="ttusers">
          <div class="thinkup-canvas clearfix">
            <div class="alpha omega grid_20 prefix_1 clearfix prepend_20 append_20">
              <h3><i class="fa fa-user icon-muted"></i> Invite New User</h3>
              {include file="_usermessage.tpl" field='invite'}
              <form name="invite" method="post" action="index.php?m=manage#ttusers" class="prepend_20 append_20">
                {insert name="csrf_token"}<input type="submit" id="login-save" name="invite" value="Create Invitation"
                class="btn btn-primary">
              </form>
            </div>
          </div>

          <h3><i class="fa fa-group icon-muted"></i> Registered Users</h3>

          <table class="table">
          {foreach from=$owners key=oid item=o name=oloop}
            {if $smarty.foreach.oloop.first}
            <thead>
              <tr>
                <th>Name</th>
                <th>Activate</th>
                <th>Admin</th>
              </tr>
            </thead>
            {/if}

            <tr>
              <td>
                <span{if $o->is_admin} style="background-color:#FFFFCC"{/if}>{$o->full_name|filter_xss}</span><br>
                <small>{$o->email|filter_xss}</small>
                <span style="color:#666"><br><small>{if $o->last_login neq '0000-00-00'}logged in {$o->last_login|relative_datetime} ago{/if}</small></span>
                 {if $o->instances neq null}
                 <br><br>Service users:
                 <span style="color:#666"><br><small>
                  {foreach from=$o->instances key=iid item=i}
                      {$i->network_username|filter_xss} | {$i->network|capitalize}
                      {if !$i->is_active} (paused){/if}<br>
                  {/foreach}
                {else}
                   &nbsp;
                {/if}
                </small></span>
              </td>
              <td>
                {if $o->id neq $owner->id}
                <span id="spanowneractivation{$o->id}">
                <input type="submit" name="submit" class="btn {if $o->is_activated}btn-danger{else}btn-success{/if} toggleOwnerActivationButton" id="user{$o->id}" value="{if $o->is_activated}Deactivate{else}Activate{/if}" />
                </span>
                <span style="display: none;" class="linkbutton" id="messageowneractive{$o->id}"></span>
                {/if}
              </td>
              <td>
                {if $o->id neq $owner->id && $o->is_activated}
                <span id="spanowneradmin{$o->id}">
                <input type="submit" name="submit" class="btn {if $o->is_admin}btn-danger{else}btn-success{/if} toggleOwnerAdminButton" id="userAdmin{$o->id}" value="{if $o->is_admin}Demote{else}Promote{/if}" />
                </span>
                <span style="display: none;" class="linkbutton" id="messageadmin{$o->id}"></span>
                {/if}
              </td>
            </tr>
          {/foreach}
          </table>
        </div> <!-- end #ttusers -->

        {/if} <!-- end is_admin -->
  {/if}<!-- /if no $body -->

  </div><!-- end container -->


<script type="text/javascript">
  var show_plugin = {if $force_plugin}true{else}false{/if};
  {literal}
$(function() {
    $(".btnPub").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=1&csrf_token=" + window.csrf_token; // toggle public on
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
        data: dataString,
        success: function() {
          $('#div' + u).html("<span class='btn btn-success' id='messagepub" + u + "'></span>");
          $('#messagepub' + u).html("Set to public!").hide().fadeIn(1500, function() {
            $('#messagepub' + u);
          });
        }
      });
      return false;
    });

    $(".btnPriv").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=0&csrf_token=" + window.csrf_token; // toggle public off
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
        data: dataString,
        success: function() {
          $('#div' + u).html("<span class='btn btn-default' id='messagepriv" + u + "'></span>");
          $('#messagepriv' + u).html("Set to private!").hide().fadeIn(1500, function() {
            $('#messagepriv' + u);
          });
        }
      });
      return false;
    });
  });

  $(function() {
    $(".btnPlay").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=1&csrf_token=" + window.csrf_token; // toggle active on
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
        data: dataString,
        success: function() {
          $('#divactivate' + u).html("<span class='btn btn-success' id='messageplay" + u + "'></span>");
          $('#messageplay' + u).html("Started!").hide().fadeIn(1500, function() {
            $('#messageplay' + u);
          });
        }
      });
      return false;
    });

    $(".btnPause").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=0&csrf_token=" + window.csrf_token; // toggle active off
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
        data: dataString,
        success: function() {
          $('#divactivate' + u).html("<span class='btn btn-warning' id='messagepause" + u + "'></span>");
          $('#messagepause' + u).html("Paused!").hide().fadeIn(1500, function() {
            $('#messagepause' + u);
          });
        }
      });
      return false;
    });
  });

    $(function() {
    var activateOwner = function(u) {
      //removing the "user" from id here to stop conflict with plugin
      u = u.substr(4);
      var dataString = 'oid=' + u + "&a=1&csrf_token=" + window.csrf_token; // toggle owner active on
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
        data: dataString,
        success: function() {
          $('#spanowneractivation' + u).css('display', 'none');
          $('#messageowneractive' + u).html("Activated!").hide().fadeIn(1500, function() {
            $('#messageowneractive' + u);
          });
          $('#spanownernamelink' + u).css('display', 'inline');
          $('#user' + u).val('Deactivate');
          $('#spanownernametext' + u).css('display', 'none');
          $('#user' + u).removeClass('btn-success').addClass('btn-danger');
          $('#userAdmin' + u).show();
          setTimeout(function() {
              $('#messageowneractive' + u).css('display', 'none');
              $('#spanowneractivation' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    var deactivateOwner = function(u) {
      //removing the "user" from id here to stop conflict with plugin
      u = u.substr(4);
      var dataString = 'oid=' + u + "&a=0&csrf_token=" + window.csrf_token; // toggle owner active off
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
        data: dataString,
        success: function() {
          $('#spanowneractivation' + u).css('display', 'none');
          $('#messageowneractive' + u).html("Deactivated!").hide().fadeIn(150, function() {
            $('#messageowneractive' + u);
          });
          $('#spanownernamelink' + u).css('display', 'none');
          $('#spanownernametext' + u).css('display', 'inline');
          $('#user' + u).val('Activate');
          $('#user' + u).removeClass('btn-danger').addClass('btn-success');
          $('#userAdmin' + u).hide();
          setTimeout(function() {
              $('#messageowneractive' + u).css('display', 'none');
              $('#spanowneractivation' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    var promoteOwner = function(u) {
      //removing the "userAdmin" from id here to stop conflict with plugin
      u = u.substr(9);
      var dataString = 'oid=' + u + "&a=1&csrf_token=" + window.csrf_token; // toggle owner active on
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneradmin.php",
        data: dataString,
        success: function() {
          $('#spanowneradmin' + u).css('display', 'none');
          $('#messageadmin' + u).html("Promoted!").hide().fadeIn(1500, function() {
            $('#messageadmin' + u);
          });
          $('#spanownernamelink' + u).css('display', 'inline');
          $('#userAdmin' + u).val('Demote');
          $('#spanownernametext' + u).css('display', 'none');
          $('#userAdmin' + u).removeClass('btn-success').addClass('btn-danger');
          setTimeout(function() {
              $('#messageadmin' + u).css('display', 'none');
              $('#spanowneradmin' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    var demoteOwner = function(u) {
      //removing the "userAdmin" from id here to stop conflict with plugin
      u = u.substr(9);
      var dataString = 'oid=' + u + "&a=0&csrf_token=" + window.csrf_token; // toggle owner active off
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneradmin.php",
        data: dataString,
        success: function() {
          $('#spanowneradmin' + u).css('display', 'none');
          $('#messageadmin' + u).html("Demoted!").hide().fadeIn(1500, function() {
            $('#messageadmin' + u);
          });
          $('#spanownernamelink' + u).css('display', 'none');
          $('#spanownernametext' + u).css('display', 'inline');
          $('#userAdmin' + u).val('Promote');
          $('#userAdmin' + u).removeClass('btn-danger').addClass('btn-success');
          setTimeout(function() {
              $('#messageadmin' + u).css('display', 'none');
              $('#spanowneradmin' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    $(".toggleOwnerActivationButton").click(function() {
      if($(this).val() == 'Activate') {
        activateOwner($(this).attr("id"));
      } else {
        deactivateOwner($(this).attr("id"));
      }
    });

    $(".toggleOwnerAdminButton").click(function() {
      if($(this).val() == 'Promote') {
        promoteOwner($(this).attr("id"));
      } else {
        demoteOwner($(this).attr("id"));
      }
    });

    $('.manage_plugin').click(function (e) {
      var url = $(this).attr('href');
      var p = url.replace(/.*p=/, '').replace(/#.*/, '');;
      if (window.location.href.indexOf("="+p) >= 0) {
        $('.section').hide();
        $('#manage_plugin').show();
        e.preventDefault();
      }
    });
    if ((show_plugin && (!window.location.hash || window.location.hash == '' || window.location.hash == '#_=_' ))
    || (window.location.hash && window.location.hash == '#manage_plugin')) {
      $('.section').hide();
      $('#manage_plugin').show();
    }

  });

  {/literal}
</script>

{include file="_footer.tpl" linkify=0}
{/if}
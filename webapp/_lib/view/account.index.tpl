{include file="_header.tpl" enable_tabs=1 enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}

<div class="container">

<div class="row">
    <div class="span3">
      <div id="tabs" class="embossed-block">
        <ul class="nav nav-tabs nav-stacked">

          <li><a href="#plugins"><i class="icon icon-list-alt"></i> Plugins <i class="icon-chevron-right"></i></a>

          </li>
          {if $user_is_admin}<li><a id="app-settings-tab" href="#app_settings"><i class="icon icon-cogs"></i> Application <i class="icon-chevron-right"></i></a></li>{/if}
          <li><a href="#instances"><i class="icon icon-lock"></i> Account <i class="icon-chevron-right"></i></a></li>
          {if $user_is_admin}<li><a href="#ttusers"><i class="icon icon-group"></i> Users <i class="icon-chevron-right"></i></a></li>{/if}
        </ul>
      </div>
    </div><!--/span3-->
    <div class="span9">
        <div class="white-card">

        <div class="section" id="plugins">

            {include file="_usermessage.tpl" field="account"}
              {if $installed_plugins}
                {foreach from=$installed_plugins key=ipindex item=ip name=foo}
                  {if $smarty.foreach.foo.first}
                    <table class="table">
                      <thead>
                        <tr>
                          <th>&nbsp;</th>
                          <th><i class="icon icon-list-alt icon-2x icon-muted pull-left"></i></th>
                          {if $user_is_admin}<th class="action-button"><i class="icon-cog icon-2x icon-muted"></i></th>{/if}
                        </tr>
                      </thead>
                  {/if}
                  {if $user_is_admin || $ip->is_active}
                        <tr>
                            <td>
                                <img src="{$site_root_path}plugins/{$ip->folder_name|get_plugin_path}/{$ip->icon}" class="pull-right">
                            </td>
                            <td>
                                <p class="lead" style="padding-left: 0px; margin : 0px;">
                                <a href="?p={$ip->folder_name|get_plugin_path}#manage_plugin" class="manage_plugin"><span id="spanpluginnamelink{$ip->id}">{$ip->name}</span></a>
                                </p>
                                <span class="muted">{$ip->description}</span>
                            </td>
                    {if $user_is_admin}
                      <td class="action-button">
                      <span id="spanpluginactivation{$ip->id}" style="margin-top : 4px;">
                          <a href="{$site_root_path}account/?p={$ip->folder_name|get_plugin_path}#manage_plugin" class="manage_plugin btn {if !$ip->isConfigured()}btn-primary{/if}">{if $ip->isConfigured()} <i class="icon-cog "></i> Configure{else}<i class="icon-warning-sign"></i> Set Up{/if}</a>
                      </span>
                      <span style="display: none;" class='linkbutton' id="messageactive{$ip->id}"></span>
                      </td>
                    {/if}
                      </tr>
                  {/if}
                {/foreach}
                    </table>
              {/if}
        </div> <!-- end #plugins -->

		<div class="section" id="manage_plugin" {if $body}style="display: block"{/if}>
            <a href="?m=manage" class="btn btn-mini"><i class="icon-chevron-left icon-muted"></i> Back to plugins</a>
            {if $body}
              {$body}
            {/if}
		</div>

        {if $user_is_admin}
        <div class="section thinkup-canvas clearfix" id="app_settings">

                
          <span class="pull-right">{insert name="help_link" id='backup'}</span>
          <h3><i class="icon-download icon-muted"></i> Back Up and Export Data</h3>
          <p style="padding-left : 20px;">
            <a href="{$site_root_path}install/backup.php" class="btn"><i class="icon icon-download-alt"></i> Back up ThinkUp's entire database</a>
            Recommended before upgrading ThinkUp.
          </p>

          <p style="padding-left : 20px; padding-bottom : 30px;">
            <a href="{$site_root_path}install/exportuserdata.php" class="btn"><i class="icon icon-user"></i> Export a single user account's data</a>
                For transfer into another existing ThinkUp database.
          </p>


          <div class="alert" id="app_setting_loading_div">
            <i class="icon-spinner icon-spin icon-2x"></i> Loading application settings...<br /><br />
          </div>
          <div id="app_settings_div" style="display: none;">
            {include file="account.appconfig.tpl"}
          </div>
          <script type="text/javascript"> var site_root_path = '{$site_root_path}';</script>
          <script type="text/javascript" src="{$site_root_path}assets/js/appconfig.js"></script>
                
        </div> <!-- end #app_setting -->
        {/if}

        <div class="section" id="instances">
          {include file="_usermessage.tpl" field='password'}
          <span class="pull-right">{insert name="help_link" id='account'}</span>
          <h3><i class="icon-key icon-muted"></i> Password</h3>
          <form name="changepass" id="changepass" class="form-horizontal" method="post" action="index.php?m=manage#instances">
            <div class="control-group input-prepend">
              <label for="oldpass" class="control-label">Current password</label>
              <div class="controls">
                <span class="add-on"><i class="icon-key"></i></span>
                <input name="oldpass" type="password" id="oldpass">{insert name="csrf_token"}<!-- reset password -->
              </div>
            </div>
            <div class="control-group">
                    <label class="control-label" for="password">New Password</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-key"></i></span>
                            <input type="password" name="pass1" id="password"
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password" required 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='icon-exclamation-sign'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-inline"></span>

                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="confirm_password">Confirm&nbsp;new Password</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-key"></i></span>            
                            <input type="password" name="pass2" id="confirm_password" required 
                             class="password" 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> Password confirmation is required." 
                            data-validation-match-match="pass1" 
                            data-validation-match-message="<i class='icon-exclamation-sign'></i> Make sure this matches the password you entered above." >
                        </span>
                        <span class="help-block"></span>
                    </div>
                </div>
            <div class="control-group">
              <div class="controls">
                <input type="submit" id="login-save" name="changepass" value="Change password" class="btn btn-primary">
              </div>
            </div>
          </form>
    <br><br>
    <span class="pull-right">{insert name="help_link" id='rss'}</span>
    <h3><i class="icon-refresh icon-muted"></i> Automate ThinkUp Data Capture</h3><br />
    
    <legend>RSS</legend>
    <p>ThinkUp can capture data automatically if you subscribe to this secret RSS feed URL in your favorite newsreader.</p>
    
    <p><a href="{$rss_crawl_url}" class="btn"><i class="icon icon-rss"></i> Secret ThinkUp Update Feed</a></p>
    
    <legend>Scheduling</legend>
    <p>Alternately, use the command below to set up a cron job that runs hourly to update your posts. (Be sure to change yourpassword to your real password!)</p>
    <p>
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
    </p>
    

        <legend>Your API Key</legend>
              {include file="_usermessage.tpl" field='api_key'}
              <strong>Your Current ThinkUp API Key:</strong>
              <span id="hidden_api_key" style="display: none;">{$owner->api_key}</span>
              <span id="show_api_key">
              <a href="javascript:;" onclick="$('#show_api_key').hide(); $('#hidden_api_key').show();" class="linkbutton">
              Click to view</a>
              </span>
    
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
        </div> <!-- end #instances -->

    {if $user_is_admin}
      <div class="section" id="ttusers">

     <div class="thinkup-canvas clearfix">
         <div class="alpha omega grid_20 prefix_1 clearfix prepend_20 append_20">
        <h3><i class="icon-user icon-muted"></i> Invite New User</h3>
        {include file="_usermessage.tpl" field='invite'}
          <form name="invite" method="post" action="index.php?m=manage#ttusers" class="prepend_20 append_20">
                {insert name="csrf_token"}<input type="submit" id="login-save" name="invite" value="Create Invitation" 
                class="btn btn-primary">
          </form>
        </div>

      <h3><i class="icon-group icon-muted"></i> Registered Users</h3>

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

        </div>
    {/if} <!-- end is_admin -->
    </div>
</div>

</div>

<script type="text/javascript">
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
          $('#div' + u).html("<span class='alert alert-success' id='messagepub" + u + "'></span>");
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
          $('#div' + u).html("<span class='alert alert-success' id='messagepriv" + u + "'></span>");
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
          $('#divactivate' + u).html("<span class='alert alert-success' id='messageplay" + u + "'></span>");
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
          $('#divactivate' + u).html("<span class='alert alert-success' id='messagepause" + u + "'></span>");
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
    if (window.location.hash && window.location.hash == '#manage_plugin') {
      $('.section').hide();
      $('#manage_plugin').show();
    }

  });

  {/literal}
</script>

{include file="_footer.tpl" linkify=0 enable_bootstrap=1}

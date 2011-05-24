{include file="_header.tpl" enable_tabs=true}
{include file="_statusbar.tpl"}

<div class="container_24">

  <div role="application" id="tabs">
    
    <ul>
      <li><a href="#plugins">Plugins</a></li>
      {if $user_is_admin}<li><a id="app-settings-tab" href="#app_settings">Application</a></li>{/if}
      <li><a href="#instances">Account</a></li>
      {if $user_is_admin}<li><a href="#ttusers">Users</a></li>{/if}
    </ul>
    
    <div class="section thinkup-canvas clearfix" id="plugins">
      <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">
        <div class="append_20 clearfix">
        {include file="_usermessage.tpl"}
          {if $installed_plugins}
            {foreach from=$installed_plugins key=ipindex item=ip name=foo}
              {if $smarty.foreach.foo.first}
                <div class="clearfix header">
                  <div class="grid_4 alpha">name</div>
                  <div class="grid_14">description</div>
                  {if $user_is_admin}
                  <div class="grid_4 omega">activate/deactivate</div>
                  {/if}
                </div>
              {/if}
              {if $user_is_admin || $ip->is_active}
              <div class="clearfix bt append prepend">
                <div class="grid_4 small alpha"><a href="?p={$ip->folder_name}"><span  id="spanpluginimage{$ip->id}"><img src="{$site_root_path}plugins/{$ip->folder_name}/{$ip->icon}" class="float-l"></span>
                    <span {if !$ip->is_active}style="display: none;"{/if} id="spanpluginnamelink{$ip->id}">{$ip->name}</span></a>
                    <span {if $ip->is_active}style="display: none;"{/if} id="spanpluginnametext{$ip->id}">{$ip->name}</span>
                </div>
                <div class="grid_14">
                  <div style="font-size:14px">{$ip->description}</div>
                  <span style="font-size:12px;color:#999"><a href="{$ip->homepage}">v{$ip->version}</a> by {$ip->author}</span>
                </div>
                {if $user_is_admin}
                <div class="grid_4 omega">
                  <span id="spanpluginactivation{$ip->id}">
                      <input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all btnToggle" id="{$ip->id}" value="{if $ip->is_active}Deactivate{else}Activate{/if}" />
                  </span>
                  <span style="display: none;" class='success mt_10' id="message{$ip->id}"></span>
                  </div>
                {/if}
              </div>
              {/if}
            {/foreach}
          {else}
            <a href="?m=manage" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-w"></span>Back to plugins</a>
          {/if}
        </div>
        {if $body}
          {$body}
        {/if}
      </div>
    </div> <!-- end #plugins -->

    {if $user_is_admin}
    <div class="section thinkup-canvas clearfix" id="app_settings">
        <div style="text-align: center" id="app_setting_loading_div">
            Loading application settings...<br /><br />
            <img src="{$site_root_path}assets/img/loading.gif" width="31" height="31" />
        </div>
        <div id="app_settings_div" style="display: none;">
         {include file="account.appconfig.tpl"}
        </div>
        <script type="text/javascript"> var site_root_path = '{$site_root_path}';</script>
        <script type="text/javascript" src="{$site_root_path}assets/js/appconfig.js"></script>
        
        
   <div class="prepend_20">
    <div style="float:right;margin:20px">{insert name="help_link" id='backup'}</div>
    <h1>Back Up</h1>

    <p>
    <a href="{$site_root_path}install/backup.php">Back up ThinkUp's database</a> (highly recommended before upgrading ThinkUp)
    </p>
  </div>
        
    </div> <!-- end #app_setting -->
    {/if}

    <div class="sections" id="instances">
      <div class="thinkup-canvas clearfix">
        <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">
        {include file="_usermessage.tpl"}
          <form name="changepass" method="post" action="index.php?m=manage#instances" class="prepend_20 append_20">
            <div class="clearfix">
              <div class="grid_9 prefix_1 right"><label for="oldpass">Current password:</label></div>
              <div class="grid_9 left" style="overflow: hidden; margin: 0px 0px 10px 5px;">
                <input name="oldpass" type="password" id="oldpass">
              </div>
            </div>
            <div class="clearfix">
              <div class="grid_9 prefix_1 right"><label for="pass1">New password:</label></div>
              <div class="grid_9 left">
                <input name="pass1" type="password" id="pass1">
                <br>
                <div class="ui-state-highlight ui-corner-all" style="margin: 10px 0px 10px 0px; padding: .5em 0.7em;"> 
                  <p>
                    <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
                    Must be at least 5 characters.
                  </p>
                </div>
              </div>
              <div class="clearfix append_bottom">
                <div class="grid_9 prefix_1 right">
                  <label for="pass2">Re-type new password:</label>
                </div>
                <div class="grid_9 left" style="overflow: hidden; margin: 0px 0px 10px 5px;">
                  <input name="pass2" type="password" id="pass2">
                </div>
              </div>
              <div class="prefix_10 grid_9 left">
                <input type="submit" id="login-save" name="changepass" value="Change password" class="tt-button ui-state-default ui-priority-secondary ui-corner-all">
              </div>
            </div>
          </form>
        </div>
      </div>
    </div> <!-- end #instances -->
    
    {if $user_is_admin}
      <div class="section" id="ttusers">
        <div class="thinkup-canvas clearfix">
          <div class="alpha omega grid_20 prefix_1 clearfix prepend_20 append_20">
          <div style="float:right;margin:20px">{insert name="help_link" id='users'}</div>
            <h1>All Users</h1><br />
            <ul class="user-accounts">
              {foreach from=$owners key=oid item=o}
                <li>
                  <b>{$o->full_name} ({$o->email})</b>{if $o->last_login neq '0000-00-00'}, last logged in {$o->last_login}{/if}{if $o->is_admin}, Administrator<br />{/if}
                  {if !$o->is_admin}
                  <input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all toggleOwnerButton" id="user{$o->id}" value="{if $o->is_activated}Deactivate{else}Activate{/if}" />
                  {/if}
                  <span style="display: none;" class='success mt_10' id="message1{$o->id}"></span>
                  
                  {if $o->instances neq null}
                    <ul>
                      {foreach from=$o->instances key=iid item=i}
                        <li>
                          {$i->network_username} ({$i->network|capitalize})
                          {if !$i->is_active} (paused){/if}
                        </li>
                      {/foreach}
                    </ul>
                  {/if}
                </li>
              {/foreach}
            </ul>
          </div>
          
         <div class="alpha omega grid_20 prefix_1 clearfix prepend_20 append_20">
        <h1>Invite User</h1>
        {include file="_usermessage.tpl"}
          <form name="invite" method="post" action="index.php?m=manage#ttusers" class="prepend_20 append_20">
                <input type="submit" id="login-save" name="invite" value="Create Invitation" class="tt-button ui-state-default ui-priority-secondary ui-corner-all">
          </form>
        </div>
          
          
        </div> <!-- end .thinkup-canvas -->
      </div> <!-- end #ttusers -->
    {/if} <!-- end is_admin -->



   
  </div>
</div>

<script type="text/javascript">
  {literal}
$(function() {
    $(".btnPub").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=1";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
        data: dataString,
        success: function() {
          $('#div' + u).html("<span class='success' id='message" + u + "'></span>");
          $('#message' + u).html("Set to public!").hide().fadeIn(1500, function() {
            $('#message' + u);
          });
        }
      });
      return false;
    });

    $(".btnPriv").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=0";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
        data: dataString,
        success: function() {
          $('#div' + u).html("<span class='success' id='message" + u + "'></span>");
          $('#message' + u).html("Set to private!").hide().fadeIn(1500, function() {
            $('#message' + u);
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
      var dataString = 'u=' + u + "&p=1";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
        data: dataString,
        success: function() {
          $('#divactivate' + u).html("<span class='success mt_10' id='message" + u + "'></span>");
          $('#message' + u).html("Crawling has started!").hide().fadeIn(1500, function() {
            $('#message' + u);
          });
        }
      });
      return false;
    });

    $(".btnPause").click(function() {
      var element = $(this);
      var u = element.attr("id");
      var dataString = 'u=' + u + "&p=0";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
        data: dataString,
        success: function() {
          $('#divactivate' + u).html("<span class='success mt_10' id='message" + u + "'></span>");
          $('#message' + u).html("Crawling has paused!").hide().fadeIn(1500, function() {
            $('#message' + u);
          });
        }
      });
      return false;
    });
  });

  $(function() {
    var activate = function(u) {
      var dataString = 'pid=' + u + "&a=1";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-pluginactive.php",
        data: dataString,
        success: function() {
          $('#spanpluginactivation' + u).css('display', 'none');
          $('#message' + u).html("Activated!").hide().fadeIn(1500, function() {
            $('#message' + u);
          });
          $('#spanpluginnamelink' + u).css('display', 'inline');
          $('#' + u).val('Deactivate');
          $('#spanpluginnametext' + u).css('display', 'none');
          $('#' + u).removeClass('btnActivate');
          $('#' + u).addClass('btnDectivate');
          setTimeout(function() {
              $('#message' + u).css('display', 'none');
              $('#spanpluginactivation' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    var deactivate = function(u) {
      var dataString = 'pid=' + u + "&a=0";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-pluginactive.php",
        data: dataString,
        success: function() {
          $('#spanpluginactivation' + u).css('display', 'none');
          $('#message' + u).html("Deactivated!").hide().fadeIn(1500, function() {
            $('#message' + u);
          });
          $('#spanpluginnamelink' + u).css('display', 'none');
          $('#spanpluginnametext' + u).css('display', 'inline');
          $('#' + u).val('Activate');
          $('#' + u).removeClass('btnDeactivate');
          $('#' + u).addClass('btnActivate');
          setTimeout(function() {
              $('#message' + u).css('display', 'none');
              $('#spanpluginactivation' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    $(".btnToggle").click(function() {
      if($(this).val() == 'Activate') {
        activate($(this).attr("id"));
      } else {
        deactivate($(this).attr("id"));
      }
    });
  });
  
    $(function() {
    var activateOwner = function(u) {
      //removing the "user" from id here to stop conflict with plugin    
      u = u.substr(4);
      var dataString = 'oid=' + u + "&a=1";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
        data: dataString,
        success: function() {
          $('#spanowneractivation' + u).css('display', 'none');
          $('#message1' + u).html("Activated!").hide().fadeIn(1500, function() {
            $('#message1' + u);
          });
          $('#spanownernamelink' + u).css('display', 'inline');
          $('#user' + u).val('Deactivate');
          $('#spanownernametext' + u).css('display', 'none');
          $('#user' + u).removeClass('btnActivate');
          $('#user' + u).addClass('btnDectivate');
          setTimeout(function() {
              $('#message1' + u).css('display', 'none');
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
      var dataString = 'oid=' + u + "&a=0";
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
        data: dataString,
        success: function() {
          $('#spanowneractivation' + u).css('display', 'none');
          $('#message1' + u).html("Deactivated!").hide().fadeIn(1500, function() {
            $('#message1' + u);
          });
          $('#spanownernamelink' + u).css('display', 'none');
          $('#spanownernametext' + u).css('display', 'inline');
          $('#user' + u).val('Activate');
          $('#user' + u).removeClass('btnDeactivate');
          $('#user' + u).addClass('btnActivate');
          setTimeout(function() {
              $('#message1' + u).css('display', 'none');
              $('#spanowneractivation' + u).hide().fadeIn(1500);
            },
            2000
          );
        }
      });
      return false;
    };

    $(".toggleOwnerButton").click(function() {
      if($(this).val() == 'Activate') {
        activateOwner($(this).attr("id"));
      } else {
        deactivateOwner($(this).attr("id"));
      }
    });
  });

  {/literal}
</script>

{include file="_footer.tpl"}

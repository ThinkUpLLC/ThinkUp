{include file="_header.tpl" enable_tabs=true}
{include file="_statusbar.tpl"}
<div class="container_24">

  <div role="application" id="tabs">
    
    <ul>
      <li><a href="#plugins">Plug-ins</a></li>
      <li><a href="#instances">Your ThinkUp Password</a></li>
      {if $user_is_admin}<li><a href="#ttusers">All ThinkUp Accounts</a></li>{/if}
    </ul>
    
    <div class="section thinkup-canvas clearfix" id="plugins">
      <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">
        <div class="append_20 clearfix">
        {if $user_is_admin}
        <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
              <p>
                <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
                As an administrator you can configure all installed plugins.
              </p>
            </div>
        {/if}
        {include file="_usermessage.tpl"}
          {if $installed_plugins}
            {foreach from=$installed_plugins key=ipindex item=ip name=foo}
              {if $smarty.foreach.foo.first}
                <div class="clearfix header">
                  <div class="grid_4 alpha">configure</div>
                  <div class="grid_4">version/author</div>
                  <div class="grid_10">description</div>
                  {if $is_user_admin}
                  <div class="grid_4 omega">activate/deactivate</div>
                  {/if}
                </div>
              {/if}
              {if $user_is_admin || $ip->is_active}
              <div class="clearfix bt append prepend">
                <div class="grid_4 small alpha"><span  id="spanpluginimage{$ip->id}"><img src="{$site_root_path}plugins/{$ip->folder_name}/{$ip->icon}" class="float-l"></span>
                    <span {if !$ip->is_active}style="display: none;"{/if} id="spanpluginnamelink{$ip->id}"><a href="?p={$ip->folder_name}">{$ip->name}</a></span>
                    <span {if $ip->is_active}style="display: none;"{/if} id="spanpluginnametext{$ip->id}">{$ip->name}</span>
                </div>
                <div class="grid_4 small"><!--(Currently {if $ip->is_active}Active{else}Inactive{/if})<br />-->Version {$ip->version}<br />by {$ip->author}</div>
                <div class="grid_10">
                  {$ip->description}
                  <a href="{$ip->homepage}">[Plug-in home]</a>
                </div>
                {if $user_is_admin}
                <div class="grid_4 omega">
                  <span id="spanpluginactivation{$ip->id}">
                      <input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all btnToggle" id="{$ip->id}" value="{if $ip->is_active}Deactivate{else}Activate{/if}" />
                  </span>
                  <span style="display: none;" class='success mt_10' id="message{$ip->id}"></span>                </div>
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
    
    <div class="section" id="instances">
      <div class="thinkup-canvas clearfix">
        <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">
{include file="_usermessage.tpl"}
          <form name="changepass" method="post" action="index.php?m=manage#instances" class="login prepend_20 append_20">
            <div class="clearfix">
              <div class="grid_9 prefix_1 right"><label for="oldpass">Current password:</label></div>
              <div class="grid_9 left"><input name="oldpass" type="password" id="oldpass"></div>
            </div>
            <div class="clearfix">
              <div class="grid_9 prefix_1 right"><label for="pass1">New password:</label></div>
              <div class="grid_9 left">
                <input name="pass1" type="password" id="pass1">
                <br>
                <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: .5em 0.7em;"> 
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
                <div class="grid_9 left">
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
            <h2 class="subhead">User accounts in this ThinkUp installation</h2>
            <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
              <p>
                <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
                As an administrator you can see all accounts in the system.
              </p>
            </div>
            <ul class="user-accounts">
              {foreach from=$owners key=oid item=o}
                <li>
                  <b>{$o->full_name} ({$o->email})</b>{if $o->last_login neq '0000-00-00'}, last logged in {$o->last_login}{/if}
                  <br />{if $o->is_activated}Activated{else}Not activated{/if}{if $o->is_admin}, Administrator{/if}
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
  {/literal}
</script>

{include file="_footer.tpl"}
{include file="_install.header.tpl"}
  <div class="container">
    <div id="thinkup-tabs">
      <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <li id="step-tab-1" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-1">1</span></h1>
            <h3>Check System Requirements</h3>
            </div>  
          </li>
          <li id="step-tab-2" class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>2</h1>
            <h3>Configure {$app_title}</h3>
            </div>
          </li>
          <li id="step-tab-3" class="no-border ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1>3</h1>
            <h3>Finish</h3>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div id="installer-page" class="container_24 round-all">
    <img id="dart2" class="dart" alt="" src="{$site_root_path}assets/img/dart_wht.png">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        <form class="input" name="form1" method="post" action="index.php?step=3">
            {include file="_usermessage.tpl"}

            <h2 class="clearfix step_title">Create Your {$app_title} Account</h2>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="full_name" id="full_name"{if isset($full_name)} value="{$full_name}"{/if}>
            </div>
          </div>
          <div class="clearfix append_20">
          {include file="_usermessage.tpl" field="email"}
            <div class="grid_5 prefix_3 right">
              <label>Email&nbsp;Address</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
            </div>
          </div>

          <div class="clearfix append_20">
          {include file="_usermessage.tpl" field="password"}
            <div class="grid_5 prefix_3 right">
              <label>Choose&nbsp;Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="password" name="password" id="password"{if isset($password)} value="{$password}"{/if}>
            </div>
          </div>

          <div class="clearfix append_20">
            <div class="grid_6 prefix_2 right">
              <label>Confirm Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="password" name="confirm_password" id="confirm_password"{if isset($confirm_password)} value="{$confirm_password}"{/if}>
            </div>
          </div>

          <div class="clearfix append_20">
          {include file="_usermessage.tpl" field="timezone"}
            <div class="grid_6 prefix_2 right">
              <label>Time Zone</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <select name="timezone" id="timezone" style="margin-top:1.25em">
              <option value=""{if $current_tz eq ''} selected{/if}>Select a Time Zone:</option>
                {foreach from=$tz_list key=group_name item=group}
                  <optgroup label='{$group_name}'>
                    {foreach from=$group item=tz}
                      <option value='{$tz.val}'{if $current_tz eq $tz.val} selected{/if}>{$tz.display}</option>
                    {/foreach}
                  </optgroup>
                {/foreach}
              </select>
              <script type="text/javascript" src="{$site_root_path}assets/js/extlib/detect_timezone.js"></script>
              <script type="text/javascript">
              {literal}
              var tz_info = jstz.determine_timezone().timezone;
              var tz_option_id = '#tz-' + tz_info.olson_tz;
              if( $('#timezone option[value=' + tz_info.olson_tz + ']').length > 0) {
                  if( $(tz_option_id) ) {
                      $('#timezone').val(tz_info.olson_tz);
                  }
              }
              {/literal}
              </script>
              <span class="input_information">Defaults to your browser's time zone; will fall back to <a href="http://www.php.net/manual/en/function.date-default-timezone-get.php">php.ini's date.time zone value</a>.</span>
            </div>
          </div>

          <h2 class="clearfix step_title">Connect {$app_title} to Your Database</h2>

          <div class="clearfix append_20">
          {include file="_usermessage.tpl" field="database"}
          {include file="_usermessage.tpl" field="database_host"}
            <div class="grid_5 prefix_3 right">
              <label>Database Host</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_host" id="db_host"{if isset($db_host)} value="{$db_host}"{/if}>
              <span class="input_information">This is usually <strong>localhost</strong> or a host name specified by 
              your hosting provider.</span>
            </div>
          </div>
          

          <div class="clearfix append_20">
          {include file="_usermessage.tpl" field="database_name"}
            <div class="grid_5 prefix_3 right">
              <label>Database Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if}>
              <span class="input_information">If the database does not exist, {$app_title} will attempt to create it.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>User Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if}>
              <span class="input_information">Your MySQL username.</span>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Password</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="password" name="db_passwd" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
              <span class="input_information">Your MySQL password.</span>
            </div>
          </div>

          <h2 class="clearfix step_title toggle-advanced-options">
            <a href="#">Advanced Options<span class="ui-icon ui-icon-circle-triangle-e"></span></a>
          </h2>
          <div id="database-advance-options">
              <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;">
                <p>
                  <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
                  These options are only necessary for some sites. If you're not sure what you should enter here,
                  leave the default settings or check with your hosting provider.
                </p>
             </div>

              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Database Socket</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_socket" id="db_socket"{if isset($db_socket)} value="{$db_socket}"{/if}>
                  <span class="input_information">If you're not sure about this, leave it blank.</span>
                </div>
              </div>

              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Database Port</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_port" id="db_port"{if isset($db_port)} value="{$db_port}"{/if}>
                  <span class="input_information">If you're not sure about this, leave it blank.</span>
                </div>
              </div>

              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Table Prefix</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_prefix" id="db_prefix"{if isset($db_prefix)} value="{$db_prefix}"{/if}>
                  <span class="input_information">Optional prefix for your {$app_title} tables.</span>
                </div>
              </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_9 left">
              <input type="submit" name="Submit" class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
{include file="_install.footer.tpl"}
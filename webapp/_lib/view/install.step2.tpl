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
            <h3>Configure ThinkUp</h3>
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

            <h2 class="clearfix step_title">Create Your ThinkUp Account</h2>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="full_name" id="full_name"{if isset($full_name)} value="{$full_name}"{/if}>
            </div>
          </div>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Email&nbsp;Address</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
            </div>
          </div>

          <div class="clearfix append_20">
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
            <div class="grid_6 prefix_2 right">
              <label>Your Time Zone</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <select name="timezone" id="timezone" style="margin-top:1.25em">
                  <option value="Kwajalein" title="-12">(GMT -12:00) Eniwetok, Kwajalein</option>
				      <option value="Pacific/Samoa" title="-11">(GMT -11:00) Midway Island, Samoa</option>
				      <option value="Pacific/Honolulu" title="-10">(GMT -10:00) Hawaii</option>
				      <option value="America/Anchorage" title="-9">(GMT -9:00) Alaska</option>
				      <option value="America/Los_Angeles" title="-8">(GMT -8:00) Pacific Time (US &amp; Canada)</option>
				      <option value="America/Denver" title="-7">(GMT -7:00) Mountain Time (US &amp; Canada)</option>
				      <option value="America/Chicago" title="-6">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
				      <option value="America/New_York" title="-5">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
						<option value="America/Caracas" title="-4.5">(GMT -4:30) Caracas</option>      
				      <option value="America/Halifax" title="-4">(GMT -4:00) Atlantic Time (Canada), La Paz</option>
				      <option value="America/St_Johns" title="-3.5">(GMT -3:30) Newfoundland</option>
				      <option value="America/Argentina/Buenos_Aires" title="-3">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
				      <option value="Atlantic/South_Georgia" title="-2">(GMT -2:00) Mid-Atlantic</option>
				      <option value="Atlantic/Cape_Verde" title="-1">(GMT -1:00 hour) Azores, Cape Verde Islands</option>
				      <option value="Europe/London" title="0">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
				      <option value="Europe/Paris" title="1">(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
				      <option value="Africa/Windhoek" title="2">(GMT +2:00) Kaliningrad, South Africa</option>
				      <option value="Asia/Baghdad" title="3">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
				      <option value="Asia/Tehran" title="3.5">(GMT +3:30) Tehran</option>
				      <option value="Asia/Muscat" title="4">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
				      <option value="Asia/Kabul" title="4.5">(GMT +4:30) Kabul</option>
				      <option value="Asia/Karachi" title="5">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
				      <option value="Asia/Kolkata" title="5.5">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
				      <option value="Asia/Katmandu" title="5.75">(GMT +5:45) Kathmandu</option>
				      <option value="Asia/Dhaka" title="6">(GMT +6:00) Almaty, Dhaka, Colombo</option>
						<option value="Asia/Rangoon" title="6.5">(GMT +6:30) Rangoon</option>
				      <option value="Asia/Bangkok" title="7">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
				      <option value="Asia/Hong_Kong" title="8">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
				      <option value="Asia/Tokyo" title="9">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
				      <option value="Australia/Adelaide" title="9.5">(GMT +9:30) Adelaide, Darwin</option>
				      <option value="Australia/Sydney" title="10">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
				      <option value="Asia/Magadan" title="11">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
				      <option value="Pacific/Auckland" title="12">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
              </select>
              <span class="input_information">Choose the location closest to you.</span>
            </div>
          </div>

          <h2 class="clearfix step_title">Connect ThinkUp to Your Database</h2>

          <div class="clearfix append_20">
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
            <div class="grid_5 prefix_3 right">
              <label>Database Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if}>
              <span class="input_information">If the database does not exist, ThinkUp will attempt to create it.</span>
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
                  <span class="input_information">Optional prefix for your ThinkUp tables.</span>
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
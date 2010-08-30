{include file="_install.header.tpl"}
  <div class="container">
    <div id="thinkup-tabs">
      <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <li id="step-tab-1" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-1">1</span></h1>
            <h3>Requirements Check</h3>
            </div>  
          </li>
          <li id="step-tab-2" class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>2</h1>
            <h3>Database Setup and Site Configuration</h3>
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
              <label>Your&nbsp;Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="full_name" id="full_name"{if isset($full_name)} value="{$full_name}"{/if}>
            </div>
          </div>
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Your&nbsp;Email&nbsp;Address</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="site_email" id="site_email"{if isset($site_email)} value="{$site_email}"{/if}>
            </div>
          </div>
          
          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Password</label>
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

          <h2 class="clearfix step_title">Tell Us About Your Database</h2>

          <div class="clearfix append_20">
            <div class="grid_5 prefix_3 right">
              <label>Database Name</label>
            </div>
            <div class="grid_10 prefix_1 left">
              <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if}>
              <span class="input_information">The name of the MySQL database your ThinkUp data will be stored in.</span>
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
                  <label>Database Host</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_host" id="db_host"{if isset($db_host)} value="{$db_host}"{/if}>
                  <span class="input_information">This is usually <strong>localhost</strong> or a host name provided by 
                  the hosting provide.</span>
                </div>
              </div>
              
              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Database Socket</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_socket" id="db_socket"{if isset($db_socket)} value="{$db_socket}"{/if}>
                  <span class="input_information">Leave it blanks if you're not sure about this.</span>
                </div>
              </div>
              
              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Database Port</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_port" id="db_port"{if isset($db_port)} value="{$db_port}"{/if}>
                  <span class="input_information">Leave it blanks if you're not sure about this.</span>
                </div>
              </div>
              
              <div class="clearfix append_20">
                <div class="grid_5 prefix_3 right">
                  <label>Table Prefix</label>
                </div>
                <div class="grid_10 prefix_1 left">
                  <input type="text" name="db_prefix" id="db_prefix"{if isset($db_prefix)} value="{$db_prefix}"{/if}>
                  <span class="input_information">Prefix of your table name</span>
                </div>
              </div>
          </div>
          
          
          <div class="clearfix append_20">
            <div class="grid_10 prefix_9 left">
              <input type="submit" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Next Step &raquo">
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
{include file="_install.footer.tpl"}
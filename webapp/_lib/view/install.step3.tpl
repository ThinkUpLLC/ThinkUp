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
          <li id="step-tab-2" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-2">2</span></h1>
            <h3>Database Setup and Site Configuration</h3>
            </div>
          </li>
          <li id="step-tab-3" class="no-border ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>{if empty($errors)}<span class="pass_step" id="pass-step-3">3</span>{else}3{/if}</h1>
            <h3>Finish</h3>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  
  <div id="installer-page" class="container_24 round-all">
    <img id="dart3" class="dart" alt="" src="{$site_root_path}assets/img/dart_wht.png">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
          <p class="success" style="margin-bottom: 30px">
            Congratulations! ThinkUp has been installed. Check your email account; an account activation message has been sent.
          </p>
        
      </div>
    </div>
  </div>
</body>
</html>
{include file="_install.footer.tpl"}
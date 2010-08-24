{include file="_install.header.tpl"}
  <div class="container">
    <div id="thinkup-tabs">
      <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <li id="step-tab-1" class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active">
            <div class="key-stat install_step">
            <h1>1</h1>
            <h3>Requirements Check</h3>
            </div>  
          </li>
          <li id="step-tab-2" class="ui-state-default ui-corner-top">
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
    <img id="dart1" class="dart" alt="" src="{$site_root_path}assets/img/dart_wht.png">
    <div class="clearfix append_20">
      <div class="grid_22 push_1 clearfix">
        <h2 class="clearfix step_title">Requirements Check</h2>
        {if $permission.logs && $permission.compiled_view && $permission.cache && $php_compat && $libs.curl && $libs.gd}
        <p class="success" style="margin-bottom: 30px">
             <strong>Great!</strong> Your system has everything it needs to run ThinkUp.
             You may proceed to the next step.
        </p>
        {else}
        <div class="ui-state-error ui-corner-all" style="margin-bottom: 20px; padding: 0.5em 0.7em;">
             <p>
               <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
               <strong>Oops!</strong> Your system requirements didn't check out. Details below.  
             </p>
        </div>
        {/if}
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right">
            <span class="label{if !$php_compat} no{/if}">PHP Version >= 5.2</span>
          </div>
          <div class="grid_8 prefix_1 left">
            {if $php_compat}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        {if !$php_compat}
        <div class="clearfix append_20 info_message">
          <p>thinkup needs PHP version greater or equal to v.{$php_required_version}</p>
        </div>
        {/if}
        
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right">
            <span class="label{if !$libs.curl} no{/if}">cURL installed</span>
          </div>
          <div class="grid_8 prefix_1 left">
            {if $libs.curl}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        {if !$libs.curl}
        <div class="clearfix append_20 info_message">
          <p>ThinkUp needs cURL installed on your system.</p>
        </div>
        {/if}
        
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right">
            <span class="label {if !$libs.gd} no{/if}">GD lib installed</span>
          </div>
          <div class="grid_8 prefix_1 left">
            {if $libs.gd}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        {if !$libs.gd}
        <div class="clearfix append_20 info_message">
          <p>Thinkup needs GD lib installed on your system.</p>
        </div>
        {/if}
        
        <div class="clearfix append_20">
          <div class="grid_6 prefix_5 right">
            {if $permissions_compat}
            <span class="label">Template and Log directories are writeable?</span>
            {else}
            <span class="label no">Template and Log directories are writeable?</span>
            {/if}
          </div>
          <div class="grid_8 prefix_1 left">
            {if $permissions_compat}
            <span class="value yes">Yes</span>
            {else}
            <span class="value no">No</span>
            {/if}
          </div>
        </div>
        {if !$permissions_compat}
        <div class="clearfix append_20 info_message">
          <p>Make sure the following directories are writeable by the web server:</p>
          <p><code>{$writeable_directories.logs}</code></p>
          <p><code>{$writeable_directories.compiled_view}</code></p>
          <p><code>{$writeable_directories.cache}</code></p>
          <p class="prepend_20">If you have command line (SSH) access to your web server then you can simply copy and paste the following command into your shell:</p>
          <p><code>chmod -R 777 {$writeable_directories.logs} {$writeable_directories.compiled_view} {$writeable_directories.cache}</code></p>
        </div>
        {/if}
        
        {if $requirements_met}
        <div class="clearfix">
          <div class="grid_10 prefix_8 left">
            <div class="next_step tt-button ui-state-default ui-priority-secondary ui-corner-all">
              <a href="index.php?step=2">Next Step &raquo;</a>
            </div>
          </div>
        </div>
        {/if}
        
      </div>
    </div>
  </div>
{include file="_install.footer.tpl"}
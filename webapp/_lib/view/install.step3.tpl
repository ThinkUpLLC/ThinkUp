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
          <li id="step-tab-2" class="ui-state-default ui-corner-top">
            <div class="key-stat install_step">
            <h1><span class="pass_step" id="pass-step-2">2</span></h1>
            <h3>Configure ThinkUp</h3>
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
      <h2 class="clearfix step_title">Congratulations!</h2>

     <div class="ui-state-success ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
            ThinkUp has been installed successfully. Check your email account; an account activation message has been sent.
         </p>
     </div> 

    
    
<br /><br />
       <div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.5em 0.7em;"> 
        <p>
             <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
             <b>Help! I didn't get the email.</b><br />
             If you didn't receive your account activation email, then PHP's 
             <a href="http://php.net/manual/en/function.mail.php">mail function</a> isn't working correctly on your server.
              You may activate your account manually by setting the is_activated field equal to 1 in ThinkUp's
             owners table, but keep in mind that ThinkUp's registration and forgot password features require
             that the mail() function can send out email messages successfully.
        </p>
       </div>
             </div>
       
    </div>
  </div>
{include file="_install.footer.tpl"}

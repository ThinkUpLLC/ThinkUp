  <div class="prepend_20">
    <h1>Application Settings</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
    {include file="_usermessage.tpl"}
    </div>
  </div>

<div id="settings_error_message_error" 
    class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em; display: none;">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
        <span id="settings_error_message"></span>
    </p>
</div>

<p class="success" id="settings_success" style="display: none;">
    Settings Saved!
</p>

<form id="app-settings-form" name="app_settings" method="post" action="{$site_root_path}session/app_settings.php"
  onsubmit="return false">
    <div class="clearfix" style="width: 640px;">

      <div style="clear:both;"></div>
      <div style="float: left;">
        <label for="is_registration_open">
          Open registration to new ThinkUp users:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_registration_open" id="is_registration_open" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 10px; margin: 0px 0px 10px 0px;">
        Set whether or not your site's registration page is available and accepts new user registrations.
      </div>

      <div style="float: left;">
        <label for="is_api_enabled">
          Enable the JSON API:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_api_enabled" id="is_api_enabled" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 10px; margin: 0px 0px 10px 0px;">
        Set whether or not your site's data is available via ThinkUp's JSON API. {insert name="help_link" id="api"}
      </div>
      
      <div style="float: left;">
        <label for="recaptcha_enable">
          Enable reCAPTCHA:
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="recaptcha_enable" id="recaptcha_enable" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 10px; margin: 0px 0px 10px 0px;">
        Select to enable reCAPTCHA, and <a href="https://www.google.com/recaptcha">get your reCAPTCHA keys here</a>.
      </div>

      <div id="recaptcha_enable_deps" style="display: none; width: 450px; margin: 10px 0px 60px 20px;">
          <div style="float: left;">
            <label for="recaptcha_public_key">
              reCAPTCHA Public Key:
            </label>
          </div>
          <div style="float: right;">
            <input type="text" name="recaptcha_public_key" id="recaptcha_public_key" value="">
          </div>

          <div style="clear:both;"></div>
          <div style="float: left;">
            <label for="recaptcha_private_key">
              reCAPTCHA Private Key:
            </label>
          </div>
          <div style="float: right;">
            <input type="text" name="recaptcha_private_key" id="recaptcha_private_key" value="">
          </div>
       </div>

   </div>

    <div style="text-align: center" id="save_setting_image">
        <img  id="save_setting_image" src="{$site_root_path}assets/img/loading.gif" width="31" height="31"  
        style="display: none; margin: 10px;"/>
    </div>
        
    <div class="clearfix">
      <div class="grid_10 prefix_9 left">
        <input type="submit" id="app-settings-save" name="Submit" 
        class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Save Settings">
      </div>
    </div>

</form>


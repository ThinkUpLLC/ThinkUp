
  <span class="pull-right">{insert name="help_link" id='application_settings'}</span>
  <h3><i class="icon icon-cogs icon-muted"></i> Application Settings</h3>
  {include file="_usermessage.tpl"}

 <div class="alert alert-error" id="settings_error_message_error" style="display: none;">
  <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
  <span id="settings_error_message"></span>
</div>


 <div class="alert alert-success"  id="settings_success" style="display: none;">
  <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
  Settings saved!
 </div>

<form id="app-settings-form" name="app_settings" method="post" action="{$site_root_path}session/app_settings.php"
  onsubmit="return false">

<legend>Test Stuff Out</legend>

      <label class="checkbox">
        <input type="checkbox" name="is_subscribed_to_beta" id="is_subscribed_to_beta" value="true"> Enable beta upgrades
      </label>
      <span class="help-block">Test bleeding edge, beta upgrades. May require command line server access. Proceed at your own risk.</span>

      <label class="checkbox">
        <input type="checkbox" name="is_log_verbose" id="is_log_verbose" value="true"> Enable developer log
      </label>
      <span class="help-block">See the verbose, unformatted developer log on the Capture Data screen.</span>

<legend>Let People In</legend>

      <label class="checkbox">
        <input type="checkbox" name="is_registration_open" id="is_registration_open" value="true"> Open registration to new ThinkUp users
      </label>
      <span class="help-block">Set whether or not your site's registration page is available and accepts new user registrations.</span>

      <label class="checkbox">
        <input type="checkbox" name="recaptcha_enable" id="recaptcha_enable" value="true"> Enable reCAPTCHA
      </label>
      <span class="help-block">Add reCAPTCHA to ThinkUp's registration form. <a href="https://www.google.com/recaptcha">Get your reCAPTCHA keys here</a>.</span>

      <label for="default_instance">
        Default service user:

      <select name="default_instance" id="default_instance">
        <option value="0">Last updated</option>
        {foreach from=$public_instances item=pi}
          <option value="{$pi->id}">{$pi->network_username} - {$pi->network|capitalize}</option>
        {/foreach}
      </select>
      </label>
      
      <span class="help-block">Set the service user to display by default. {insert name="help_link" id="default_service_user"}</span>

      <div id="recaptcha_enable_deps" style="display: none; width: 470px; margin: 0 0 20px 0;">
        <label for="recaptcha_public_key">reCAPTCHA Public Key</label>
        <input type="text" name="recaptcha_public_key" id="recaptcha_public_key" value="">
        <label for="recaptcha_private_key">reCAPTCHA Private Key</label>
        <input type="text" name="recaptcha_private_key" id="recaptcha_private_key" value="">
      </div>

<legend>Don't Share Data</legend>

      <label class="checkbox">
        <input type="checkbox" name="is_api_disabled" id="is_api_disabled" value="true"> Disable the JSON API
      </label>
      <span class="help-block">Set whether or not your site's data is available via ThinkUp's JSON API. <a href="http://thinkup.com/docs/userguide/api/posts/index.html">Learn more...</a></span>

      <label class="checkbox">
        <input type="checkbox" name="is_embed_disabled" id="is_embed_disabled" value="true"> Disable thread embeds
      </label>
      <span class="help-block">Set whether or not a user can embed a ThinkUp thread onto another web site.</span>

      <label class="checkbox">
        <input type="checkbox" name="is_opted_out_usage_stats" id="is_opted_out_usage_stats" value="true"> Disable usage reporting
      </label>
      <span class="help-block">Usage reporting helps us improve ThinkUp. <a href="http://thinkup.com/docs/userguide/settings/application.html#disable-usage-reporting">Learn more...</a></span>

    <div style="text-align: center" id="save_setting_image">
        <img  id="save_setting_image" src="{$site_root_path}assets/img/loading.gif" width="50" height="50"  
        style="display: none; margin: 10px;"/>
    </div>
        
    <div class="clearfix">
      <div class="grid_10 prefix_9 left">
        <input type="submit" id="app-settings-save" name="Submit" 
        class="btn btn-primary" value="Save Settings">
      </div>
    </div>

</form>

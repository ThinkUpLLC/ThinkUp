  <div class="prepend_20">
  {insert name="help_link" id='application_settings'}
    <h1>Application Settings</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
    {include file="_usermessage.tpl"}
    </div>
  </div>

 <div class="alert urgent" id="settings_error_message_error" style="display: none;">
     <p>
       <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
       <span id="settings_error_message"></span>
     </p>
</div>


 <div class="alert helpful"  id="settings_success" style="display: none;">
     <p>
       <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
       Settings saved!
     </p>
 </div>    

<form id="app-settings-form" name="app_settings" method="post" action="{$site_root_path}session/app_settings.php"
  onsubmit="return false">
    <div class="clearfix" style="width: 640px;">

      <div style="float: left;">
        <label for="default_instance">
          Default service user:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <select name="default_instance" id="default_instance">
        <option value="0">Last updated</option>
        {foreach from=$public_instances item=pi}
            <option value="{$pi->id}">{$pi->network_username} - {$pi->network|capitalize}</option>
        {/foreach}
        </select>
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Set the service user to display by default. {insert name="help_link" id="default_service_user"}
      </div>

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
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Set whether or not your site's registration page is available and accepts new user registrations.
      </div>

      <div style="float: left;">
        <label for="is_log_verbose">
          Enable developer log:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_log_verbose" id="is_log_verbose" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        See the verbose, unformatted developer log on the Capture Data screen.
      </div>
   </div>

      <div style="float: left;">
        <label for="recaptcha_enable">
          Enable reCAPTCHA: 
          <br>
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="recaptcha_enable" id="recaptcha_enable" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Add reCAPTCHA to ThinkUp's registration form. <a href="https://www.google.com/recaptcha">Get your reCAPTCHA keys here</a>.
      </div>

      <div id="recaptcha_enable_deps" style="display: none; width: 470px; margin: 10px 0px 60px 20px;">
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

      <div style="float: left;">
        <label for="is_subscribed_to_beta">
          Enable beta upgrades:
          <br>
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_subscribed_to_beta" id="is_subscribed_to_beta" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Test bleeding edge, beta upgrades. May require command line server access. Proceed at your own risk.
      </div>

      <div style="float: left;">
        
        <label for="is_api_disabled">
          Disable the JSON API:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_api_disabled" id="is_api_disabled" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Set whether or not your site's data is available via ThinkUp's JSON API. <a href="http://thinkupapp.com/docs/userguide/api/posts/index.html">Learn more...</a> 
      </div>

      <div style="float: left;">
        <label for="is_embed_disabled">
          Disable thread embeds:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_embed_disabled" id="is_embed_disabled" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Set whether or not a user can embed a ThinkUp thread onto another web site.
      </div>

      <div style="float: left;">
        <label for="is_opted_out_usage_stats">
          Disable usage reporting:
          <br />
        </label>
      </div>
      <div style="float: left;">
        <input type="checkbox" name="is_opted_out_usage_stats" id="is_opted_out_usage_stats" value="true">
      </div>
      <div style="clear:both;"></div>
      <div style="font-size: 12px; color : #555; margin: 0px 0px 10px 0px;">
        Usage reporting helps us improve ThinkUp. <a href="http://thinkupapp.com/docs/userguide/settings/application.html#disable-usage-reporting">Learn more...</a>
      </div>

    <div style="text-align: center" id="save_setting_image">
        <img  id="save_setting_image" src="{$site_root_path}assets/img/loading.gif" width="50" height="50"  
        style="display: none; margin: 10px;"/>
    </div>
        
    <div class="clearfix">
      <div class="grid_10 prefix_9 left">
        <input type="submit" id="app-settings-save" name="Submit" 
        class="linkbutton emphasized" value="Save Settings">
      </div>
    </div>

</form>

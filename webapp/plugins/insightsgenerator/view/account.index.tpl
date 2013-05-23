<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='insightsgenerator'}</span>
    <h1>
        <img src="{$site_root_path}plugins/insightsgenerator/assets/img/plugin_icon.png" class="plugin-image">
        Insights Generator Plugin
    </h1>

    <p>{$message}</p>

</div>

    <div>
    <p>The following is a list of currently installed and running insight plugins:</p>
    {if $user_is_admin}
    <div class="alert" id="insightsgenerator_setting_loading_div">
    	<i class="icon-spinner icon-spin icon-2x"></i> Loading application settings...<br /><br />
    </div>
     <div class="alert alert-error" id="plugin_settings_error_message_error" style="display: none;">
      <span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
      <span id="plugin_settings_error_message"></span>
    </div>
    
    
     <div class="alert alert-success"  id="plugin_settings_success" style="display: none;">
      <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
      Settings saved!
     </div>
    <form id="insightsgenerator-settings-form" name="insightsgenerator_settings" method="post" 
    	action="{$site_root_path}session/insightsgeneratorconfig.php"  onsubmit="return false">
    {/if}
    <table class="table">
        <tr>
            <th><b>Name</b></th>
            <th><b>Description</b></th>
            {if $user_is_admin}
            	<th>Disabled</th>
            {/if}
        </tr>
    {foreach from=$installed_plugins key=pid item=plugin name=foo}
        <tr>
            <td><b>{$plugin.name}</b></td>
            <td>{$plugin.description}</td>
            {if $user_is_admin}
            	<td>
                	<label class="checkbox">
                        <input type="checkbox" name="hide_{$plugin.filename}" 
                        id="hide_{$plugin.filename}" value="true" >
                      </label>
                </td>
            {/if}
        </tr>
    {/foreach}
    </table>
     <div style="text-align: center" id="save_plugin_setting_image">
                <img  id="save_setting_image" src="{$site_root_path}assets/img/loading.gif" width="50" height="50"  
                style="display: none; margin: 10px;"/>
            </div>
                
            <div class="clearfix">
              <div class="grid_10 prefix_9 left">
                <input type="submit" id="insightsgenerator-settings-save" name="Submit" 
                class="btn btn-primary" value="Save Settings">
              </div>
            </div>
    	</form>
    </div>
    {if $user_is_admin}
    		
	    <script type="text/javascript"> var site_root_path = '{$site_root_path}';</script>
        <script type="text/javascript" 
			src="{$site_root_path}plugins/insightsgenerator/assets/js/insightsgeneratorconfig.js"></script>
    {/if}

<div class="append_20">

{if $options_markup}
    {if $user_is_admin}
        {include file="_plugin.showhider.tpl"}
        {include file="_usermessage.tpl" field="setup"}
        {$options_markup}
    {/if}
{/if}
</div>


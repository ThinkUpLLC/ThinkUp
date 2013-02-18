<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='hellothinkup'}</span>
    <h1>
        <img src="{$site_root_path}plugins/hellothinkup/assets/img/plugin_icon.png" class="plugin-image">
        Hello ThinkUp Plugin
    </h1>
    
    <p>{$message}</p>

</div>

{if $user_is_admin}
{include file="_plugin.showhider.tpl"}

    {include file="_usermessage.tpl" field="setup"}

    {$options_markup}

</div>
{/if}
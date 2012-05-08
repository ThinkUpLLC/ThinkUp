<div class="append_20 alert helpful">
    {insert name="help_link" id='insightsgenerator'}
    <h2>Insights Generator Plugin</h2>
    <div>
    <p>{$message}</p>
    </div>
</div>
    <div>
    <p>The following is a list of currently installed and running insight plugins:</p>
    <table style="border-spacing: 5px;">
    <tr><th><b>Name</b></th><th><b>Description</b></th></tr>
    {foreach from=$installed_plugins key=pid item=plugin name=foo}
      <tr><td>{$plugin.name}</td><td>{$plugin.description}</td></tr>
    {/foreach}
    </table>
    </div>

<div class="append_20">

{if $options_markup}
    {if $user_is_admin}
        {include file="_plugin.showhider.tpl"}
        {include file="_usermessage.tpl" field="setup"}
        {$options_markup}
    {/if}
{/if}
</div>


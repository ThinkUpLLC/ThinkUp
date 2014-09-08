<div class="plugin-info">

    <span class="pull-right">{insert name="help_link" id='insightsgenerator'}</span>
    <h1>
        <i class="fa fa-list text-muted"></i>
        Insights Generator
    </h1>

    <p>{$message}</p>

</div>

    <div>
    <p>The following is a list of currently installed and running insight plugins:</p>
    <table class="table">
        <tr>
            <th><b>Name</b></th>
            <th><b>Description</b></th>
        </tr>
    {foreach from=$installed_plugins key=pid item=plugin name=foo}
        <tr>
            <td><b>{$plugin.name}</b></td>
            <td>{$plugin.description} {if $plugin.when}<span class="label label-default">{$plugin.when}</span>{/if}</td>
        </tr>
    {/foreach}
    </table>
    </div>

<div class="append_20">

{if $options_markup}
    <p>
    {$options_markup}
    </p>
{/if}
</div>


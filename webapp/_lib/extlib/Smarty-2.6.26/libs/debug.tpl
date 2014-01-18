{* Smarty *}
{* debug.tpl, last updated version 2.1.0 *}
{assign_debug_info}

<h4>included templates &amp; config files (load time in seconds)</h4>

<div style="width: 90%;">
{section name=templates loop=$_debug_tpls}
    {section name=indent loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}
    <font color={if $_debug_tpls[templates].type eq "template"}brown{elseif $_debug_tpls[templates].type eq "insert"}black{else}green{/if}>
        {$_debug_tpls[templates].filename|escape:html}</font>
    {if isset($_debug_tpls[templates].exec_time)}
        <span class="exectime">
        ({$_debug_tpls[templates].exec_time|string_format:"%.5f"})
        {if %templates.index% eq 0}(total){/if}
        </span>
    {/if}
    <br />
{sectionelse}
    <p>no templates included</p>
{/section}
</div>

<h2>assigned template variables</h2>

<table id="table_assigned_vars">
    {section name=vars loop=$_debug_keys}
        <tr class="{cycle values="odd,even"}">
            <th>{ldelim}${$_debug_keys[vars]|escape:'html'}{rdelim}</th>
            <td>{$_debug_vals[vars]|@debug_print_var}</td></tr>
    {sectionelse}
        <tr><td><p>no template variables assigned</p></td></tr>
    {/section}
</table>

<h2>assigned config file variables (outer template scope)</h2>

<table id="table_config_vars">
    {section name=config_vars loop=$_debug_config_keys}
        <tr class="{cycle values="odd,even"}">
            <th>{ldelim}#{$_debug_config_keys[config_vars]|escape:'html'}#{rdelim}</th>
            <td>{$_debug_config_vals[config_vars]|@debug_print_var}</td></tr>
    {sectionelse}
        <tr><td><p>no config vars assigned</p></td></tr>
    {/section}
</table>
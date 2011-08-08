<div style="margin:10px">
{foreach from=$profile_items key=tid item=t name=foo}

{if $smarty.foreach.foo.index == 0}

Profiling enabled:<br />
{if $t.time > 0.5}<span style="color:red">{/if}{$t.time}{if $t.time > 0.5}</span>{/if} seconds {$t.action}
<center>
<table style="border-spacing: 5px;">
<tr>
    <th>Time</th>
    <th>Rows</th>
    <th>Action</th>
    <th>Class and method</th>
</tr>
{else}
<tr>
    <td style="vertical-align: top;">{if $t.time > 0.5}<span style="color:red">{/if}{$t.time}s{if $t.time > 0.5}</span>{/if}</td>
    <td style="vertical-align: top;text-align:center;">{if $t.is_query}{$t.num_rows}{/if}</td>
    <td>{$t.action}</td>
    <td style="vertical-align: top;">{$t.dao_method}</td>
</tr>
{/if}
{/foreach}
</table></center>
</div>

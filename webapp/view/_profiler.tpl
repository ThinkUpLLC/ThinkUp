<div style="margin:10px">
{foreach from=$profile_items key=tid item=t name=foo}

{if $smarty.foreach.foo.index == 0}

Profiling enabled:<br />
{$t.time} seconds {$t.action}
<center>
<table style="border-spacing: 5px;">
<tr>
    <th>Num</th>
    <th>Took</th>
    <th>Rows</th>
    <th>Query</th>
</tr>
{else}
<tr>
    <td style="vertical-align: top;text-align:center;">{$smarty.foreach.foo.index}</td>
    <td style="vertical-align: top;">{if $t.time > 0.5}<span style="color:red">{/if}{$t.time}s{if $t.time > 0.5}</span>{/if}</td>
    <td style="vertical-align: top;text-align:center;">{$t.num_rows}</td>
    <td>{$t.action}</td>
</tr>
{/if}
{/foreach}
</table></center>
</div>

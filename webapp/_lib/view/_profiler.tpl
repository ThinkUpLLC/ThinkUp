
<div class="table-responsive col-xs-12">
{foreach from=$profile_items key=tid item=t name=foo}

{if $smarty.foreach.foo.index == 0}

<h2>Profiling enabled:</h2>
<h3 {if $t.time > 0.5}class="text-danger"{/if}>{$t.time} seconds {$t.action}</h3>
<table class="table table-condensed table-hover">
	<thead>
	<tr>
	    <th>Time</th>
	    <th>Rows</th>
	    <th>Action</th>
	    <th>Class and method</th>
	</tr>
	</thead>

	<tbody>
{else}
	<tr>
	    <td style="vertical-align: top;" {if $t.time > 0.5}class="danger"{/if}>{$t.time}s</td>
	    <td style="vertical-align: top;">{if $t.is_query}{$t.num_rows}{/if}</td>
	    <td>{$t.action}</td>
	    <td style="vertical-align: top;">{$t.dao_method}</td>
	</tr>
{/if}
{/foreach}
	</tbody>
</table>
</div>
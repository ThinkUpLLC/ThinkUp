<div class="table-responsive col-xs-6 closed" id="profiler" style="position: fixed; bottom: 0; border: 1px solid #ccc; width: 100%; height: 33%; background-color: #ffc; opacity: 0.8; overflow: scroll;">
<i class="fa fa-plus-square" id="profiler-close"></i>

<h4>Profiler</h4>

{if $i}

<div id="instance_tree"><div>

{literal}
<style>
.profiler-insights-toggle .neg { display: none; }
.profiler-insights-toggle.closed .neg { display: block; }
.profiler-insights-toggle.closed .pos { display: none; }
#profiler-close {
	position: absolute;
	top: 10px;
	right: 10px;
}

#profiler.closed { height: 50px !important;}
#profiler.closed * { display: none; }
#profiler.closed h4, #profiler.closed #profiler-close { display: block;}
</style>
{/literal}

<div class="profiler-insights">
	<div class="profiler-insights-toggle">
		<a href="#" class="pos">Show insights data</a>
		<a href="#" class="neg">Hide insights data</a>
	</div> 
	<pre class="profiler-insights-data" style="display: none;background-color: transparent; border: none;">
	{$insights|@print_r}
	</pre>
</div>

{literal}
<script type="text/javascript">

	// var insightsArray = {$insights|@json_encode};
	$("body").on("click", ".profiler-insights-toggle a", function(e){
		e.preventDefault();
		$(this).parent().toggleClass("closed");
		$(".profiler-insights-data").toggle();
	});

	$("body").on("click", "#profiler-close, #profiler h4", function(e){
		e.preventDefault();
		$("#profiler").toggleClass("closed");
		$("#profiler-close").toggleClass("fa-minus-square").toggleClass("fa-plus-square");
	});

</script>
{/literal}

{/if}

{foreach from=$profile_items key=tid item=t name=foo}

{if $smarty.foreach.foo.index == 0}

<h5 {if $t.time > 0.5}class="text-danger"{/if}>{$t.time} seconds {$t.action}</h5>
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
<div class="table-responsive col-xs-6 closed" id="profiler" style="position: fixed; bottom: 0; border: 1px solid #ccc; width: 100%; height: 50%; background-color: #ffc;  overflow: scroll;">
<i class="fa fa-plus-square" id="profiler-close"></i>

<h4 id="profiler-title">Profiler</h4>

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

#profiler.closed { height: 50px !important; opacity: 0.6;}
#profiler.closed * { display: none;}
#profiler.closed h4, #profiler.closed #profiler-close { display: block;}
</style>
{/literal}

{literal}
<script type="text/javascript">

	// var insightsArray = {$insights|@json_encode};

	$("body").on("click", "#profiler-close, #profiler h4", function(e){
		e.preventDefault();
		$("#profiler").toggleClass("closed");
		$("#profiler-close").toggleClass("fa-minus-square").toggleClass("fa-plus-square");
	});
	$('#profiler-tabs a:first').tab('show') // Select first tab

</script>
{/literal}

<ul class="nav nav-tabs" id="profiler-tabs">
  <li class="active"><a href="#log" data-toggle="tab">Query Log</a></li>
  {if $i}<li><a href="#insights" data-toggle="tab">Insights</a></li>{/if}
  <li><a href="#smarty" data-toggle="tab">Smarty</a></li>
</ul>

	<div class="tab-content" style="background-color: white;
		border-left: 1px solid #ddd; border-right: 1px solid #ddd; padding: 6px;">

		<div class="tab-pane active" id="log">

			{foreach from=$profile_items key=tid item=t name=foo}

			{if $smarty.foreach.foo.index == 0}

			<table class="table table-condensed table-hover">
				<thead>
				<tr>
				    <th colspan="2" {if $t.time > 0.5}class="danger"{/if}>{$t.time} seconds {$t.action}</th>
				    <th>Rows</th>
				    <th>Class and method</th>
				</tr>
				</thead>

				<tbody>
			{else}
				<tr {if $t.time > 0.5}class="danger"{/if}>
				    <td style="vertical-align: top;">{$t.time}s</td>
				    <td>{$t.action}</td>
				    <td style="vertical-align: top;">{if $t.is_query}{$t.num_rows}{/if}</td>
				    <td style="vertical-align: top;">{$t.dao_method}</td>
				</tr>
			{/if}
			{/foreach}
				</tbody>
			</table>
		</div>

{if $i}

		<div class="tab-pane profiler-insights" id="insights">
			<pre class="profiler-insights-data" style="background-color: transparent; border: none;">
{$insights|@print_r}
			</pre>
		</div>

{/if}

		<div class="tab-pane" id="smarty">
			{debug}
		</div>

	</div>


</div>
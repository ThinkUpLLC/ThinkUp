<div class="table-responsive col-xs-6 closed" id="profiler" style="position: fixed; top: 50%; border: 1px solid #ccc; width: 100%; height: 50%; background-color: #ffc;  overflow: scroll;">


<h4 id="profiler-title">Profiler </h4>
<i class="fa fa-plus-square" id="profiler-close"></i>

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

#profiler.closed { height: 40px !important; width: 200px !important; opacity: 0.6;}
#profiler.closed * { display: none;}
#profiler.closed h4, #profiler.closed #profiler-close { display: block;}
#page-content.profiled { padding-bottom: 40% !important;}
</style>
{/literal}

{literal}
<script type="text/javascript">

	// var insightsArray = {$insights|@json_encode};

	$("body").on("click", "#profiler-close, #profiler h4", function(e){
		e.preventDefault();
		$("#profiler").toggleClass("closed");
		$("#page-content").toggleClass("profiled");
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
			{assign_debug_info}


			{section name=templates loop=$_debug_tpls}
			<h4>included templates &amp; config files (load time in seconds)</h4>
			<div style="width: 90%;">
			    {section name=indent loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}
			    <font color={if $_debug_tpls[templates].type eq "template"}brown{
			    	elseif $_debug_tpls[templates].type eq "insert"}black{else}green{/if}>
			        {$_debug_tpls[templates].filename|escape:html}</font>
			    {if isset($_debug_tpls[templates].exec_time)}
			        <span class="exectime">
			        ({$_debug_tpls[templates].exec_time|string_format:"%.5f"})
			        {if %templates.index% eq 0}(total){/if}
			        </span>
			    {/if}
			    <br />
			{sectionelse}
			    <h5>no templates included</h5>
			</div>
			{/section}

			<h4>assigned template variables</h4>

			<table id="table_assigned_vars" class="table table-condensed table-hover">
			    {section name=vars loop=$_debug_keys}
			        <tr>
			            <th><code>{ldelim}${$_debug_keys[vars]|escape:'html'}{rdelim}</code></th>
			            <td>{$_debug_vals[vars]|@debug_print_var}</td></tr>
			    {sectionelse}
			        <tr><td><p>no template variables assigned</p></td></tr>
			    {/section}
			</table>

			<h4>assigned config file variables (outer template scope)</h4>

			<table id="table_config_vars" class="table table-condensed table-hover">
			    {section name=config_vars loop=$_debug_config_keys}
			        <tr>
			            <th>{ldelim}#{$_debug_config_keys[config_vars]|escape:'html'}#{rdelim}</th>
			            <td>{$_debug_config_vals[config_vars]|@debug_print_var}</td></tr>
			    {sectionelse}
			        <tr><td><p>no config vars assigned</p></td></tr>
			    {/section}
			</table>
		</div>

	</div>


</div>
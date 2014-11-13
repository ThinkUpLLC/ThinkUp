    <div id="chart_{$i->id}" class="chart" align="center"></div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );
        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            {literal}
            var raw = {/literal}{$i->related_data.line_chart|@json_encode}{literal};
            var table = new google.visualization.DataTable(raw);
            var maxvalue = 0;
            for (var i=0; i<raw.rows.length; i++) {
                maxvalue = Math.max(maxvalue, raw.rows[i].c[1].v);
            }
            var num_ticks = Math.min(10, maxvalue+1);
            var c = window.tu.constants.colors;
            var chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart_{/literal}{$i->id}{literal}',
              chartType: 'LineChart',
              dataTable: table,
              options: {
                  width: 400,
                  height: 290,
                  pointSize: 4,
                  colors: {/literal}[c.{$color}, c.{$color}_dark, c.{$color}_darker],{literal}
                  chartArea:{width:"80%",height:"80%"},
                  legend: 'none',
                  interpolateNulls: true,
                  hAxis: {
                    baselineColor: '#ccc',
                    textStyle: { color: '#999', fontSize: 10 },
                    gridlines: {
                        color: '#eee'
                    },
                  },
                  vAxis: {
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: {
                        color: '#eee'
                    },
                    format: '0',
                    minValue: 0,
                  }
                }
            });
        {/literal}
            {include file=$tpl_path|cat:"_chartcallback.tpl"}
        {literal}
            chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>


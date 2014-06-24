    <div id="exclamation_count_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );
        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            {literal}
            var raw = {/literal}{$i->related_data.bar_chart|@json_encode}{literal};
            var table = new google.visualization.DataTable(raw);
            var maxvalue = 0;
            for (var i=0; i<raw.rows.length; i++) {
                maxvalue = Math.max(maxvalue, raw.rows[i].c[1].v);
            }
            var view_duration_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'exclamation_count_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: table,
              options: {
                  width: 300,
                  height: 250,
                  chartArea:{left:100,height:"80%"},
                  hAxis: {
                    textStyle: { color: '#999', fontSize: 10 },
                    format: '0',
                    minValue: 0,
                    gridlines: {count: maxvalue+1}
                  },
                  vAxis: {
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' }
                  }
                }
            });
            view_duration_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

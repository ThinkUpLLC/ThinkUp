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
            var num_ticks = Math.min(10, maxvalue+1);
            var c = window.tu.constants.colors;
            var view_duration_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'exclamation_count_{/literal}{$i->id}{literal}',
              chartType: 'ColumnChart',
              dataTable: table,
              options: {
                  width: 380,
                  height: 250,
                  colors: {/literal}[c.{$color}, c.{$color}_dark, c.{$color}_darker],{literal}
                  chartArea:{left:40,height:"80%"},
                  legend: 'none',
                  hAxis: {
                    textStyle: { color: '#999', fontSize: 10 }
                  },
                  vAxis: {
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' },
                    format: '0',
                    minValue: 0,
                    gridlines: {count: num_ticks}
                  }
                }
            });
            view_duration_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

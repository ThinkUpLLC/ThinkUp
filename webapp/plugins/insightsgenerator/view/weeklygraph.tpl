
    <div id="response_rates_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var response_rates_data_{$i->id} = new google.visualization.DataTable({$i->related_data.posts[0]});

            {literal}
            var response_rates_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'response_rates_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: response_rates_data_{/literal}{$i->id}{literal},
              options: {
                  colors: ['#3e5d9a', '#3c8ecc', '#BBCCDD'],
                  isStacked: true,
                  width: 300,
                  height: 250,
                  chartArea:{left:100,height:"80%"},
                  legend: 'bottom',
                  hAxis: {
                    textStyle: { color: '#fff', fontSize: 1 }
                  },
                  vAxis: {
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' }
                  },
                }
            });
            response_rates_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

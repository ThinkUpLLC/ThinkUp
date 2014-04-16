{if isset($i->related_data.posts[0])}
    <div id="response_rates_{$i->id}" class="weekly_chart">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var response_rates_data_{$i->id} = new google.visualization.DataTable({$i->related_data.posts[0]});

            var c = window.tu.constants.colors;
            var colors = [c.{$color}, c.{$color}_dark, c.{$color}_darker];

            {literal}
            var response_rates_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'response_rates_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: response_rates_data_{/literal}{$i->id}{literal},
              options: {
                  backgroundColor: 'transparent',
                  colors: colors,
                  isStacked: true,
                  height: 250,
                  focusTarget: 'category',
                  annotations: {
                    textStyle: {
                      fontName: 'tablet-gothic-wide, Helvetica Neue, Helvetica, Arial, Lucida Grande, sans-serif'
                    },
                  },
                  chartArea:{
                      height:"100%",
                      left: 200
                  },
                  legend: {
                    position: 'bottom',
                    alignment: 'start'
                  },
                  tooltip: {
                    showColorCode: true
                  },
                  hAxis: {
                    direction: 1,
                    titleTextStyle: {
                      left: -100
                    }
                  },
                  vAxis: {
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: {
                        color: '#000',
                        fontName: 'Libre Baskerville, georgia, serif'
                    },
                    textPosition: 'out',
                    gridlines: { color: '#eee' }
                  },
                }
            });
            response_rates_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>
{/if}

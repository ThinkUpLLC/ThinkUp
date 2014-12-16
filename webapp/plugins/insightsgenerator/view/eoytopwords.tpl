    <div id="chart_{$i->id}">&nbsp;</div>
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
            var chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart_{/literal}{$i->id}{literal}',
              chartType: 'BarChart',
              dataTable: table,
              options: {
                  backgroundColor: 'transparent',
                  colors: {/literal}[c.{$color}, c.{$color}_dark, c.{$color}_darker],{literal}
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

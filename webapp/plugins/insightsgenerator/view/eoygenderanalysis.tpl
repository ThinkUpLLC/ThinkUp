    <div style="width: 100%;"><div style="margin: auto;width: 300px" id="chart_{$i->id}">&nbsp;</div></div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );

        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            var gender_analysis_data_{$i->id} = new google.visualization.arrayToDataTable([
          ['Gender', 'Number per post'],
          ['Male',  {$i->related_data.pie_chart.male}],
          ['Female',  {$i->related_data.pie_chart.female}]
         ]);
            {literal}
            var c = window.tu.constants.colors;
            var chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart_{/literal}{$i->id}{literal}',
              chartType: 'PieChart',
              dataTable: gender_analysis_data_{/literal}{$i->id}{literal},
              'options': {
              colors: [{/literal}c.{$color}, c.{$color}_dark{literal}],
            	'width': 300,
            	'height': 250,
            	'legend': 'right'
          	}

            });
        {/literal}
            {include file=$tpl_path|cat:"_chartcallback.tpl"}
        {literal}
            chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>


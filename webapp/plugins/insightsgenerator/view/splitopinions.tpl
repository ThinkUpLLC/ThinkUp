
    <div id="like_dislikes_{$i->id}">&nbsp;</div>
    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        google.load('visualization', '1');
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart{$i->id} );
        {literal}
        function drawChart{/literal}{$i->id}{literal}() {
        {/literal}
            {literal}
            var likes_dislikes_data_{/literal}{$i->id}{literal} = google.visualization.arrayToDataTable([
                ['Metric', 'Quantity'],
                ['Likes', {/literal}{$i->related_data->likes}{literal}],
                ['Dislikes',{/literal}{$i->related_data->dislikes}{literal}]
            ]);

            var likes_dislikes_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
              containerId: 'chart-{/literal}{$i->id}{literal}',
              chartType: 'PieChart',
              dataTable: likes_dislikes_data_{/literal}{$i->id}{literal},
              options: {
                  width: 300,
                  height: 250,
                  chartArea:{left:100,height:"80%"},
                  legend: 'bottom',
                  colors: ['#7DD3F0', '#E6B8D4']
                }
            });
            likes_dislikes_chart_{/literal}{$i->id}{literal}.draw();
        }
        {/literal}
    </script>

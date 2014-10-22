{include file=$tpl_path|cat:"_post.tpl" post=$i->related_data.posts.0 hide_insight_header=true}

<div id="age_analysis_{$i->id}">&nbsp;</div>
<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart{$i->id} );

    {literal}
    function drawChart{/literal}{$i->id}{literal}() {
    {/literal}
        var data = new google.visualization.DataTable({literal}{
            cols: [{label: 'Age Range', type: 'string'}, {label:'People', type: 'number'}],
            rows: [{/literal}
            {literal}{c: [{v:'<18'}, {v: {/literal}{$i->related_data.age_data.18}}]},
            {literal}{c: [{v:'18-25'}, {v: {/literal}{$i->related_data.age_data.18_25}}]},
            {literal}{c: [{v:'25-35'}, {v: {/literal}{$i->related_data.age_data.25_35}}]},
            {literal}{c: [{v:'25-45'}, {v: {/literal}{$i->related_data.age_data.35_45}}]},
            {literal}{c: [{v:'45+'}, {v: {/literal}{$i->related_data.age_data.45}}]}
            ]
     	});
        var maxvalue = Math.max({$i->related_data.age_data.18}, {$i->related_data.age_data.18-25},
                {$i->related_data.age_data.25_35}, {$i->related_data.age_data.35_45}, {$i->related_data.age_data.45});
        var num_ticks = Math.min(10, maxvalue+1);
        {literal}
        var c = window.tu.constants.colors;
        var age_analysis_chart_{/literal}{$i->id}{literal} = new google.visualization.ChartWrapper({
            containerId: 'age_analysis_{/literal}{$i->id}{literal}',
            chartType: 'ColumnChart',
            dataTable: data,
            options: {
                width: 380,
                height: 250,
                colors: {/literal}[c.{$color}, c.{$color}_dark, c.{$color}_darker],{literal}
                chartArea:{left:40,height:"80%"},
                legend: 'none',
      		    hAxis: { textStyle: { color: '#999', fontSize: 10 } },
      		    vAxis: {
                    format: '0',
                    minValue: 0,
                    baselineColor: '#ccc',
                    textStyle: { color: '#999' },
                    gridlines: { color: '#eee' },
                    gridlines: {count: num_ticks}
                },
           }
        });
        {/literal}{include file=$tpl_path|cat:"_chartcallback.tpl"}{literal}
        age_analysis_chart_{/literal}{$i->id}{literal}.draw();
    }
    {/literal}
</script>

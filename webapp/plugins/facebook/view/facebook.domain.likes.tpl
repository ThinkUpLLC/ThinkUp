<h1>Domain Stats for {$instance->network_username}</h1>
 <div class="">
  <div class="help-container">{insert name="help_link" id=$display}</div>
  {if $description}<i>{$description}</i>{/if}
</div>
    {if $error}
    <p class="error">
        {$error}
    </p>
    {/if}


<div id="domain_widget_likes_by_day"></div>
<div id="domain_widget_likes_by_week"></div>
<div id="domain_widget_likes_by_month"></div>

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawCharts);

{literal}
function drawCharts() {
{/literal}
    var domain_widget_likes_by_day_data = new google.visualization.DataTable(
    {$domain_widget_likes_by_day});
    var domain_widget_likes_by_week_data = new google.visualization.DataTable(
    {$domain_widget_likes_by_week});
    var domain_widget_likes_by_month_data = new google.visualization.DataTable(
    {$domain_widget_likes_by_month});

{literal}
    var domain_widget_likes_by_day_chart = new google.visualization.ChartWrapper({
        containerId: 'domain_widget_likes_by_day',
        chartType: 'LineChart',
        dataTable: domain_widget_likes_by_day_data,
        options: {
            title: 'Domain Widget Likes By Day',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0,
              logScale: true,
              format: '#,###',
            }
        },
    });
    domain_widget_likes_by_day_chart.draw();

    var domain_widget_likes_by_week_chart = new google.visualization.ChartWrapper({
        containerId: 'domain_widget_likes_by_week',
        chartType: 'LineChart',
        dataTable: domain_widget_likes_by_week_data,
        options: {
            title: 'Domain Widget Likes By Week',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0,
              logScale: true,
              format: '#,###',
            }
        },
    });
    domain_widget_likes_by_week_chart.draw();

    var domain_widget_likes_by_month_chart = new google.visualization.ChartWrapper({
        containerId: 'domain_widget_likes_by_month',
        chartType: 'LineChart',
        dataTable: domain_widget_likes_by_month_data,
        options: {
            title: 'Domain Widget Likes By Month',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0,
              logScale: true,
              format: '#,###',
            }
        },
    });
    domain_widget_likes_by_month_chart.draw();
}

{/literal}
</script>

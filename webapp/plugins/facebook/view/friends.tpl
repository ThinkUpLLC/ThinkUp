<div class="section">
    <h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Day {if $follower_count_history_by_day.trend}{if $follower_count_history_by_day.trend > 0}(<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}</h2>

    {if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
    <div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">
        <div id="follower_count_history_by_day"></div>
    </div>

    {if $follower_count_history_by_day.milestone and $follower_count_history_by_day.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.will_take} day{if $follower_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_day.milestone.next_milestone|number_format} followers</span> at this rate.</small>
    </div>
    {/if}
    {/if}
</div>

<div class="section">
    <h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Week {if $follower_count_history_by_week.trend != 0}{if $follower_count_history_by_week.trend > 0}(<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}</h2>

    {if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
    {else} 

    <div class="article">
        <div id="follower_count_history_by_week"></div>
    </div>

    {if $follower_count_history_by_week.milestone and $follower_count_history_by_week.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.</small> 
    </div>
    {/if}
    {/if}
</div>

<div class="section">
    <h2>{if $instance->network eq 'facebook page'}Likes{else}Friends{/if} By Month {if $follower_count_history_by_month.trend != 0}{if $follower_count_history_by_month.trend > 0}(<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_month.trend|number_format}</span>/month){/if}</h2>

    {if !$follower_count_history_by_month.history OR $follower_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</i></div>
    {else} 

    <div class="article">        
        <div id="follower_count_history_by_month"></div>
    </div>

    {if $follower_count_history_by_month.milestone and $follower_count_history_by_month.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.will_take} month{if $follower_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.next_milestone|number_format} followers</span> at this rate.</small>
    </div>
    {/if}
    {/if}
</div>

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawCharts);

{literal}
function drawCharts() {
{/literal}
    var follower_count_history_by_day_data = new google.visualization.DataTable(
    {$follower_count_history_by_day.vis_data});
    var follower_count_history_by_week_data = new google.visualization.DataTable(
    {$follower_count_history_by_week.vis_data});
    var follower_count_history_by_month_data = new google.visualization.DataTable(
    {$follower_count_history_by_month.vis_data});

{literal}
    var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
    var formatter_date = new google.visualization.DateFormat({formatType: 'medium'});

    var chart_options = {
            colors: ['#3c8ecc'],
            width: '100%',
            height: 250,
            legend: "none",
            interpolateNulls: true,
            pointSize: 2,
            hAxis: {
                baselineColor: '#eee',
                format: 'MMM d',
                textStyle: { color: '#999' },
                gridlines: { color: '#eee' }
            },
            vAxis: {
                baselineColor: '#eee',
                textStyle: { color: '#999' },
                gridlines: { color: '#eee' }
            },    
    };
    
    formatter.format(follower_count_history_by_day_data, 1);
    formatter_date.format(follower_count_history_by_day_data, 0);
    var follower_count_history_by_day_chart = new google.visualization.ChartWrapper({
        containerId: 'follower_count_history_by_day',
        chartType: 'LineChart',
        dataTable: follower_count_history_by_day_data,
        options: chart_options
    });
    follower_count_history_by_day_chart.draw();

    formatter.format(follower_count_history_by_week_data, 1);
    formatter_date.format(follower_count_history_by_week_data, 0);
    var follower_count_history_by_week_chart = new google.visualization.ChartWrapper({
        containerId: 'follower_count_history_by_week',
        chartType: 'LineChart',
        dataTable: follower_count_history_by_week_data,
        options:  chart_options    });
    follower_count_history_by_week_chart.draw();

    formatter.format(follower_count_history_by_month_data, 1);
    formatter_date.format(follower_count_history_by_month_data, 0);
    var follower_count_history_by_month_chart = new google.visualization.ChartWrapper({
        containerId: 'follower_count_history_by_month',
        chartType: 'LineChart',
        dataTable: follower_count_history_by_month_data,
        options: {
            colors: ['#3c8ecc'],
            width: '100%',
            height: 250,
            legend: "none",
            interpolateNulls: true,
            pointSize: 2,
            hAxis: {
                baselineColor: '#eee',
                format: 'MMM yyyy',
                textStyle: { color: '#999' },
                gridlines: { color: '#eee' }
            },
            vAxis: {
                baselineColor: '#eee',
                textStyle: { color: '#999' },
                gridlines: { color: '#eee' }
            },
        },
    });
    follower_count_history_by_month_chart.draw();
}
{/literal}
</script>
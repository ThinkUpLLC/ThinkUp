
 <div class="">
  <div class="help-container">{insert name="help_link" id=$display}</div>
  {if $description}<i>{$description}</i>{/if}
</div>
    {if $error}
    <p class="error">
        {$error}
    </p>
    {/if}

<h2>{if $list_membership_count_history_by_day.trend}({if $list_membership_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
<div id="list_membership_count_history_by_day"></div>

{if $list_membership_count_history_by_day.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.will_take} day{if $list_membership_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.next_milestone|number_format} groups</span> at this rate.</small>
{/if}
<br /><br />

<h2>{if $list_membership_count_history_by_week.trend}({if $list_membership_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
<div id="list_membership_count_history_by_week"></div>

{if $list_membership_count_history_by_week.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.will_take} week{if $list_membership_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.next_milestone|number_format} groups</span> at this rate.</small>
{/if}

<br /><br />

<h2>{if $list_membership_count_history_by_month.trend}({if $list_membership_count_history_by_month.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_month.trend|number_format}</span>/month){/if}</h2>
<div id="list_membership_count_history_by_month"></div>

{if $list_membership_count_history_by_month.milestone}
<br /><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.will_take} month{if $list_membership_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.next_milestone|number_format} groups</span> at this rate.</small>
{/if}

<script type="text/javascript">
// Load the Visualization API and the standard charts
google.load('visualization', '1');
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawCharts);

{literal}
function drawCharts() {
{/literal}
    var list_membership_count_history_by_day_data = new google.visualization.DataTable(
    {$list_membership_count_history_by_day.vis_data});
    var list_membership_count_history_by_week_data = new google.visualization.DataTable(
    {$list_membership_count_history_by_week.vis_data});
    var list_membership_count_history_by_month_data = new google.visualization.DataTable(
    {$list_membership_count_history_by_month.vis_data});

{literal}
    var list_membership_count_history_by_day_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_day',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_day_data,
        options: {
            title: 'List Membership Count By Day',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0
            }
        },
    });
    list_membership_count_history_by_day_chart.draw();

    var list_membership_count_history_by_week_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_week',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_week_data,
        options: {
            title: 'List Membership Count By Week',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0
            }
        },
    });
    list_membership_count_history_by_week_chart.draw();

    var list_membership_count_history_by_month_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_month',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_month_data,
        options: {
            title: 'List Membership Count By Month',
            width: 600,
            height: 250,
            legend: "top",
            interpolateNulls: true,
            vAxis: {
              minValue: 0
            }
        },
    });
    list_membership_count_history_by_month_chart.draw();
}

{/literal}
</script>

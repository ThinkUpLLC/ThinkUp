{if $leastlikelythisweek|@count >1}
<div class="section">
    <h2>This Week's Most Discerning Followers</h2>
    <div class="article" style="padding-left : 0px; padding-top : 0px;">
    {foreach from=$leastlikelythisweek key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}"  title="{$u.user_name} has {$u.follower_count|number_format} followers and {$u.friend_count|number_format} friends"><img src="{$u.avatar}" class="avatar2"/><i class="service-icon2 fa fa-{$u.network}"></i></a>
      </div>
    {/foreach}
        <br /><br /><br />
    </div>
    <div class="view-all"><a href="?v=followers-leastlikely&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $leastlikely|@count >1}
<div class="section">
    <h2>All-Time Most Discerning Followers</h2>
    <div class="article" style="padding-left : 0px; padding-top : 0px;">
    {foreach from=$leastlikely key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name} has {$u.follower_count|number_format} followers and {$u.friend_count|number_format} friends"><img src="{$u.avatar}" class="avatar2"/><i class="service-icon2 fa fa-{$u.network}"></i></a>
      </div>
    {/foreach}
    <br /><br /><br />
    </div>
    <div class="view-all"><a href="?v=followers-leastlikely&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $popular|@count >1}
<div class="section">
    <h2>Most Popular Followers</h2>
    <div class="article" style="padding-left : 0px; padding-top : 0px;">
    {foreach from=$popular key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name} has {$u.follower_count|number_format} followers and {$u.friend_count|number_format} friends"><img src="{$u.avatar}" class="avatar2"/<i class="service-icon2 fa fa-{$u.network}"></i></a>
      </div>
    {/foreach}
    <br /><br /><br />
</div>
<div class="view-all"><a href="?v=followers-mostfollowed&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}


<div class="section">
    <h2>Follower Count By Day {if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
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
    <h2>Follower Count By Week {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
    {if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">
        <div id="follower_count_history_by_week"></div>
    </div>
    {if $follower_count_history_by_week.milestone and $follower_count_history_by_week.milestone.will_take > 0}
    <div class="stream-pagination">
    <small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.</small> 
    </div>
    {/if}
    {/if}
</div>

<div class="section">
    <h2>Follower Count By Month {if $follower_count_history_by_month.trend != 0}({if $follower_count_history_by_month.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_month.trend|number_format}</span>/month){/if}</h2>
    {if !$follower_count_history_by_month.history OR $follower_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">
        <div id="follower_count_history_by_month"></div>
    </div>

    {if $follower_count_history_by_month.milestone and $follower_count_history_by_month.milestone.will_take > 0}
    <div class="stream-pagination">
    <small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.will_take} month{if $follower_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_month.milestone.next_milestone|number_format} followers</span> at this rate.</small>
    </div>
    {/if}
    {/if}
</div>

<div class="section">
    <h2>List Membership Count By Day {if $list_membership_count_history_by_day.trend}({if $list_membership_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
    {if !$list_membership_count_history_by_day.history OR $list_membership_count_history_by_day.history|@count < 2}
    <div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">

    <div id="list_membership_count_history_by_day"></div>

    {if $list_membership_count_history_by_day.milestone and $list_membership_count_history_by_day.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.will_take} day{if $list_membership_count_history_by_day.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_day.milestone.next_milestone|number_format} groups</span> at this rate.</small></div>
    {/if}
    </div>
    {/if}
</div>

<div class="section">
    <h2>List Membership Count By Week {if $list_membership_count_history_by_week.trend != 0}({if $list_membership_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
    {if !$list_membership_count_history_by_week.history OR $list_membership_count_history_by_week.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">

    <div id="list_membership_count_history_by_week"></div>

    {if $list_membership_count_history_by_week.milestone and $list_membership_count_history_by_week.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.will_take} week{if $list_membership_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_week.milestone.next_milestone|number_format} groups</span> at this rate.</small></div>
    {/if}
    </div>
    {/if}
</div>

<div class="section">
    <h2>List Membership Count By Month {if $list_membership_count_history_by_month.trend != 0}({if $list_membership_count_history_by_month.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$list_membership_count_history_by_month.trend|number_format}</span>/month){/if}</h2>
    {if !$list_membership_count_history_by_month.history OR $list_membership_count_history_by_month.history|@count < 2}<div class="alert urgent">Not enough data to display chart</div>
    {else}
    <div class="article">

    <div id="list_membership_count_history_by_month"></div>
    
    {if $list_membership_count_history_by_month.milestone and $list_membership_count_history_by_month.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">NEXT MILESTONE: <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.will_take} month{if $list_membership_count_history_by_month.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$list_membership_count_history_by_month.milestone.next_milestone|number_format} groups</span> at this rate.</small></div>
    {/if}
    </div>
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
    var list_membership_count_history_by_day_data = new google.visualization.DataTable(
        {$list_membership_count_history_by_day.vis_data});
    var list_membership_count_history_by_week_data = new google.visualization.DataTable(
        {$list_membership_count_history_by_week.vis_data});
    var list_membership_count_history_by_month_data = new google.visualization.DataTable(
        {$list_membership_count_history_by_month.vis_data});
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
        options: chart_options
    });
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

    formatter.format(list_membership_count_history_by_day_data, 1);
    formatter_date.format(list_membership_count_history_by_day_data, 0);
    var list_membership_count_history_by_day_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_day',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_day_data,
        options: chart_options
    });
    list_membership_count_history_by_day_chart.draw();

    formatter.format(list_membership_count_history_by_week_data, 1);
    formatter_date.format(list_membership_count_history_by_week_data, 0);
    var list_membership_count_history_by_week_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_week',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_week_data,
        options: chart_options
    });
    list_membership_count_history_by_week_chart.draw();
    
    formatter.format(list_membership_count_history_by_month_data, 1);
    formatter_date.format(list_membership_count_history_by_month_data, 0);
    var list_membership_count_history_by_month_chart = new google.visualization.ChartWrapper({
        containerId: 'list_membership_count_history_by_month',
        chartType: 'LineChart',
        dataTable: list_membership_count_history_by_month_data,
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
    list_membership_count_history_by_month_chart.draw();

}

{/literal}
</script>
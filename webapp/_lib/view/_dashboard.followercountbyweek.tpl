  <h2>
    {if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if} By Week
    {if $follower_count_history_by_week.trend != 0}
        ({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week)
    {/if}
  </h2>
  {if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}
      <div class="alert helpful">Not enough data to display chart</div>
  {else}
    <div class="article">
        <div id="follower_count_history_by_week"></div>
    </div>
    {if $follower_count_history_by_week.milestone and $follower_count_history_by_week.milestone.will_take > 0}
    <div class="stream-pagination"><small style="color:gray">
        <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.
    </small></div>
    {/if}
  <div class="view-all">
    <a href="{$site_root_path}?v={if $instance->network neq 'twitter'}friends{else}followers{/if}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a>
  </div>
  {/if}
  
<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawFollowerCountByWeekChart);

    {literal}
    function drawFollowerCountByWeekChart() {
    {/literal}
        var follower_count_history_by_week_data = new google.visualization.DataTable({$follower_count_history_by_week.vis_data});
        {literal}
        var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
        var formatter_date = new google.visualization.DateFormat({formatType: 'medium'});

          formatter.format(follower_count_history_by_week_data, 1);
          formatter_date.format(follower_count_history_by_week_data, 0);

          var follower_count_history_by_week_chart = new google.visualization.ChartWrapper({
              containerId: 'follower_count_history_by_week',
              chartType: 'LineChart',
              dataTable: follower_count_history_by_week_data,
              options: {
                  width: 325,
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
              },
          });
          follower_count_history_by_week_chart.draw();
    }
    {/literal}
</script>
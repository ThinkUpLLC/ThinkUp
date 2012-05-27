  <h2>
    {if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if}By Day
    {if $follower_count_history_by_day.trend}
        ({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day)
    {/if}
  </h2>
  {if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
    <div class="alert helpful">Not enough data to display chart</div>
  {else}
      <div class="article">
        <div id="follower_count_history_by_day"></div>
    </div>
    <div class="view-all">
    <a href="{$site_root_path}?v={if $instance->network neq 'twitter'}friends{else}followers{/if}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a>
  </div>
  {/if}

<script type="text/javascript">
    // Load the Visualization API and the standard charts
    google.load('visualization', '1');
    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawFollowerCountByDayChart);

    {literal}
    function drawFollowerCountByDayChart() {
    {/literal}
        var follower_count_history_by_day_data = new google.visualization.DataTable({$follower_count_history_by_day.vis_data});
        {literal}
        var formatter = new google.visualization.NumberFormat({fractionDigits: 0});
        var formatter_date = new google.visualization.DateFormat({formatType: 'medium'});

          formatter.format(follower_count_history_by_day_data, 1);
          formatter_date.format(follower_count_history_by_day_data, 0);
        
          var follower_count_history_by_day_chart = new google.visualization.ChartWrapper({
              containerId: 'follower_count_history_by_day',
              chartType: 'LineChart',
              dataTable: follower_count_history_by_day_data,
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
          follower_count_history_by_day_chart.draw();
    }
    {/literal}
</script>
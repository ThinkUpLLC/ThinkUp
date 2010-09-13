  <div class="container small center">
  
    <div id="ft" role="contentinfo">
      <p>
        {if $stats neq 'no'}
          Status:
          <cite title="{$instance->total_posts_in_system|number_format} of {$owner_stats->post_count|number_format}"><strong>{$percent_tweets_loaded|number_format}%</strong></cite> of your posts loaded: <a href="{$site_root_path}post/export.php?u={$instance->network_username}&n={$instance->network}">Export</a> | 
        {/if}
       <a href="http://thinkupapp.com">ThinkUp</a>{if $thinkup_version} v{$thinkup_version}{/if}<br>
        It is nice to be nice.
      </p>
    </div> <!-- #ft -->
  
  </div> <!-- .content -->

<div id="screen"></div>
</body>

</html>
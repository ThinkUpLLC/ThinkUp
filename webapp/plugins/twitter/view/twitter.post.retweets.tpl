       {if $post}
          <div class="clearfix">
            <div class="grid_2 alpha">
              <div class="avatar-container">
                <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
             </div>
            </div>
            <div class="grid_12">
              <div class="br" style="min-height:110px">
                <span class="tweet pr">
                  {if $post->post_text}
                    {$post->post_text|filter_xss|link_usernames_to_twitter}
                  {else}
                    <span class="no-post-text">No post text</span>
                  {/if}
                </span>
                {if $post->link->expanded_url and !$post->link->image_src and $post->link->expanded_url != $post->link->url}
                  <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">
                    {$post->link->expanded_url}
                  </a>
                {/if}
 
                {literal}
                <script>
                $(function() {

                  $('#button').click(function() {
                    $('#more-detail').toggle('slow', function() {
                      // Animation complete.
                      if ($('#button').hasClass('ui-icon-circle-arrow-s')) {
                          $('#button').removeClass('ui-icon-circle-arrow-s').addClass('ui-icon-circle-arrow-n');
                      } else if ($('#button').hasClass('ui-icon-circle-arrow-n')) {
                          $('#button').removeClass('ui-icon-circle-arrow-n').addClass('ui-icon-circle-arrow-s');
                      } else {
                          $('#button').addClass('ui-icon-circle-arrow-s');
                      }
                    });
                  });

                  });
                </script>
                {/literal}

                <!-- more-detail element -->
                <div class="clearfix append">
                  <span id="button" class="float-l ui-icon ui-icon-circle-arrow-s"></span>
                </div>
                <div class="clearfix gray" id="more-detail" style="display:none;width:460px;">
                  <div class="grid_2 alpha">
                    <img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png">
                     </div>
                  <div class="grid_5">
                      {$post->adj_pub_date|date_format:"%D"} @ {$post->adj_pub_date|date_format:"%I:%M %p"}<br>
                        {if $post->location}{$post->location}{/if}
                        <!--
                         {if $post->in_reply_to_post_id}<a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}">In reply to</a>{/if}
                      {if $post->in_retweet_of_post_id}<a href="{$site_root_path}post/?t={$post->in_retweet_of_post_id}">In retweet of</a><br>{/if}
                      -->
                    </div>
                  <div class="grid_4 omega">
                        {if $post->source}
                        
                              {if $post->source eq 'web'}
                                the web
                              {else}
                                {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                              {/if}
                        {/if}<br>
                          {if $post->network eq 'twitter'}
                          <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">View on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
                    {/if}
                  </div>
                </div> <!-- /#more-detail -->
              </div>
            </div>
            <div class="grid_5 center keystats omega">
              <div class="big-number">
               {if $retweets}
                  {assign var=reach value=0}
                  {foreach from=$retweets key=tid item=t name=foo}
                   {assign var=reach value=$reach+$t->author->follower_count}
                  {/foreach}
                  <h1>{$post->all_retweets|number_format}{if $post->rt_threshold}+{/if}</h1>
                  <h3>Forward{if $post->all_retweets > 1}s{/if} to {$reach|number_format}</h3>
               {/if} <!-- end if retweets -->
               {if $favds}
                  <h1>{$favds|@count}</h2>
                  <h3>favorites</h3>
               {/if} <!-- end if favds -->
                {/if}
              </div>
            </div>
          </div> <!-- end .clearfix -->

{if $retweets}
<div class="prepend">
  <div class="append_20 clearfix bt"><br /><br />
    {foreach from=$retweets key=tid item=t name=foo}
      {include file="_post.author_no_counts.tpl" post=$t scrub_reply_username=false}
    {/foreach}
  </div>
</div>
{/if}

{if $favds}
  <div class="prepend">
  <div class="append_20 clearfix bt"><br />
    {foreach from=$favds key=fid item=f name=foo}
        {include file="_user.tpl" f=$f}
    {/foreach}
  </div>
  </div>
{/if}


<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>


{if $is_searchable}
    {include file="_grid.search.tpl"}
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
    
{/if}


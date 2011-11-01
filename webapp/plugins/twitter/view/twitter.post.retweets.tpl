       {if $post}
          <div class="clearfix alert stats">
            <div class="grid_2 alpha">
              <div class="avatar-container">
                <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
             </div>
            </div>
            <div class="grid_10">
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
 
                <!-- more-detail element -->
                <div class="clearfix gray" id="more-detail" style="width:460px;">
                  {if $post->network eq 'twitter'}
                    <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">
                  {/if}
                  {$post->adj_pub_date|date_format:"%b %e, %Y %l:%M %p"}
                    {if $post->network eq 'twitter'}
                      </a>
                    {/if}
                    
                    {if $post->location}<br />from {$post->location}{/if}
                    {if $post->source}
                      <br />via
                      {if $post->source eq 'web'}
                        Web
                      {else}
                        {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                      {/if}
                    {/if}
                    {if $post->network eq 'twitter'}
                      <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></a>
                      <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></a>
                      <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></a>
                    {/if}
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
  <div class="append_20 clearfix section">
  	<h2>Retweets</h2>
    {foreach from=$retweets key=tid item=t name=foo}
      {include file="_post.author_no_counts.tpl" post=$t scrub_reply_username=false}
    {/foreach}
  </div>
</div>
{/if}

{if $favds}
  <div class="prepend">
  <div class="append_20 clearfix section">
  	<h2>Favorited</h2>
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


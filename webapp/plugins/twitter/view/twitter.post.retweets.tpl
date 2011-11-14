       {if $post}
          <div class="clearfix alert stats">
{include file="post.index._top-post.tpl"}

            <div class="grid_6 center keystats omega">
              <div class="big-number">
               {if $retweets}
                  {assign var=reach value=0}
                  {foreach from=$retweets key=tid item=t name=foo}
                   {assign var=reach value=$reach+$t->author->follower_count}
                  {/foreach}
                  <h1>{$post->all_retweets|number_format}{if $post->rt_threshold}+{/if}</h1>
                  <h3>forward{if $post->all_retweets > 1}s{/if} to {$reach|number_format}</h3>
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


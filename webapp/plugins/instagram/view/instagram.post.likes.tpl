
          {if $post}
            <div class="clearfix alert stats">
{include file="post.index._top-post.tpl"}

              <div class="grid_6 center keystats omega">
                <div class="big-number">
                       {if $likes}
                      <h1>{$likes|@count}</h2>
                      <h3>like{if $likes|@count neq 1}s{/if}
                    
                     in {$post->adj_pub_date|relative_datetime}</h3>
                   {/if} <!-- end if favds -->
                </div>
              </div>
            </div> <!-- /.clearfix -->
          {/if} <!-- end if post -->


{if $likes}
<div class="prepend">
  <div class="append_20 clearfix section">
      <h2>Likes</h2>
    {foreach from=$likes key=fid item=f name=foo}
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


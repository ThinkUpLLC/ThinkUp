
          {if $post}
            <div class="clearfix alert stats">
              <div class="grid_2 alpha">
                <div class="avatar-container">
                  <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
                </div>
              </div>

              <div class="grid_10">
                <div class="br" style="min-height:110px">
                  <div class="tweet pr">
                    {if $post->post_text}
                          {$post->post_text}
                          {if $post->is_protected}
                                <span class="sprite icon-locked"></span>
                          
						  {/if}
                    {/if}
                  </div>

                  {if $post->link->expanded_url and !$post->link->image_src and $post->link->expanded_url != $post->link->url}
                    <div class="clearfix">
                      <a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">{$post->link->expanded_url}</a><span class="ui-icon ui-icon-newwin">
                    </div>
                  {/if}

      {if $post->link->expanded_url}
      <br>
        {if $post->link->image_src}
         <div class="pic" style="float:left;margin-right:5px;margin-top:5px;"><a href="{$post->link->url}"><img src="{$post->link->image_src}" style="margin-bottom:50px;"/></a></div>
        {/if}
         <span class="small"><a href="{$post->link->url}" title="{$post->link->expanded_url}">{if $post->link->title}{$post->link->title}{else}{$post->link->url}{/if}</a>
        {if $post->link->description}<br><small>{$post->link->description}</small>{/if}</span>
      {/if}

                  <!-- more-detail element -->
                  <div class="clearfix gray" id="more-detail" style="width:460px;">
                    {$post->adj_pub_date|date_format:"%b %e, %Y %l:%M %p"} 
                      {if $post->location}from {$post->location}<br>{/if}
                      <!--{if $post->in_reply_to_post_id}<a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}">In reply to</a>{/if}
                      {if $post->in_retweet_of_post_id}<a href="{$site_root_path}post/?t={$post->in_retweet_of_post_id}">In retweet of</a><br>{/if}
                      -->
                          {if $post->source}
                                {if $post->source eq 'web'}
                                  Web
                                {else}
                                  {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                                {/if}
                          {/if}

                    {if $disable_embed_code != true}
                    <div>
                    Embed this thread:<br>
                    <textarea cols="55" rows="3">&lt;script src=&quot;http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}api/embed/v1/thinkup_embed.php?p={$smarty.get.t}&n={$smarty.get.n|urlencode}&quot;>&lt;/script></textarea>
                    </div>
                    {/if}
                    
                  </div> <!-- /#more-detail -->

                  <!--{if $post->is_geo_encoded eq 1}
                  <div>
                  <a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                    <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
                  </a>
                  </div>
                  {/if}-->
                </div>
              </div>

              <div class="grid_5 center keystats omega">
                <div class="big-number">
                       {if $likes}
                      <h1>{$likes|@count}</h2>
                      <h3>likes</h3>
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


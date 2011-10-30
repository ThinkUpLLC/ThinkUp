<div class="section">
<div class="">
          {if $post}
            <div class="clearfix alert stats">
            {insert name="help_link" id=$display}
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
                          {/if}<br>
                    
                  </div> <!-- /#more-detail -->
                </div>
              </div>

            <div class="grid_5 center keystats omega">
              <div class="big-number">
               {if $post->favlike_count_cache}
                  <h2>{$post->favlike_count_cache}</h2>
                  <h3>+1s</h3>
              </div>
            </div>
        {/if}{/if}

{if $description}<h2>{$description}</h2>{/if}

<div class="header">
  
      {if $is_searchable}<a href="#" class="grid_search" title="Search" onclick="return false;"><span id="grid_search_icon">Search</span></a>{/if}
      {if $logged_in_user and $display eq 'all_gplus_posts'} | <a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Export</a>{/if}

</div>

{if $display eq 'all_gplus_posts' or $gplus_posts}
{else}
    {if $disable_embed_code != true}
        <div class="alert stats">
        <h6>Embed this thread:</h6>
        <textarea cols="55" rows="2" id="txtarea" onClick="SelectAll('txtarea');">&lt;script src=&quot;http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}api/embed/v1/thinkup_embed.php?p={$smarty.get.t}&n={$smarty.get.n|urlencode}&quot;>&lt;/script></textarea>
        </div>
        {literal}
        <script type="text/javascript">
        function SelectAll(id) {
        document.getElementById(id).focus();
        document.getElementById(id).select();
        }
        </script>
        {/literal}
    {/if}
{/if}

{if ($display eq 'all_gplus_posts' and not $gplus_posts) or 
    ($display eq 'most_replied_to_posts' and not $gplus_posts) }
  <div class="alert urgent"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No posts to display.
    </p>
  </div>
{/if}

{if $gplus_posts}
<div id="all-posts-div">
  {foreach from=$gplus_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}

<div class="view-all" id="older-posts-div">
  {if $next_page}
    <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
  {/if}
  {if $last_page}
    | <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
  {/if}
</div>
</div>
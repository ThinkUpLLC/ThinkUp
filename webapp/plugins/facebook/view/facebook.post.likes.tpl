       {if $post}
          {if $post}
            <div class="clearfix">
              <div class="grid_2 alpha">
                <div class="avatar-container">
                  <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
                </div>
              </div>

              <div class="grid_12">
                <div class="br" style="min-height:110px">
                  <div class="tweet pr">
                    {if $post->post_text}
                        {if $post->network == 'twitter'}
                          {$post->post_text|filter_xss|link_usernames_to_twitter}
                          <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></a>
                          <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></a>
                          <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></a>
                        {else}
                          {$post->post_text}
                          {if $post->is_protected}
                                <span class="sprite icon-locked"></span>
                          {/if}
                          
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
                  <div class="clearfix prepend append">
                    <span id="button" class="float-l ui-icon ui-icon-circle-arrow-s"></span>
                  </div>

                  <div class="clearfix gray" id="more-detail" style="display:none;width:460px;">
                    <div class="grid_2 alpha">
                        <img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" />
                      </div>
                    <div class="grid_5">
                        {$post->adj_pub_date|date_format:"%D"} @ {$post->adj_pub_date|date_format:"%I:%M %p"}<br>
                          {if $post->location}{$post->location}<br>{/if}
                          <!--{if $post->in_reply_to_post_id}<a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}">In reply to</a>{/if}
                          {if $post->in_retweet_of_post_id}<a href="{$site_root_path}post/?t={$post->in_retweet_of_post_id}">In retweet of</a><br>{/if}
                      -->
                      </div>
                    <div class="grid_4 omega">
                          {if $post->source}
                                {if $post->source eq 'web'}
                                  Web
                                {else}
                                  {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                                {/if}
                          {/if}<br>
                            {if $post->network eq 'twitter'}
                            <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">View on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
                      {/if}
                    </div>
                    {if $disable_embed_code != true}
                    <div class="grid_15 omega">
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

              <div class="grid_5 omega center keystats">
                <div class="big-number">
                       {if $likes}
                      <h1>{$likes|@count}</h2>
                      <h3>likes</h3>
                   {/if} <!-- end if favds -->
                </div>
              </div>
            </div> <!-- /.clearfix -->
          {/if} <!-- end if post -->
    {/if}

{if $likes}
  <div class="prepend">
  <div class="append_20 clearfix bt"><br />
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


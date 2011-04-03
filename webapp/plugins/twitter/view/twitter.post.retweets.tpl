    {if $error}
    <p class="error">
        {$error}
    </p>    
    {/if}
    
       {if $post}
          <div class="clearfix">
            <div class="grid_2 alpha">
            <div class="avatar-container">
              <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
             </div>
            </div>
            <div class="grid_12">
              <div class="br">
                <div class="clearfix pr">
                  <div id="post_detail" class="float-r"><span class="ui-icon ui-icon-squaresmall-plus"></span></div>
                </div>
                <span class="tweet pr">
                  {if $post->post_text}
                    {$post->post_text|link_usernames_to_twitter}
                  {else}
                    <span class="no-post-text">No post text</span>
                  {/if}
                </span>
                {if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
                  <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">
                    {$post->link->expanded_url}
                  </a>
                {/if}
                
                <!-- tooltip element -->
                <div class="tooltip">
                	<img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" style="float:left;margin:0 15px 20px 0" />
                	<table style="margin:0">
                		<tr>
                			<td class="label">Posted:</td>
                			<td>{$post->adj_pub_date|date_format:"%D"} @ {$post->adj_pub_date|date_format:"%I:%M %p"}</td>
                		</tr>
                		{if $post->location}
                		<tr>
                			<td class="label">From:</td>
                			<td>{$post->location}</td>
                		</tr>
                		{/if}
                		{if $post->source}
                		<tr>
                			<td class="label">Via:</td>
                			<td>
                			  {if $post->source eq 'web'}
                			    the web
                			  {else}
                			    {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                			  {/if}
                			</td>
                		</tr>
                		{/if}
            			  {if $post->network eq 'twitter'}
                		<tr>
                			<td class="label">Link:</td>
                			<td>
                          <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">View on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
                			</td>
                		</tr>		
                    {/if}
                	</table>
                </div> <!-- /.tooltip -->

                {literal}
                <script>
                  $("#post_detail").tooltip({ 
                    tip: '.tooltip',
                    effect: 'fade',
                    offset: [0, 0],
                    position: 'bottom center',
                    relative: true,
                    opacity: 1,
                    delay: 30,
                  });
                </script>
                {/literal}

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
                  {assign var=reach value=0}
                  {foreach from=$retweets key=tid item=t name=foo}
                   {assign var=reach value=$reach+$t->author->follower_count}
                  {/foreach}
                  <h1>{$post->all_retweets|number_format}{if $post->rt_threshold}+{/if}</h1>
                  <h3>Forward{if $post->all_retweets > 1}s{/if} to {$reach|number_format}</h3>
                {/if}
              </div>
            </div>
              
          </div> <!-- end .clearfix -->

{if $retweets}
  <div class="append_20 clearfix bt"><br />
    {foreach from=$retweets key=tid item=t name=foo}
      {include file="_post.clean.tpl" t=$t sort='no' scrub_reply_username=false}
    {/foreach}
  </div>
{/if}

<script type="text/javascript">
  {literal}
  $(function() {
    // Begin reply assignment actions.
    $(".button").click(function() {
      var element = $(this);
      var Id = element.attr("id");
      var oid = Id;
      var pid = $("select#pid" + Id + " option:selected").val();
      var u = '{/literal}{$i->network_username|escape:'url'}{literal}';
      var t = 'inline.view.tpl';
      var ck = '{/literal}{$i->network_username|escape:'url'}-{$logged_in_user}-{$display}{literal}';
      var dataString = 'u=' + u + '&pid=' + pid + '&oid[]=' + oid + '&t=' + t + '&ck=' + ck;
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}post/mark-parent.php",
        data: dataString,
        success: function() {
          $('#div' + Id).html("<div class='success' id='message" + Id + "'></div>");
          $('#message' + Id).html("<p>Saved!</p>").hide().fadeIn(1500, function() {
            $('#message'+Id);  
          });
        }
      });
      return false;
    });
  });
  {/literal}
</script>

{if $is_searchable}
    {include file="_grid.search.tpl"}
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
    
{/if}


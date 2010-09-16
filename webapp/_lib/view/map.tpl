{include file="_header.tpl" load="no"}

<script type="text/javascript">{$posts_data}</script>
<script type="text/javascript">
    var geo = "{$post->geo}";
    var latlng = geo.split(',');
</script>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key={$gmaps_api}" type="text/javascript">
</script>
<script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/markerclusterer_packed.js"></script>
<link rel="stylesheet" type="text/css" href="{$site_root_path}plugins/geoencoder/assets/css/maps.css" />

<body onload="initializeMap()" onunload="GUnload()">
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix heading">
      {if $errormsg}
        {include file="_usermessage.tpl"}
      {else}
        <div class="grid_2 alpha">
          <div class="avatar-container">
          <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
          </div>
        </div>
        <div class="{if $replies or $retweets}grid_13{else}grid_19{/if}">
          <span class="tweet">
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
          <div class="grid_10 omega small gray {if $replies or $retweets}prefix_3 prepend{else}prefix_10{/if}">
            <img src="{$site_root_path}assets/img/social_icons/{$post->network}.png" class="float-l">
            Posted at {$post->adj_pub_date} via {$post->source} <br>
            From: {$post->location}
          </div>
        </div>
        <br /><br /><br /><br />
    <div id="wrap">
      <div id="userpanel">
        <div class="button">
          <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network}">&larr; back to post</a>
        </div>
        <h3>All Post Locations</h3>
        <div id="markerlist"></div>
      </div>
      <div id="mappanel">
        <div id="map"></div>
      </div>
      {/if}
    </div>

<h2 style="font-size:150%;margin-top:10px">Nearest Responses</h2>
  <div class="append_20 clearfix"><br />
  {foreach from=$posts_by_location key=tid item=t name=foo}
   {if !$smarty.foreach.foo.first}
    {include file="_post.tpl" t=$t sort='no' scrub_reply_username=true show_distance=true}
  {/if}
  {/foreach}
  {if !$logged_in_user && $private_reply_count > 0}
  <span style="font-size:12px">Plus {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if} not shown.</span>
  {/if}
  
  </div>
      </div>

</div>


{include file="_footer.tpl"}

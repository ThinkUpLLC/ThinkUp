<div class="grid_2 alpha">
<div class="avatar-container">
<img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
</div>
</div> <!-- end .grid_2 -->
<div class="grid_11">
<div class="tweet clearfix">

{if $post->link->is_image}
<div class="pic float-r ml_10"><a href="{$post->link->url}"><img src="{$post->link->expanded_url}" /></a></div>
{/if}
<!-- POST -->
{if $post->post_text}
{$post->post_text|link_usernames_to_twitter}
{else}
<span class="no-post-text">No post text</span>
{/if}
<!-- LINK -->
{if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
<br>
<a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}" class="small">
{$post->link->expanded_url}
</a>
{/if}

</div>
{*
<div class="small gray prepend">
<!-- POST NETWORK ICON -->
<img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" class="float-l">
<!-- POST DATESTAMP -->
Posted at {$post->adj_pub_date}{if $post->source} via {$post->source}{/if}<br>
{if $post->location}From: {$post->location}{/if}
<!-- POST GEO-LOCATION -->
{if $post->is_geo_encoded eq 1}
<div>
<a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
<img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
</a>
</div>
{/if}
</div>
*}
</div> <!-- end .grid_11 -->
<div class="grid_9 omega">

<!-- TOOL BUTTONS -->
<div class="clearfix small append">

<!-- BACK HOME BUTTON -->
<a href="{$site_root_path}index.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
<span class="ui-icon ui-icon-circle-arrow-w"></span>
Back home
</a>

<!-- MAP BUTTON -->
{if $post->is_geo_encoded eq 1}
<a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
<span class="ui-icon ui-icon-pin-s"></span>
Map
</a>
{/if}
</div>
<div class="clearfix small">
<div class="bb clearfix">
<div class="grid_2 alpha">Network</div>
<div class="grid_5 omega">{$post->network}</div>
</div>
<div class="bb clearfix">
<div class="grid_2 alpha">Date</div>
<div class="grid_5 omega">{$post->adj_pub_date}</div>
</div>
<div class="bb clearfix">
<div class="grid_2 alpha">Life span</div>
<div class="grid_5 omega">{$post->adj_pub_date|relative_datetime}</div>
</div>
<div class="bb clearfix">
<div class="grid_2 alpha">Client</div>
<div class="grid_5 omega">{if $post->source}{$post->source}{/if}</div>
</div>
<div class="grid_2 alpha">Location</div>
<div class="grid_5 omega">{if $post->location}{$post->location}{/if}</div>
</div>
{*

<div class="post-stats clearfix" style="padding-top:10px;">
<div class="grid_3 alpha center">
<div class="round-all border-all">
<h1>{$post->reply_count_cache|number_format}</h1>
Repl{if $post->reply_count_cache == 1}y{else}ies{/if}
</div>
</div>
<div class="grid_5 alpha center">
<div class="round-all border-all">
<h1>{$retweets|@count|number_format}|{$retweet_reach|number_format}</h1>
Forwards|Reach
</div>
</div>
<!--
<div class="grid_3 omega center">
<div class="round-all border-all">
<h1>{$retweet_reach|number_format}</h1>
Reach
</div>
</div>
-->
</div>
{if $logged_in_user}
<!-- SEARCH -->
<a href="#" class="grid_search" title="Search" onclick="return false;"><img src="{$site_root_path}assets/img/search-icon.gif" id="grid_search_icon"></a>
{/if}
*}
</div> <!-- end grid_9 -->
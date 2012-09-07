{*
Render a checkin, ie, a post with place information.

Included in multiple plugin templates which render lists of posts.

Parameters:
$post (required) Post object
*}

{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_14 alpha">&#160;</div>
    <div class="grid_2 omega">
      comments
    </div>
  </div>
{/if}

<div class="clearfix article"> 
    <div class="individual-tweet post clearfix">
        <div class="grid_4 alpha">
            <div class="map-image-container">
                {if $post->place_obj->map_image}
                    <a href="http://maps.google.com/maps?q={$post->geo}"><img src="{$post->place_obj->map_image}" class="map-image2"/>
                    <img src="{$post->place_obj->icon}" class="place-icon2"/></a>
                {/if}
            </div>
        </div>
        <div class="grid_10">
            <div class="post">
                {if $post->post_text != " "}{$post->post_text}<br><br>{/if}
                {foreach from=$post->links item=current_link}
                   <a href="{$current_link->url}"><img src="{$current_link->url}" width=100px height=100px}></a><br> 
                {/foreach}

                {$post->place} <br> {$post->location} <br>
                <div class="small gray">
                <span class="metaroll">
                    <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->pub_date|relative_datetime} ago</a> via {$post->source}
                </span>
                </div>
            </div>
        </div>

        <div class="grid_2 omega">
            {if $post->reply_count_cache > 0}
                <span class="reply-count">
                <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->reply_count_cache|number_format}</a></span>
            {else}
                &#160;
            {/if}
        </div>
    </div>
</div>

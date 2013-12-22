{if $hide_insight_header}

{else}
    {if $i->slug|substr:24 eq 'replies_frequent_words_'}
        <div class="pull-right detail-btn"><a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}" class="btn btn-info btn-mini detail-btn" ><i class="icon-comment icon-white"></i></a></div>
    {/if}
    {if $i->slug eq 'geoencoded_replies'}
        <div class="pull-right detail-btn"><a href="{$site_root_path}post/?v=geoencoder_map&t={$post->post_id}&n=twitter"><button class="btn btn-info btn-mini detail-btn" ><i class="icon-map-marker icon-white"></i></button></a></div>
    {/if}

    <span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-{$icon}"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>
        <i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
        {$i->text|link_usernames_to_twitter}
{/if}

<table class="table table-condensed lead">
    <tr>
    <td class="avatar-data">
            <a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar}" class=""  width="48" height="48"/></a>
    </td>
    <td>
            {if $post->network eq 'twitter'}
                {if $i->instance->network_username != $post->author_username}

                    <h4><a href="https://twitter.com/intent/user?user_id={$post->author_user_id}">{$post->author_fullname}</a></h4>
                    <p class="twitter-bio-info"><i class="icon-twitter"></i> <a href="https://twitter.com/intent/user?user_id={$post->author_user_id}">@{$post->author_username}</a> <small>{$post->place}</small>

                        {if $post->is_geo_encoded < 2}
                            <small>
                          {if $show_distance}
                              {if $unit eq 'km'}
                                {$post->reply_retweet_distance|number_format} kms away
                                {else}
                                {$post->reply_retweet_distance|number_format} miles away in
                              {/if}
                          {/if}
                          {if $post->location}
                          from {$post->location|truncate:60:' ...'}
                          {/if}
                            </small>
                        {/if}
                    </p>
                {/if}
                {if $post->post_text}
                    {if $scrub_reply_username}
                        {if $reply_count && $reply_count > $top_20_post_min}
                          <div class="reply_text post" id="reply_text-{$smarty.foreach.foo.iteration}">
                        {/if}
                        {$post->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
                    {else}
                        <div class="post">{$post->post_text|filter_xss|link_usernames_to_twitter}
                    {/if}
                {/if}

            {else}
                <h3><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.png" class="service-icon2"/> {$post->author_fullname}
                    {if $post->network == 'foursquare'}<a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->place}</a>{/if}
                    {if $post->other.total_likes}<small style="color:gray">{$post->other.total_likes|number_format} likes</small>{/if}
                </h3>
                <div class="post">
                    {$post->post_text}
                    {if $post->network == 'youtube'}
                        <br>
                        <iframe id="ytplayer" type="text/html" width="427" height="260" src="http://www.youtube.com/embed/{$post->post_id}"frameborder="0"/></iframe>
                    {/if}
                    {if $post->network == 'foursquare'}From {$post->location}{/if}
            {/if}

            {if $post->link->expanded_url}
              {if $post->post_text != ''}<br>{/if}
              {if $post->link->image_src}
               <div class="pic" style="float:left;margin-right:5px;margin-top:5px;"><a href="{$post->link->expanded_url}"><img src="{$post->link->image_src}" style="margin-bottom:5px;"/></a></div>
              {/if}
               <span class="small"><a href="{$post->link->url}" title="{$post->link->expanded_url}">{if $post->link->title}{$post->link->title}{else}{$post->link->url}{/if}</a>
              {if $post->link->description}<br><small>{$post->link->description}</small>{/if}</span>
            {/if}

            {if !$post && $post->in_reply_to_post_id }
                <a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}&n={$post->network|urlencode}"><span class="ui-icon ui-icon-arrowthick-1-w" title="reply to..."></span></a>
            {/if}


            {if $post->network == 'twitter'}
                <p class="twitter-bio-info">
                <a href="http://twitter.com/{$post->author_user_id}/statuses/{$post->post_id}">{$post->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}</a>

                &nbsp;&nbsp;
                <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><i class="icon icon-reply" title="reply"></i></a>
                <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><i class="icon icon-retweet" title="retweet"></i></a>
                <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><i class="icon icon-star-empty" title="favorite"></i></a>
                </p>
            {else}
                <span class="metaroll">
                    <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->adj_pub_date|relative_datetime} ago</a>
                </span>
            {/if}
        </div> <!-- end body of post div -->

    </td>
    </tr>
</table>

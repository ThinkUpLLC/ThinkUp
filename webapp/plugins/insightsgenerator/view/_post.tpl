{if $hide_insight_header}

{else}
    {if $i->slug|substr:24 eq 'replies_frequent_words_'}
        <div class="pull-right detail-btn"><a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}" class="btn btn-info btn-mini detail-btn" ><i class="icon-comment icon-white"></i></a></div>
    {/if}
    {if $i->slug eq 'geoencoded_replies'}
        <div class="pull-right detail-btn"><a href="{$site_root_path}post/?v=geoencoder_map&t={$post->post_id}&n=twitter"><button class="btn btn-info btn-mini detail-btn" ><i class="icon-map-marker icon-white"></i></button></a></div>
    {/if}

    <span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-{$icon}"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->prefix}</a></span> 
        <i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
        {$i->text|link_usernames_to_twitter}
{/if}

<table class="table table-condensed lead">
    <tr>
    <td>
            {if $post->network eq 'twitter'}

                    <blockquote class="twitter-tweet">
                        <p>{$post->post_text}</p>
                        &mdash; {$post->author_fullname} (@{$post->author_username}) <a href="https://twitter.com/twitterapi/status/{$post->post_id}" data-datetime="{$post->adj_pub_date}">{$post->adj_pub_date}</a>
                    </blockquote>

            {else}
            
        {if $i->instance->network_username != $post->author_username }
    <div class="avatar-data">
            <h3><a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar}" class="avatar2"  width="48" height="48"/></a></h3>
    </div>
        {/if}


                <h3><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.png" class="service-icon2"/> {$post->author_fullname}
                    {if $post->network == 'foursquare'}<a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->place}</a>{/if}
                    {if $post->other.total_likes}<small style="color:gray">{$post->other.total_likes|number_format} likes</small>{/if}
                </h3>
                <div class="post">
                    {$post->post_text}
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

            <span class="metaroll">
                <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->adj_pub_date|relative_datetime} ago</a>
            </span>

        </div> <!-- end body of post div -->

    </td>
    </tr>
</table>

{if $hide_insight_header}

{else}
<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}inverse{/if}">{$i->prefix}</span> 
    <i class="icon-{if $i->emphasis eq '1'}time{elseif $i->emphasis eq '2'}thumbs-up{elseif $i->emphasis eq '3'}warning-sign{else}retweet{/if}"></i>
    {$i->text}
{/if}

<div class="post lead">
  {if $post->post_text}
    {if $scrub_reply_username}
      {if $reply_count && $reply_count > $top_20_post_min}
          <div class="reply_text" id="reply_text-{$smarty.foreach.foo.iteration}">
      {/if} 
      {$post->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
      {if $reply_count && $reply_count > $top_20_post_min}</div>{/if}
    {else}
     {if $post->network == 'google+'}
      {$post->post_text}
     {else}
      {$post->post_text|filter_xss|link_usernames_to_twitter}
      {/if}
    {/if}
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

    <div class="metaroll">
    <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->adj_pub_date|relative_datetime} ago</a>
    {if $post->is_geo_encoded < 2}
      {if $show_distance}
          {if $unit eq 'km'}
            {$post->reply_retweet_distance|number_format} kms away
            {else}
            {$post->reply_retweet_distance|number_format} miles away in 
          {/if}
      {/if}
     from {$post->location|truncate:60:' ...'}
    {/if}
    {if $post->network == 'twitter'}
    <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></span></a>
    <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></span></a>
    <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></span></a>
    {/if}
    </div>
</div>

{if $i->slug eq 'geoencoded_replies'}

<div class="pull-right detail-btn"><button class="btn-minid detail-btn" data-toggle="collapse" data-target="#map-{$i->id}"><i class="icon-map-marker"></i></button></div>
<div class="collapse in" id="map-{$i->id}">
<script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/iframe.js"></script>
<iframe width="680" frameborder="0" src="{$site_root_path}plugins/geoencoder/map.php?pid={$post->post_id}&n=twitter&t=post" name="childframe" id="childframe" >
</iframe>
</div>
{/if}
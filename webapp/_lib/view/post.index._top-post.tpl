<div class="grid_2 alpha">
  <div class="avatar-container">
    <img src="{if $post->author->avatar}{$post->author->avatar}{else}{$post->author_avatar}{/if}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.png" class="service-icon2"/>
  </div>
</div>

<div class="grid_10">
  <div class="br" style="min-height:110px;margin-bottom:1em">
    <div class="tweet pr">
      {if $post->post_text}
          {if $post->network == 'twitter'}
            {$post->post_text|filter_xss|link_usernames_to_twitter}
          {else}
            {$post->post_text}
            {if $post->is_protected}
                  <span class="sprite icon-locked"></span>
            {/if}
          {/if}
      {/if}
    </div>
   {if $post->network eq 'foursquare'}
     {if $post->post_text neq ' '}<br>{/if}{$post->place}<br>
     <a href="http://maps.google.com/maps?q={$post->geo}"><img src="http://maps.googleapis.com/maps/api/staticmap?size=350x150&zoom=15&maptype=roadmap&markers=color:blue%7C{$post->geo}&sensor=false"></a>
   {/if}

    {foreach from=$post->links key=lkey item=link name=linkloop}
    <div class="clearfix" style="word-wrap:break-word;">
        {if $post->network eq 'foursquare' && $smarty.foreach.linkloop.first}
          <br>
        {/if}
        {if $link->expanded_url}
          {if $link->image_src}
           <div class="pic" style="float:left;margin-right:5px;margin-top:5px;"><a href="{$link->url}"><img src="{$link->image_src}" style="margin-bottom:5px;"/></a></div>
          {/if}
           <span class="small"><a href="{$link->expanded_url}" title="{$link->url}">{if $link->title}{$link->title}{else}{$link->expanded_url}{/if}</a>
          {if $link->description}<br><small>{$link->description}</small>{/if}</span>
        {/if}
    </div>
    {/foreach}

    <div class="clearfix gray" id="more-detail">
    <br>
      {if $post->network eq 'twitter'}
        <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">
      {/if}
      {if $post->network eq 'foursquare'}
        <a href="https://foursquare.com/user/{$post->author_user_id}/checkin/{$post->post_id}">
      {/if}
      {$post->adj_pub_date|date_format:"%b %e, %Y %l:%M %p"}
      {if $post->network eq 'twitter' || $post->network eq 'foursquare'}
        </a>
      {/if}
      
      {if $post->location} from {$post->location}{/if}
      {if $post->source}
        <br />via
        {if $post->source eq 'web'}
          Web
        {else}
          {$post->source}<span class="ui-icon ui-icon-newwin"></span>
        {/if}
      {/if}
      {if $post->network eq 'twitter'}
        <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}">
        <span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></span></a>
        <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}">
        <span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></span></a>
        <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}">
        <span class="ui-icon ui-icon-star" title="favorite"></span></a>
      {/if}
    {if $disable_embed_code != true && $show_embed}
    <a href="javascript:;" title="Embed this thread" onclick="$('#embed-this-thread').show(); return false;">
    <span class="ui-icon ui-icon-carat-2-e-w"></span></a>
    {/if}
    </div> <!-- /#more-detail -->
  </div>
</div>

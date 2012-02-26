{*
Render a post without an author and with replies or retweet counts.

Included in multiple plugin templates which render lists of posts.

Parameters:
$post (required) Post object
$scrub_reply_username (optional) If set or not false, scrub the @reply username from the post_text
$show_favorites_instead_of_retweets (optional) If set or not false, show favorites instead of retweet counts.
*}

{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_13 alpha">&#160;</div>
    <div class="grid_2 center">
      {if $post->network eq 'twitter' || $post->network eq 'google+'}
        {if $show_favorites_instead_of_retweets}{if $post->network eq 'google+'}+1s{else}favorites{/if}{else}{if $post->network eq 'google+'}reshares{else}retweets{/if}{/if}
     {else}
        {if $show_favorites_instead_of_retweets}{if $post->network eq 'google+'}+1s{else}likes{/if}{/if}
     {/if}
    </div>
    <div class="grid_2 center omega">
      replies
    </div>
  </div>
{/if}

<div class="clearfix article">
  <div class="individual-tweet post clearfix{if $post->is_protected} private{/if}">
    <div class="grid_13 alpha">
      <div class="post">
        {if $post->post_text}
          {if $scrub_reply_username}
            {$post->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
          {else}
          {if $post->network == 'google+'}
            {$post->post_text|strip_tags|truncate:'150'}
           {else}
            {$post->post_text|filter_xss|link_usernames_to_twitter}
            {/if}
          {/if}
        {/if}

      {foreach from=$post->links key=lkey item=link name=linkloop}
          {if $link->expanded_url}
            {if $post->post_text != ''}<br>{/if}
            {if $link->image_src}
             <div class="pic" style="float:left;margin-right:5px;margin-top:5px;"><a href="{$link->url}"><img src="{$link->image_src}" style="margin-bottom:5px;"/></a></div>
            {/if}
             <span class="small"><a href="{$link->expanded_url}" title="{$link->url}">{if $link->title}{$link->title}{else}{$link->expanded_url}{/if}</a>
            {if $link->description}<br><small>{$link->description}</small>{/if}</span>
          {/if}
      {/foreach}
      <br clear="all">

        {if !$post && $post->in_reply_to_post_id }
          <a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}&n={$post->network|urlencode}"><span class="ui-icon ui-icon-arrowthick-1-w" title="reply to..."></span></a>
        {/if}

      <div class="small gray">
        <span class="metaroll">
        <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->adj_pub_date|relative_datetime} ago</a>
        {if $post->is_geo_encoded < 2}
        from {$post->location|truncate:60:' ...'}
       {/if}
        {if $post->network == 'twitter'}
        <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></span></a>
        <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></span></a>
        <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></span></a>
        {/if}
      </span>&nbsp;</div>
      </div><!--end post-->
      
    </div>
    <div class="grid_2 center">
    {if $post->network eq 'twitter' || $post->network eq 'google+'}
     {if $show_favorites_instead_of_retweets && $show_favorites_instead_of_retweets != false}
       {if $post->favlike_count_cache}
       <span class="reply-count">
          <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}&v={if $post->network eq 'twitter'}favs{else}plus1s{/if}">{$post->favlike_count_cache|number_format}</a>
       </span>
      {else}
        &#160;
      {/if}
    {else}
      {if $post->all_retweets > 0}
        <span class="reply-count">
        <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}&v=fwds">{$post->all_retweets|number_format}{if $post->rt_threshold}+{/if}<!-- retweet{if $post->retweet_count_cache eq 1}{else}s{/if}--></a>
        </span>
      {else}
        &#160;
      {/if}
      {/if}
    {/if}
    {if $post->network eq 'facebook' OR $post->network eq 'facebook page'}
        {if $post->favlike_count_cache  > 0}
        <span class="reply-count">
            <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}&v=likes">{$post->favlike_count_cache|number_format}</a>
        </span>
        {else}
        &#160;
        {/if}
    {/if}
    </div>
    <div class="grid_2 center omega">
      {if $post->reply_count_cache > 0}
        <span class="reply-count">
        <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network|urlencode}">{$post->reply_count_cache|number_format}<!-- repl{if $post->reply_count_cache eq 1}y{else}ies{/if}--></a>
        </span>
      {else}
        &#160;
      {/if}
    </div>
  </div>
</div>
{*
Renders a post object in related_data.

Parameters:
$post (required) post object
$hide_avatar (optional) do not display the user's avatar, typically used if the post is the user's own
*}

{if isset($post)}
<blockquote class="tweet{if $hide_avatar} hide-photo{/if}">
  <a href="{include file=$tpl_path|cat:"_user.networklink.tpl" network=$post->network user_id=$post->author_user_id username=$post->author_username}" title="{$post->author_username}"><img src="{insert name='user_avatar' avatar_url=$post->author_avatar image_proxy_sig=$image_proxy_sig}" alt="{$post->author_username}" width="60" height="60" class="img-circle pull-left tweet-photo user-photo"></a>
  <div class="byline"><a href="{include file=$tpl_path|cat:"_user.networklink.tpl" network=$post->network user_id=$post->author_user_id username=$post->author_username}" title="{$post->author_username}"><strong>{$post->author_fullname}</strong> {if $post->network eq 'twitter'}<span class="username">@{$post->author_username}</span>{/if}</a></div>
  <div class="tweet-body">{$post->post_text|filter_xss|link_usernames_to_network:$post->network}</div>

{*
On Facebook, links may be included with a post but not inline in post_text.
Therefore, on Facebook posts, list links that are not images.
*}
{if $post->network eq 'facebook'}
{foreach from=$post->links item=l}
  {if $l->image_src neq $l->url}
  <div class="tweet-body">
    <div class="link">
      <a href="{$l->url}" class="link-title">
        {if isset($l->title) && $l->title neq ''}
            {$l->title|truncate:100}
        {elseif ($l->caption neq '')}
            {$l->caption|truncate:100}
        {elseif $l->expanded_url}
            {$l->expanded_url|truncate:40}
        {else}
            {$l->url|truncate:40}
        {/if}
      </a>
    </div>
  </div>
  {/if}
{/foreach}
{/if}

{foreach from=$post->links item=l}
  {if !isset($breakphotos) and isset($l->image_src) and $l->image_src neq ""}
  <div class="photo clearfix">
    <a href="{$l->url}">
      <img src="{$l->image_src}" class="photo_img" alt="Photo from {$post->author_fullname}">
    </a>
  </div>
  {assign var="breakphotos" value="true"}
  {/if}
{/foreach}

{if isset($post->standard_resolution_url)}
  <div class="photo {if $post->is_short_video}video{/if} clearfix">
    <a href="{$post->permalink}"><img src="{$post->standard_resolution_url}" class="photo_img" alt="Instagram post from {$post->author_fullname}">
    {if $post->is_short_video}<i class="play-button-overlay fa fa-play-circle-o"></i>{/if}
    </a>
  </div>
{/if}

  <div class="tweet-actions">
    <a href="{if $post->network eq 'facebook' || $post->network eq 'twitter'}{if $post->network eq 'twitter'}https://twitter.com/{$post->author_username}/status/{/if}{if
      $post->network eq 'facebook'}https://www.facebook.com/{$post->author_user_id}/posts/{/if}{$post->post_id}{elseif $post->network eq 'instagram'}{$post->permalink}{/if}"
      class="tweet-action tweet-action-permalink">{$post->pub_date|date_format:'%b %e, %Y'}</a>
  {if $post->network eq 'twitter'}
    <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-heart icon"></i></a>
  {/if}
  </div>
</blockquote>
{/if}

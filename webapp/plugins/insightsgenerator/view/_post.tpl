{*
Renders a post object in related_data.

Parameters:
$post (required) post object
$hide_avatar (optional) do not display the user's avatar, typically used if the post is the user's own
*}

{if isset($post)}
<blockquote class="tweet{if $hide_avatar} tweet-without-photo{/if}">
  <a href="{if $post->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $post->network eq 'facebook'}https://facebook.com/{/if}{$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar|use_https}" alt="{$post->author_username}" width="60" height="60" class="img-circle pull-left tweet-photo"></a>
  <div class="byline"><a href="{if $post->network eq 'twitter'}https://twitter.com/intent/user?user_id={elseif $post->network eq 'facebook'}https://facebook.com/{/if}{$post->author_user_id}" title="{$post->author_username}"><strong>{$post->author_fullname}</strong> <span class="username">{if $post->network eq 'twitter'}@{/if}{$post->author_username}</span></a></div>
  <div class="tweet-body">{$post->post_text|filter_xss|link_usernames_to_twitter}</div>
  {if $post->network eq 'twitter'}
  <div class="tweet-actions">
    <a href="//twitter.com/{$post->author_username}/status/{$post->post_id}"
      class="tweet-action tweet-action-permalink">{$post->pub_date|date_format:'%b %e, %Y'}</a>
    <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-star icon"></i></a>
  </div>
  {/if}
</blockquote>
{/if}
{*
Renders a post object in related_data.

Parameters:
$i (required) post object
$hide_avatar (optional) do not display the user's avatar, typically used if the post is the user's own
*}

<blockquote class="tweet{if $hide_avatar} tweet-without-photo{/if}">
  {if $i->network eq 'twitter'}<a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}">{/if}<img src="{$post->author_avatar}" alt="{$post->author_username}" width="60" height="60" class="img-circle pull-left tweet-photo">{if $i->network eq 'twitter'}</a>{/if}
  <div class="byline"><a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}"><strong>{$post->author_fullname}</strong> <span class="username">@{$post->author_username}</span></a></div>
  <div class="tweet-body">{$post->post_text|filter_xss|link_usernames_to_twitter}</div>
  <div class="tweet-actions">
  {if $i->network eq 'twitter'}
    <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}" class="tweet-action"><i class="fa fa-reply icon"></i></a>
    <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
    <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-star icon"></i></a>
  {/if}
  </div>
</blockquote>
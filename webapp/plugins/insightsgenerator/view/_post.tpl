
<ul class="body-list tweet-list">
    <li class="list-item"><blockquote class="tweet tweet-with-photo">
      <a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar}" alt="{$post->author_username}" width="60" height="60" class="img-circle pull-left tweet-photo"></a>
      <div class="byline"><strong>{$post->author_fullname}</strong> <span class="username">@{$post->author_username}</span></div>
      <div class="tweet-body">{$post->post_text|filter_xss|link_usernames_to_twitter}</div>
      <div class="tweet-actions">
        <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}" class="tweet-action"><i class="fa fa-reply icon"></i></a>
        <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
        <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-star icon"></i></a>
    </div></blockquote></li>
</ul>

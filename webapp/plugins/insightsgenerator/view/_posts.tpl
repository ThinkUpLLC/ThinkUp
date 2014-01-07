{*
Renders an insight with an array of post objects in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}

<ul class="tweet-list" style="">
{foreach from=$i->related_data key=uid item=p name=bar}

    <li class="list-item"><blockquote class="tweet tweet-with-photo">
      <a href="https://twitter.com/intent/user?user_id={$post->author_user_id}" title="{$post->author_username}"><img src="{$post->author_avatar}" alt="Matt Jacobs" width="60" height="60" class="img-circle pull-left tweet-photo"></a>
      <div class="byline"><strong>{$post->author_fullname}</strong> <span class="username">@{$post->author_username}</span></div>
      <div class="tweet-body">{$post->post_text|filter_xss|link_usernames_to_twitter}</div>
      <div class="tweet-actions">
        <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}" class="tweet-action"><i class="fa fa-reply icon"></i></a>
        <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
        <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}" class="tweet-action"><i class="fa fa-star icon"></i></a>
    </div></blockquote></li>

<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all 6 tweets</span> <i class="fa fa-chevron-down icon"></i></button>

    {assign var="prev_post_year" value=$p->adj_pub_date|date_format:"%Y"}
{/foreach}
</ul>

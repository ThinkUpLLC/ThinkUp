{*
Renders an insight with an array of links embedded in posts in related_data.

Parameters:
$i (required) Insight object
$posts An array of posts (that contain links!)
*}

{if isset($posts)}
    <ul class="body-list link-list
    {if count($posts) gt 2}body-list-show-some{else}body-list-show-all{/if}">
    {foreach from=$posts key=pid item=post name=bar}

        {foreach from=$post->links key=lid item=l name=link}
        <li class="list-item">
            <div class="link">
                <div class="link-title">
                    <a href="{$l->url}">
                        {if $l->title}
                            {$l->title|truncate:100}
                        {elseif $l->expanded_url}
                            {$l->expanded_url}
                        {else}
                            {$l->url}
                        {/if}
                    </a>
                </div>
                <div class="link-metadata">
                {if $post->network eq 'twitter'}
                    Posted by {'@'|cat:$post->author_username|link_usernames_to_twitter}
                    on <a href="http://twitter.com/{$post->author_user_id}/statuses/{$post->post_id}">
                    {$post->adj_pub_date|date_format:"%b %e"}</a>
                {else}
                    Posted by {$post->author_fullname} on {$post->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}
                {/if}
                </div>
            </div>
        </li>
        {/foreach}

    {/foreach}

    </ul>
    {if count($posts) gt 2}
    <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$posts|@count} links</span> <i class="fa fa-chevron-down icon"></i></button>
    {/if}
{/if}

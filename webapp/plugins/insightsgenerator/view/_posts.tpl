{*
Renders an insight with an array of post objects in related_data.

Parameters:
$i (required) Insight object
$posts: List of multiple posts
*}

{if isset($posts)}
<ul class="body-list tweet-list
{if count($posts) gt 2}body-list-show-some{else}body-list-show-all{/if}">
{foreach from=$posts key=uid item=post name=bar}

    <li class="list-item">
      {include file=$tpl_path|cat:"_post.tpl" post=$post}
    </li>
    {assign var="prev_post_year" value=$p->adj_pub_date|date_format:"%Y"}
{/foreach}
</ul>

{if count($posts) gt 2}
<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$posts|@count} tweets</span> <i class="fa fa-chevron-down icon"></i></button>
{/if}

{/if}
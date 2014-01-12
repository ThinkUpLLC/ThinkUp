{*
Renders an insight with an array of post objects in related_data.

Parameters:
$i (required) Insight object
*}

<ul class="body-list tweet-list
{if count($i->related_data.posts) lte 2}body-list-show-all{else}body-list-show-some{/if}">
{foreach from=$i->related_data.posts key=uid item=post name=bar}

    <li class="list-item">
      {include file=$tpl_path|cat:"_post.tpl" post=$post}
    </li>

{if count($i->related_data.posts) gt 2}
<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$i->related_data.posts|@count} tweets</span> <i class="fa fa-chevron-down icon"></i></button>
{/if}

    {assign var="prev_post_year" value=$p->adj_pub_date|date_format:"%Y"}
{/foreach}
</ul>

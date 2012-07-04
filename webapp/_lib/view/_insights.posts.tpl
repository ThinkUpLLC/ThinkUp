{if $i->slug eq 'posts_on_this_day_flashback'}
    {foreach from=$i->related_data key=uid item=p name=bar}
        {* Hide posts after the first one *}
        {if $smarty.foreach.bar.index eq 1}
            <div class="collapse in" id="flashback-{$i->id}">
        {/if}

        {* Show "X years ago you posted" text if post is from a different year than the last one *}
        {if !$smarty.foreach.bar.first and $prev_post_year neq $p->adj_pub_date|date_format:"%Y"}
            <span style="color:gray">{$p->adj_pub_date|date_format:"%Y"} flashback: {$p->adj_pub_date|relative_datetime} ago, you posted:</span>
        {/if}

        {include file="_insights.post.tpl" post=$p}

        {* Show more link if there are more posts after the first one *}
        {if $smarty.foreach.bar.total gt 0 and $smarty.foreach.bar.first}
            <div class="pull-right" style="margin-top : -12px;"><button class="btn-mini" data-toggle="collapse" data-target="#flashback-{$i->id}"><i class=" icon-chevron-down"></i></button></div>
        {/if}

        {* Close up hidden div if there is one *}
        {if $smarty.foreach.bar.total gt 0 and $smarty.foreach.bar.last}
            </div>
        {/if}

        {assign var="prev_post_year" value=$p->adj_pub_date|date_format:"%Y"}
    {/foreach}
{else}
    {foreach from=$i->related_data key=uid item=p name=bar}
        {include file="_insights.post.tpl" post=$p}
    {/foreach}
{/if}



 

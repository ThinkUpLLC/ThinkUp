<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}inverse{/if}">{$i->prefix}</span> 
                <i class="icon-{if $i->emphasis eq '1'}time{elseif $i->emphasis eq '2'}thumbs-up{elseif $i->emphasis eq '3'}warning-sign{else}star{/if}"></i>
                {$i->text}

{if $i->slug eq 'posts_on_this_day_flashback'}
    {foreach from=$i->related_data key=uid item=p name=bar}
        {* Hide posts after the first one *}
        {if $smarty.foreach.bar.index eq 1}
            <div class="collapse in" id="flashback-{$i->id}">
        {/if}

        {* Show "X years ago you posted" text if post is from a different year than the last one *}
        {if !$smarty.foreach.bar.first and $prev_post_year neq $p->adj_pub_date|date_format:"%Y"}
            <p>{$p->adj_pub_date|relative_datetime} ago in {$p->adj_pub_date|date_format:"%Y"}, you posted:</p>
        {/if}

        {include file="_insights.post.tpl" post=$p hide_insight_header='1'}

        {* Show more link if there are more posts after the first one *}
        {if $smarty.foreach.bar.total gt 1 and $smarty.foreach.bar.first}
            <div class="pull-right detail-btn"><button class="btn btn-mini" data-toggle="collapse" data-target="#flashback-{$i->id}"><i class=" icon-chevron-down"></i></button></div>
        {/if}

        {* Close up hidden div if there is one *}
        {if $smarty.foreach.bar.total gt 1 and $smarty.foreach.bar.last}
            </div>
        {/if}

        {assign var="prev_post_year" value=$p->adj_pub_date|date_format:"%Y"}
    {/foreach}
{else}
    {foreach from=$i->related_data key=uid item=p name=bar}
        {include file="_insights.post.tpl" post=$p}
    {/foreach}
{/if}



 

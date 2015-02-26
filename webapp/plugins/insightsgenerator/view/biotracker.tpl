<ul class="body-list user-list {if $i->related_data.changes|@count > 2}body-list-show-some{else}body-list-show-all{/if}">

{foreach from=$i->related_data.changes item=change name=changed }
<li class="list-item">
    {if $change.field_name eq 'description'}
        {include file=$tpl_path|cat:"_user.tpl" user=$change.user bio_before=$change.before bio_after=$change.after}
    {elseif $change.field_name eq 'avatar'}
        {include file=$tpl_path|cat:"_user.tpl" user=$change.user avatar_before=$change.before avatar_after=$change.after}
    {/if}
</li>
{/foreach}
</ul>

{if $i->related_data.changes|@count > 2}<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$i->related_data.changes|@count} changes</span> <i class="fa fa-chevron-down icon"></i></button>{/if}

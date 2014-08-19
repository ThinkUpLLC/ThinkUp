{if $i->related_data.milestones}
  {include file=$tpl_path|cat:"_bignumbers.tpl"
  milestones=$i->related_data.milestones}
{/if}

<!--
{foreach from=$i->related_data.people key=k item=u name=bar}

        <img src="{$u.user->avatar}" alt="{$u.user->full_name}" class="img-circle pull-left user-photo" style="height: 25px; width: 25px;">

{/foreach}
-->

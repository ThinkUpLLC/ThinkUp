{if $i->related_data.milestones}
  {if $i->related_data.milestones.items|@count eq 1}
    {include file=$tpl_path|cat:"_bignumber.tpl"
    milestone=$i->related_data.milestones.items[0]
    milestone_label_type=$i->related_data.milestones.label_type}
  {else}
    {include file=$tpl_path|cat:"_bignumbers.tpl"
    milestones=$i->related_data.milestones}
  {/if}
{else}
  {if $i->related_data.button}
  {include file=$tpl_path|cat:"_button.tpl" button=$i->related_data.button }
  {/if}
{/if}


{include file=$tpl_path|cat:"_users.tpl" users=$i->related_data.people}

{if $i->related_data.button}
{include file=$tpl_path|cat:"_button.tpl" button=$i->related_data.button }
{/if}

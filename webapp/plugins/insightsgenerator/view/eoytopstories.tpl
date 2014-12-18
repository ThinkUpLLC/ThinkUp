{include file=$tpl_path|cat:"_posts.tpl" posts=$i->related_data.posts}

{if $i->related_data.button}
    {include file=$tpl_path|cat:"_button.tpl" button=$i->related_data.button }
{/if}

{include file=$tpl_path|cat:'_header.tpl'}

{if $i->prefix eq 'Ramping up:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='arrow-circle-up'}
{elseif $i->prefix eq 'Slowing down:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='arrow-circle-down'}
{elseif $i->prefix eq 'Nudge, nudge:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='exclamation-triangle'}
{else}
{include file=$tpl_path|cat:'_textonly.tpl' icon='check-circle-o'}
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
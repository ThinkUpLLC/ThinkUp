{include file=$tpl_path|cat:'_header.tpl'}

{if $i->prefix eq 'Ramping up:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='circle-arrow-up'}
{elseif $i->prefix eq 'Slowing down:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='circle-arrow-down'}
{elseif $i->prefix eq 'Nudge, nudge:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='exclamation-sign'}
{else}
{include file=$tpl_path|cat:'_textonly.tpl' icon='ok-circle'}
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
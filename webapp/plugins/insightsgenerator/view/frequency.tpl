{include file=$tpl_path|cat:'_header.tpl'}

<<<<<<< HEAD
{if $i->headline eq 'Ramping up:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='circle-arrow-up'}
{elseif $i->headline eq 'Slowing down:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='circle-arrow-down'}
{elseif $i->headline eq 'Nudge, nudge:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='exclamation-sign'}
=======
{if $i->prefix eq 'Ramping up:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='arrow-circle-up'}
{elseif $i->prefix eq 'Slowing down:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='arrow-circle-down'}
{elseif $i->prefix eq 'Nudge, nudge:'}
{include file=$tpl_path|cat:'_textonly.tpl' icon='exclamation-triangle'}
>>>>>>> 9e7ded4... Additional Font Awesome icon replacements
{else}
{include file=$tpl_path|cat:'_textonly.tpl' icon='check-circle-o'}
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
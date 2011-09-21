Hello ThinkUp Post Detail Plugin in action!
<br />
Header: {$header}<br />
Description: {$description}
{if $replies_by_date}
This will show a list of replies.

  {foreach from=$replies_by_date key=tid item=t name=foo}
    {include file="_post.tpl" t=$t sort='no' scrub_reply_username=true}
  {/foreach}

{/if}
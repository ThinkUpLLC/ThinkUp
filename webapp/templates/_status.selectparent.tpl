<select name="pid{$t->post_id}" id="pid{$t->post_id}">
  <option value="0">No Tweet in Particular (Mark as standalone)</option>
  <option disabled>Set as a reply to:</option>
{assign var='found_reply' value=false}
{foreach from=$all_tweets key=aid item=a}
  <option value="{$a->post_id}"{if $a->in_reply_to_post_id == $tweet->post_id && $tweet->id > 0} selected{assign var='found_reply' value=true}{/if}>&nbsp;&nbsp;{$a->post_text|truncate_for_select}</option>
{/foreach}
{if $found_reply == false && $tweet->id > 0}
  <option disabled>...</option>
  <option value="{$tweet->post_id}" selected>&nbsp;&nbsp;{$tweet->post_text|truncate_for_select}</option>
{/if}
</select>
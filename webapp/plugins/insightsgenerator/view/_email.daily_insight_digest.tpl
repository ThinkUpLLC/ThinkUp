ThinkUp has new insights for you!

{foreach from=$insights item=insight}
{if $insight->text ne ''}
* {$insight->time_updated|relative_datetime}ago: {$insight->headline|replace:":":""} ({$insight->instance->network|ucfirst})
  {$insight->text|strip_tags:false}
  {$application_url}?u={$insight->instance->network_username|urlencode}&n={$insight->instance->network|urlencode}&d={$insight->date|date_format:"%Y-%m-%d"}&s={$insight->slug}

{/if}
{/foreach}

Sent to you by {$apptitle}.
Change your mail preferences: {$application_url}account/index.php?m=manage#instances

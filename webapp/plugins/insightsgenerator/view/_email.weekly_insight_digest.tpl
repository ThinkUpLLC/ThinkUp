{$apptitle} Weekly Email Digest
Your insights from the past week:

{foreach from=$insights item=insight}
{if $insight->text ne ''}
* {$insight->time_updated|relative_datetime}ago: {$insight->prefix|replace:":":""} ({if $insight->instance->network eq 'twitter'}@{/if}{$insight->instance->network_username})
  {$insight->text|strip_tags:false}
  {$application_url}?u={$insight->instance->network_username}&n={$insight->instance->network}&d={$insight->date|date_format:"%Y-%m-%d"}&s={$insight->slug}
{/if}
{/foreach}

Sent to you by {$apptitle}. 
Change your mail preferences here {$site_root_path}account/index.php?m=manage#instances

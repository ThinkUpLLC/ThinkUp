<ul>
{foreach from=$insights item=insight}
{if $insight->text ne ''}
<li>
    {$insight->time_updated|relative_datetime}ago: {$insight->prefix|replace:":":""} ({$insight->instance->network|ucfirst})
    <br />
    <a href="{$application_url}?u={$insight->instance->network_username|urlencode}&n={$insight->instance->network|urlencode}&d={$insight->date|date_format:"%Y-%m-%d"}&s={$insight->slug}">{$insight->text|strip_tags:false}</a>
</li>
{/if}
{/foreach}
</ul>

{*
Render a plain, text-only insight.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}

<div class="insight-attachment-detail none">
        <span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="fa icon-white fa-{$icon}"></i> <a href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->prefix}</a></span>
        <i class="fa fa-{$i->instance->network}{if $i->instance->network eq 'google+'} fa-google-plus{/if} icon-muted"></i>
        {$i->text|link_usernames_to_twitter}
</div>


{*
Renders an insight with an array of links embedded in posts in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-{$icon}"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span> 

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{assign var='collapse_links' value=true}
{foreach from=$i->related_data key=pid item=p name=bar}

    {foreach from=$p->links key=lid item=l name=lnk}

        {* Show more link if there are more posts after the first one *}
        {if !$expand and ($smarty.foreach.bar.total gt 1 or $smarty.foreach.lnk.total gt 1) and ($smarty.foreach.bar.first and $smarty.foreach.lnk.first)}
            <div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#flashback-{$i->id}"><i class="icon-chevron-down icon-white"></i></button></div>
        {/if}

        {* Hide posts after the first one *}
        {if !$expand and $collapse_links and ($smarty.foreach.bar.index eq 1 or ($smarty.foreach.bar.index eq 0 and $smarty.foreach.lnk.index eq 1))}
            <div class="collapse in" id="flashback-{$i->id}">
            {assign var='collapse_links' value=false}
        {/if}

<table class="table table-condensed">
    <tr>
    <td class="link-image">
        {if isset($l->image_src) and $l->image_src neq ''}
            <h3><a href="{$l->url}" title="{$l->caption}"><img src="{$l->image_src}" class="avatar2"  width="48" height="48"/></a></h3>
        {else}
            <h3><a href="{$l->url}" title="{$l->caption}"><a href="https://twitter.com/intent/user?user_id={$p->author_user_id}" title="{$p->author_username}"><img src="{$p->author_avatar}" class=""  width="48" height="48"/></a></h3>
        {/if}
    </td>

    <td>
        {if isset($l->title) and $l->title neq ''}
            <h3><a href="{$l->url}">{$l->title|truncate:100}</a></h3>
        {else}
            <h3><a href="{$l->url}">{$l->expanded_url|truncate:75}</a></h3>
        {/if}

        {if isset($l->expanded_url) and $l->expanded_url neq ''}
            <p class="link-url"><small><a href="{$l->url}">{$l->expanded_url|truncate:40}</a></small></p>
        {/if}

        <div class="link">
            {if isset($l->description) and $l->description neq ''}
                <p class="link-description">{$l->description|truncate:300}</p>
            {/if}

            <p class="link-detail"><small>
                {if $p->network eq 'twitter'}
                    tweeted by {'@'|cat:$p->author_username|link_usernames_to_twitter}
                    at <a href="http://twitter.com/{$p->author_user_id}/statuses/{$p->post_id}">{$p->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}</a>
                {else}
                    posted by {$p->author_fullname} at {$p->adj_pub_date|date_format:"%l:%M %p - %d %b %y"}
                {/if}
            </small></p>
        </div>
    </td>
    </tr>
</table>

        {* Close up hidden div if there is one *}
        {if !$expand and !$collapse_links and (($smarty.foreach.bar.total gt 1 and $smarty.foreach.bar.last and $smarty.foreach.lnk.last) or ($smarty.foreach.bar.total eq 1 and $smarty.foreach.lnk.total gt 1 and $smarty.foreach.lnk.last))}
            </div>
        {/if}

    {/foreach}

{/foreach}

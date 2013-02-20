{*
Renders an insight with an array of user objects in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-{$icon}"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->prefix}</a></span> 

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{foreach from=$i->related_data key=uid item=u name=bar}

    {* Show more link if there are more posts after the first one *}
    {if !$expand and $smarty.foreach.bar.total gt 1 and $smarty.foreach.bar.first}
        <div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#flashback-{$i->id}"><i class="icon-chevron-down icon-white"></i></button></div>
    {/if}

    {* Hide posts after the first one *}
    {if  !$expand and $smarty.foreach.bar.index eq 1}
        <div class="collapse in" id="flashback-{$i->id}">
    {/if}

<table class="table table-condensed">
    <tr>
    <td class="avatar-data">
        {if $u->network eq 'twitter'}
            <h3><a href="https://twitter.com/intent/user?user_id={$u->user_id}" title="{$u->username} has {$u->follower_count|number_format} followers and {$u->friend_count|number_format} friends"><img src="{$u->avatar}" class="avatar2"  width="48" height="48"/></a></h3>
        {else}
            <h3><img src="{$u->avatar}" class="avatar2" width="48" height="48"/></h3>
        {/if}
    </td>

    <td>
        {if $u->network eq 'twitter'}
            <h3><img src="{$site_root_path}plugins/{$u->network}/assets/img/favicon.png" class="service-icon2"/> <a href="https://twitter.com/intent/user?user_id={$u->user_id}">{$u->full_name}</a>     <small>{$u->follower_count|number_format} followers</small></h3>
            <p>{$u->description|link_usernames_to_twitter}<br />
            {$u->url}</p>
        {else}
            <h3><img src="{$site_root_path}plugins/{$u->network}/assets/img/favicon.png" class="service-icon2"/> {$u->full_name}    {if $u->other.total_likes}<small style="color:gray">{$u->other.total_likes|number_format} likes</small>{/if}</h3>
        {/if}
    </td>
    </tr>
</table>

    {* Close up hidden div if there is one *}
    {if !$expand and $smarty.foreach.bar.total gt 1 and $smarty.foreach.bar.last}
        </div>
    {/if}

{/foreach}

{*
Renders an insight with an array of user objects in related_data.

Parameters:
$user (required) A single user objects
$user_text Determines what to show below the user's name
*}

{if isset($user)}
<div class="user{if $i->header_image ne ''} hide-photo{/if}">
    <a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={else}https://facebook.com/{/if}{$user->user_id}">
        {if $user->avatar ne $i->header_image}
        <img src="{$user->avatar|use_https}" alt="{$user->full_name}" class="img-circle pull-left user-photo">
        {/if}
        <div class="user-about">
            <div class="user-name"><br>{if $user->full_name}{$user->full_name}{else}{$user->username}{/if}</b>
                <i class="fa fa-{$user->network} icon icon-network"></i></div>

        </div>
    </a>
</div>
<div class="link-title">
<img src="//getfavicon.appspot.com/{if $user->expanded_url}{$user->expanded_url|escape:'url'}{else}
{$user->url|escape:'url'}{/if}?defaulticon=lightpng" alt="{$user->title}" width="16" height="16" />
<a href="{$user->url}">{if $user->title}{$user->title|truncate:100}{elseif $l->expanded_url}{$user->expanded_url}
{else}{$user->url}{/if}</a>
</div>
{/if}

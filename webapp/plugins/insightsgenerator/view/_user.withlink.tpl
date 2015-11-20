{*
Renders an insight with an array of user objects in related_data with a specified link instead of follower count.

Parameters:
$user (required) A single user object
$link (required) A link displayed instead of the follower count
$link_label (required) The text of the link
*}

{if isset($user)}
<div class="user{if $i->header_image eq $user->avatar } hide-photo{/if}">
        {if $user->avatar ne $i->header_image}
        <a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={else}https://facebook.com/{/if}{$user->user_id}">
        <img src="{insert name='user_avatar' avatar_url=$user->avatar image_proxy_sig=$image_proxy_sig}" alt="{$user->full_name}" class="img-circle pull-left user-photo">
        </a>
        {/if}
        <div class="user-about">
            <div class="user-name"><a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={else}https://facebook.com/{/if}{$user->user_id}">{if $user->full_name}{$user->full_name}{else}{$user->username}{/if} <i class="fa fa-{$user->network} icon icon-network"></i></a></div>
            <div class="user-text">
                <small><a href="{$link}">{$link_label}</a></small>
                {if $user->description neq ''}
                    <p>{$user->description}</p>
                {/if}
            </div>
        </div>
</div>
{/if}

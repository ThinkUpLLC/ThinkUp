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
            <div class="user-name">{if $user->full_name}{$user->full_name}{else}{$user->username}{/if} <i class="fa fa-{$user->network} icon icon-network"></i></div>
            <div class="user-text">
                <p>{if $user->network eq 'twitter'}
                    {$user->follower_count|number_format} followers
                {else}
                    {if isset($user->other.total_likes)}
                    {$user->other.total_likes|number_format} likes
                    {/if}
                {/if}</p>
                {if $user->description neq ''}
                    <p>{$user->description}</p>
                {/if}
            </div>
        </div>
    </a>
</div>
{/if}

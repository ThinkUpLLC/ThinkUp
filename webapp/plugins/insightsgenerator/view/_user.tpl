{*
Renders an insight with an array of user objects in related_data.

Parameters:
$user (required) A single user objects
$user_text Determines what to show below the user's name
*}

{if isset($user)}
<div class="user">
    <a href="{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={$user->user_id}{/if}">
        <img src="{$user->avatar|use_https}" alt="{$user->full_name}" class="img-circle pull-left user-photo">
        <div class="user-about">
            <div class="user-name">{$user->full_name} <i class="fa fa-{$user->network} icon icon-network"></i></div>
            <div class="user-text">
                {if $user->network eq 'twitter'}
                    {$user->follower_count|number_format} followers
                {else}
                    {if isset($user->other.total_likes)}
                    {$user->other.total_likes|number_format} likes
                    {/if}
                {/if}
            </div>
        </div>
    </a>
</div>
{/if}
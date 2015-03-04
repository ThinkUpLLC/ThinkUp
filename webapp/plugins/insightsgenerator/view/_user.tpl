{*
Renders an insight with an array of user objects in related_data.

Parameters:
$user (required) A single user object
$bio_before (optional) If this is a bio change, the old bio
$bio_after (optional) If this is a bio change, the current bio
*}

{if isset($user)}
<div class="user{if $i->header_image eq $user->avatar || isset($avatar_before)} hide-photo{/if}">
       {if $user->avatar ne $i->header_image && !isset($avatar_before)}
       <a href="{if $user->network eq 'twitter' or $user->network eq 'facebook'}{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={else}https://facebook.com/{/if}{$user->user_id}{else}https://instagram.com/{$user->username}{/if}">
        <img src="{insert name='user_avatar' avatar_url=$user->avatar image_proxy_sig=$image_proxy_sig}" alt="{$user->full_name}" class="img-circle pull-left user-photo">
        </a>
        {/if}
        <div class="user-about">
            <div class="user-name"><a href="{if $user->network eq 'twitter' or $user->network eq 'facebook'}{if $user->network eq 'twitter'}https://twitter.com/intent/user?user_id={else}https://facebook.com/{/if}{$user->user_id}{else}https://instagram.com/{$user->username}{/if}">{if $user->full_name}{$user->full_name}{else}{$user->username}{/if} <i class="fa fa-{$user->network} icon icon-network"></i></a></div>
            <div class="user-text">
                <p>{if $user->network eq 'twitter'}
                    {$user->follower_count|number_format} followers
                {else}
                    {if isset($user->other.total_likes)}
                    {$user->other.total_likes|number_format} likes
                    {/if}
                {/if}</p>

                {if isset($bio_before) and isset($bio_after)}
                <div class="text-diff">
                    <div class="bio-diff">
                        <p>{insert name="string_diff" from_text=$bio_before to_text=$bio_after}</p>
                    </div>

                    <div class="bio-before-after">
                        <p class="bio-before"><strong>Before:</strong><br>
                        {$bio_before|link_usernames_to_twitter}</p>
                        <p class="bio-after"><strong>After:</strong><br>
                        {$bio_after|link_usernames_to_twitter}</p>
                    </div>
                    <p><a class="diff-toggle" href="#" data-alt-text="Show diff">Show before/after</a></p>
                </div>
                {else if $user->description neq ''}
                    {if !isset($avatar_before)}
                    <p>{$user->description}</p>
                    {/if}
                {/if}

                {if isset($avatar_before) and isset($avatar_after)}
                <div class="table-responsive avatar-change">
                    <table class="table table-condensed">
                        <tr>
                            <th class="avatar-before"><img src="{insert name='user_avatar' avatar_url=$avatar_before image_proxy_sig=$image_proxy_sig avatar_size='original'}" alt="{$user->full_name}" class="img-circle"></th>
                            <th class="avatar-after"><img src="{insert name='user_avatar' avatar_url=$avatar_after image_proxy_sig=$image_proxy_sig avatar_size='original'}" alt="{$user->full_name}" class="img-circle"></th>
                        </tr>
                        <tr>
                            <td class="avatar-before">Before</td>
                            <td class="avatar-after">After</td>
                        </tr>
                    </table>
                </div>
                {/if}

            </div>
        </div>
</div>
{assign var="bio_before" value=""}
{assign var="bio_after" value=""}
{assign var="avatar_before" value=""}
{assign var="avatar_after" value=""}
{/if}

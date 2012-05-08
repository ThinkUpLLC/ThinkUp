<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}inverse{/if}"><i class="icon-white icon-{if $i->emphasis eq '1'}time{elseif $i->emphasis eq '2'}thumbs-up{elseif $i->emphasis eq '3'}warning-sign{else}star{/if}"></i> {$i->prefix}</span> 
                
                {$i->text}

<table class="table table-condensed">

{foreach from=$i->related_data key=uid item=u name=bar}
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
{/foreach}
</table>
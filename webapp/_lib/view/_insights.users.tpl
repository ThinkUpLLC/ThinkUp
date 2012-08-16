<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}inverse{/if}">{$i->prefix}</span> 
                <i class="icon-{if $i->emphasis eq '1'}time{elseif $i->emphasis eq '2'}thumbs-up{elseif $i->emphasis eq '3'}warning-sign{else}star{/if}"></i>
                {$i->text}

{foreach from=$i->related_data key=uid item=u name=bar}
 <div class="avatar-container" style="float:left;margin:7px; clear : left;">
   <a href="https://twitter.com/intent/user?user_id={$u->user_id}" title="{$u->username} has {$u->follower_count|number_format} followers and {$u->friend_count|number_format} friends"><img src="{$u->avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u->network}/assets/img/favicon.png" class="service-icon2"/></a>
 </div>
 <div style="margin-left:60px">
<a href="https://twitter.com/intent/user?user_id={$u->user_id}">{$u->full_name}</a> <small style="color:gray">{$u->follower_count|number_format} followers</small><br>
 <span style="color:gray">{$u->description|link_usernames_to_twitter}</span>
 {$u->url}<br>
  </div>
 <div style="clear:all">&nbsp;</div>
 
 {/foreach}

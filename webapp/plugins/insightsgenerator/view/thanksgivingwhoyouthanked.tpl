{assign var="users" value=$i->related_data.people}

{if isset($users)}
    {if !isset($user_text)}{assign var="user_text" value=null}{/if}
<ul class="body-list user-list {if $users|@count > 2}body-list-show-some{else}body-list-show-all{/if}">
{foreach from=$users key=k item=u name=bar}
<li class="list-item">
    {include file=$tpl_path|cat:"_user.withlink.tpl" user=$u user_text=$user_text link_label="see the tweets" link="https://twitter.com/search?q=thanks%20OR%20thank%20from%3A"|cat:$i->instance->network_username|cat:"%20to%3A"|cat:$u->username|cat:"&src=typd"}
</li>
{/foreach}
</ul>

{if $users|@count > 2}<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$users|@count} people</span> <i class="fa fa-chevron-down icon"></i></button>{/if}

{/if}
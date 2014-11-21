{assign var="users" value=$i->related_data.people}

{if isset($users)}
    {if !isset($user_text)}{assign var="user_text" value=null}{/if}
<ul class="body-list user-list {if $users|@count > 2}body-list-show-some{else}body-list-show-all{/if}">
{foreach from=$users key=k item=u name=bar}
<li class="list-item">
    {if $i->instance->network eq "twitter"}
        {assign var="link_url" value="https://twitter.com/search?q=thanks%20OR%20thank%20to%3A"|cat:$i->instance->network_username|cat:"%20from%3A"|cat:$u->username|cat:"&src=typd"}
        {assign var="link_label" value="see the tweets"}
    {else}
        {assign var="link_url" value="https://www.facebook.com/search/"|cat:$u->user_id|cat:"/stories-commented/me/stories-by/2014/date/stories/intersect"}
        {assign var="link_label" value="see the posts"}
    {/if}
    {include file=$tpl_path|cat:"_user.withlink.tpl" user=$u user_text=$user_text link_label=$link_label link=$link_url}

</li>
{/foreach}
</ul>

{if $users|@count > 2}<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$users|@count} people</span> <i class="fa fa-chevron-down icon"></i></button>{/if}

{/if}
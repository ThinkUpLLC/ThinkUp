{*
Renders an insight with an array of user objects in related_data.

Parameters:
$i (required) Insight object
$icon (required) Icon glyph name, from http://twitter.github.com/bootstrap/base-css.html#icons
*}

<!--

PRINT_R USERS

  {$i->related_data.people|@print_r}

-->


<ul class="body-list user-list {if $i->related_data.people|@count > 2}body-list-show-some{else}body-list-show-all{/if}">

{foreach from=$i->related_data.people key=k item=u name=bar}
<li class="list-item">
    <div class="user ">
        <a href="{if $u->network eq 'twitter'}https://twitter.com/intent/user?user_id={$u->user_id}{/if}">
            <img src="{$u->avatar}" alt="{$u->full_name}" class="img-circle pull-left user-photo">
            <div class="user-about">
                <div class="user-name">{$u->full_name} <i class="fa fa-{$u->network} icon icon-network"></i></div>
                <div class="user-text">
                    {if $u->network eq 'twitter'}
                    {$u->follower_count|number_format} followers
                    {else}
                    {$u->other.total_likes|number_format} likes
                    {/if}
                </div>
            </div>
        </a>
    </div>
</li>

{/foreach}

</ul>

{if $i->related_data.people|@count > 2}<button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all {$i->related_data.people|@count} people</span> <i class="fa fa-chevron-down icon"></i></button>{/if}
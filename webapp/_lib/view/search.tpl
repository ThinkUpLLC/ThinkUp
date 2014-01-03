{include file="_header.tpl"}
{include file="_statusbar.tpl"}

    <div id="main" class="container">

{include file="_usermessage.tpl"}

<div class="row">
    <div class="col-md-3">
      <div class="embossed-block">
        <ul>
          <li>
{if $current_page eq 1}First {$posts|@count} s{else}S{/if}earch results for "{$smarty.get.q}"
          </li>
        </ul>
      </div>
    </div><!--/col-md-3-->

    <div class="col-md-9">
    {if $smarty.get.c eq 'posts'}
        {if $posts|@count > 0}
        {foreach from=$posts key=pid item=post name=bar}
            <div class="alert insight-item">
            {include file=$tpl_path|cat:"_post.tpl" post=$post hide_insight_header='1'}
            {include file=$tpl_path|cat:'_footer.tpl'}
        {/foreach}
        {else}
         <h2>No posts found.</h2>
        {/if}
    {/if}
    {if $smarty.get.c eq 'searches'}
        {if $posts|@count > 0}
        {foreach from=$posts key=pid item=post name=bar}
            <div class="alert insight-item">
            {include file=$tpl_path|cat:"_post.tpl" post=$post hide_insight_header='1'}
            {include file=$tpl_path|cat:'_footer.tpl'}
        {/foreach}
        {else}
         <h2>No posts found.</h2>
        {/if}
    {/if}
    {if $smarty.get.c eq 'followers'}
        {if $users|@count > 0}
        {foreach from=$users key=uid item=u name=bar}
            <div class="alert insight-item">
                <table class="table table-condensed">
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
                            <h3><i class="fa fa-{$u->network}"></i> <a href="https://twitter.com/intent/user?user_id={$u->user_id}">{$u->full_name}</a>     <small>{$u->follower_count|number_format} followers</small></h3>
                            <p>{$u->description|link_usernames_to_twitter}<br />
                            {$u->url}</p>
                        {else}
                            <h3><i class="fa fa-{$u->network}"></i> {$u->full_name}    {if $u->other.total_likes}<small style="color:gray">{$u->other.total_likes|number_format} likes</small>{/if}</h3>
                        {/if}
                    </td>
                    </tr>
                </table>
            </div>
        {/foreach}
        {else}
         <h2>No followers found.</h2>
        {/if}
    {/if}
    </div><!--/col-md-9-->
</div><!--/row-->

<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-9">

        <ul class="pager">
        {if $next_page}
          <li class="previous">
            <a href="{$site_root_path}search.php?{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.c}c={$smarty.get.c|urlencode}&{/if}{if $smarty.get.k}k={$smarty.get.k|urlencode}&{/if}{if $smarty.get.q}q={$smarty.get.q|urlencode}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="fa fa-arrow-left"></i> Older</a>
          </li>
        {/if}
        {if $last_page}
          <li class="next">
            <a href="{$site_root_path}search.php?{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.c}c={$smarty.get.c|urlencode}&{/if}{if $smarty.get.k}k={$smarty.get.k|urlencode}&{/if}{if $smarty.get.q}q={$smarty.get.q|urlencode}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="fa fa-arrow-right"></i></a>
          </li>
        {/if}
        </ul>

    </div>
</div>


{include file="_footer.tpl" linkify=1}

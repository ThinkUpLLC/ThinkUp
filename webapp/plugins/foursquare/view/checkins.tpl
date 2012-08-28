<style type="text/css">
{literal}
.map-image-container { width: 130px; height: 130px; padding-bottom : 30px; }
img.map-image2 {float:left;margin:6px 0 0 0;width:150px;height:150px;}
img.place-icon2 {position: relative;width: 32px;height: 32px;top: -146px;left: 5px;}
{/literal}
</style>

{if $all_checkins|@count >0}
    <div class="section">
        <h2>Your Checkins</h2>
        {foreach from=$all_checkins item=post name=foo}
            {include file="_post.checkin.tpl"}
        {/foreach}
        <div class="view-all" id="older-posts-div">
        {if $next_page}
            <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}"id="next_page">&#60; Older</a>
        {/if}
        {if $last_page}
            | <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}"id="last_page">Newer &#62;</a>
        {/if}
        </div>
    </div>
{else}
    <div class="alert urgent">
        No posts to display. {if $logged_in_user}Update your data and try again.{/if}
    </div>
{/if}


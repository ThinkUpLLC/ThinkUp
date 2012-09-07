<style type="text/css">
{literal}
.map-image-container { width: 130px; height: 130px; padding-bottom : 30px; }
img.map-image2 {float:left;margin:6px 0 0 0;width:150px;height:150px;}
img.place-icon2 {position: relative;width: 32px;height: 32px;top: -146px;left: 5px;}
{/literal}
</style>

{if $all_checkins|@count >0}
    <div class="section">
        <div class="clearfix">
          {insert name="help_link" id=$display}
          <h2>{if $parent_name}<a href="?v={$parent}&u={$instance->network_username}&n=twitter">{$parent_name}</a> &rarr; {/if}{$header}</h2>
          {if $description}<h3>{$description}</h3>{/if}
        </div>
        <div class="header">
        {if $logged_in_user and $display eq 'posts'}<a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network}">Export</a>{/if}
        </div>

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


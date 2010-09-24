<div class="float-r">
    {if $next_page}
        <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
    {/if}
    {if $last_page}
        | <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
    {/if}
</div>

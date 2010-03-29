<div class="float-r">
    {if $prev_page}<a href="{$cfg->site_root_path}public.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}page={$prev_page}" id="prev_page">&lt; Prev Page</a>
    {/if} 
    {if $prev_page or $next_page} 
    Page {$current_page} of {$total_pages} 
    {/if}
    {if $next_page}<a href="{$cfg->site_root_path}public.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}page={$next_page}" id="next_page">Next Page &gt;</a>
    {/if}
</div>

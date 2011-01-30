<div id="grid_search_template">
<div id="grid_overlay_div" class="grid_overlay_div{if $version2}2{/if}">
<script type="text/javascript">
    GRID_TYPE={if $version2}2{else}1{/if};
</script>
<iframe class="grid_iframe{if $version2}2{/if}" id="grid_iframe" src="{$site_root_path}assets/img/ui-bg_glass_65_ffffff_1x400.png" 
frameborder="0" scrolling="no"></iframe>
<div id="close_grid_search_div"><a href="#" id="close_grid_search" onclick="return false;"><img src="{$site_root_path}assets/img/close-icon.gif" /></a></div>
</div>
</div>
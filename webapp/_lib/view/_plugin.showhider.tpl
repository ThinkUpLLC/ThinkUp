<script type="text/javascript">
{literal}
var settings_visible = {/literal}{if $is_configured}true{else}false{/if}{literal};
{/literal}
</script>

{if $is_configured}
<p>
    <a href="#" onclick="show_settings(); return false" class="btn btn-small"><i id="settings-icon" class="fa fa-chevron-down"></i> <span id="settings-flip-prompt">Show</span> Settings</a>
</p>
{/if}
<div class="plugin-settings">
<h2 class="subhead">Settings</h2>

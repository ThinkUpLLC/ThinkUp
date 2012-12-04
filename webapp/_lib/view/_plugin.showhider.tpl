<script type="text/javascript">
{literal}
var settings_visible = {/literal}{if $is_configured}true{else}false{/if}{literal};
function show_settings() {
    if (settings_visible) {
        $(".plugin-settings").hide();
        $('#settings-flip-prompt').html('Show');
        settings_visible = false;
        $("#settings-icon").removeClass('icon-chevron-up').addClass('icon-chevron-down');
    } else {
        $(".plugin-settings").show();
        $('#settings-flip-prompt').html('Hide');
        settings_visible = true;
        $("#settings-icon").removeClass('icon-chevron-down').addClass('icon-chevron-up');
    }
}
  $(document).ready(function() {
      show_settings();
    });
{/literal}
</script>

{if $is_configured}
<p>
    <a href="#" onclick="show_settings(); return false" class="btn btn-small"><i id="settings-icon" class="icon-chevron-down"></i> <span id="settings-flip-prompt">Show</span> Settings</a>
</p>
{/if}
<div class="plugin-settings">
<h2 class="subhead">Settings</h2>

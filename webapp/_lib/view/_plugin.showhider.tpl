<script type="text/javascript">
{literal}
var settings_visible = {/literal}{if $is_configured}true{else}false{/if}{literal};
function show_settings() {
    if (settings_visible) {
        $(".plugin-settings").hide();
        $('#settings-flip-prompt').html('Show');
        settings_visible = false;
        $("#settings-icon").attr("src", site_root + "assets/img/slickgrid/actions.gif");
    } else {
        $(".plugin-settings").show();
        $('#settings-flip-prompt').html('Hide');
        settings_visible = true;
        $("#settings-icon").attr("src", site_root + "assets/img/slickgrid/actions_reverse.jpg");
    }
}
  $(document).ready(function() {
      show_settings();
    });
{/literal}
</script>

<p>
    <a href="#" onclick="show_settings(); return false"><img id="settings-icon" src="{$site_root_path}assets/img/slickgrid/actions.gif" /> <span id="settings-flip-prompt">Show</span> Settings</a>
<br><br>
</p>

<div class="plugin-settings">
<h2 class="subhead">Settings</h2>

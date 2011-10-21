{include file="_header.tpl"}
{include file="_statusbar.tpl"}
<div class="container_24 thinkup-canvas round-all">
  <div class="prepend_20">
    <h1>Upgrade ThinkUp</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
    {include file="_usermessage.tpl"}
    </div>
  </div>

    {if $high_table_row_count}
    <!-- too many db records, use CLI interface? -->
    <div id="info-parent" class="ui-state-highlight ui-corner-all" style="margin: 0px 50px 0px 50px; padding: 0.5em 0.7em;">
        <div id="migration-info">
           <p>
            <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
            The table <b>{$high_table_row_count.table}</b> has a row count of 
            <b>{$high_table_row_count.count|number_format:0:".":","}</b>.
            We recommend that you use the <a href="http://thinkupapp.com/docs/install/upgrade.html">
            <b>Command Line Upgrade Tool</b></a> when upgrading Thinkup.
            </p>
        </div>
    </div>
    <br />
    {/if}

    {if ! $migrations[0]}
    <!-- no upgrade needed -->
     <div class="ui-state-success ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           Your database is up to date. <a href="{$site_root_path}">Continue using ThinkUp</a>, or <a href="backup.php">back up your database.</a>
                {if $version_updated}
                <p>Your application database version has been updated to reflect the latest installed version of ThinkUp.</p>
                {/if}
        </p>
     </div> 
    {else}
    <div id="info-parent" class="ui-state-highlight ui-corner-all" style="margin: 0px 50px 0px 50px; padding: 0.5em 0.7em;">
        <div id="migration-info">
        <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        Your ThinkUp installation needs {$migrations|@count} database update{if $migrations|@count gt 1}s{/if}. {if $user_is_admin}Before you proceed, 
        <a href="{$site_root_path}install/backup.php">back up your current ThinkUp database</a>.{else}<br />If you haven't already, <a href="http://thinkupapp.com/docs/install/backup.html">back up your current installation's data first</a>.{/if}
        </p>
        </div>
        <script type="text/javascript">
        var sql_array = {$migrations_json};
        </script>
    </div>
    {/if}
    
    {if $migrations[0]}
    <div class="clearfix">
    <br /><br />
    <div class="grid_10 prefix_9 left">
        <form name="upgrade" method="get" action="" id="upgrade-form" onsubmit="return false;">
        <input id="migration-submit" 
        name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" 
        value="Update ThinkUp's Database" type="submit" style="font-size:24px;line-height:2.2em;">
        </form>
        </div>
     </div>
     
     <div id="upgrade-error" class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em; display: none;">
     Error
     </div>

     <div id="migration-status-details" style="margin: 20px; display: none;"><p><a href="javascript:jchange('migration-status');">Show update details:</a></p></div>
     {literal}
<script language="javascript" type="text/javascript">
function jchange(o) {
if(document.getElementById(o).style.display=='none') {
document.getElementById(o).style.display='block';
 } 
}
</script>
{/literal}
     
     <div style="text-align:center; height: 31px;">
        <img src="{$site_root_path}assets/img/loading.gif" style="display: none;" 
        id="migrate_spinner" width="31" height="31">
     </div>
     
     <div id="migration-status" style="margin: 20px; display: none;">
     </div>
    {/if}

<br />&nbsp;<br />
    
</div>

{if $upgrade_token}
<script type="text/javascript">
var upgrade_token = '{$upgrade_token}';
</script>
{/if}
<script type="text/javascript" src="{$site_root_path}assets/js/upgrade.js"></script>


{include file="_footer.tpl"}

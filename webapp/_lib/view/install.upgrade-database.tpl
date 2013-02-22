{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas round-all clearfix" style="margin-top : 10px;">

    <div class="grid_18" style="margin-bottom : 20px; margin-left : 100px;">
        {include file="_usermessage.tpl"}
    </div>

  <div class="prepend_20">
    <h1>Upgrade ThinkUp's Database</h1>
  </div>

    {if $high_table_row_count}
    <!-- too many db records, use CLI interface? -->
    <div id="info-parent" class="alert urgent" style="margin: 0px 50px 0px 50px; padding: 0.5em 0.7em;">
        <div id="migration-info">
           <p>
            <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
            Wow, your database has grown! The <b>{$high_table_row_count.table}</b> table  has <b>{$high_table_row_count.count|number_format:0:".":","} rows</b>.
            Since upgrading a large database can time out in the browser, we recommend that you use the <a href="http://thinkup.com/docs/install/upgrade.html">
            <b>command line upgrade tool</b></a> when upgrading ThinkUp.
            </p>
        </div>
    </div>
    <br />
    {/if}

    {if ! $migrations[0]}
    <!-- no upgrade needed -->
     <div class="alert helpful" style="margin: 20px 0px; padding: 0.5em 0.7em;">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           Sweet! Your database is up to date. {if $thinkup_db_version}Here's <a href="http://thinkup.com/docs/changelog/{$thinkup_db_version}.html" target="_new">what's new in version <b>{$thinkup_db_version}</b></a>.{/if}
        </p>
     </div> 
     <br>
    <div>
      <p><a href="{$site_root_path}" class="linkbutton emphasized">Start using ThinkUp</a></p>
    </div>
     
    {else}
    <div id="info-parent" class="alert urgent" style="margin: 0px 50px 0px 50px; padding: 0.5em 0.7em;">
        <div id="migration-info">
        <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin: 0.3em 0.3em 0pt 0pt;"></span>
        Your ThinkUp installation needs {$migrations|@count} database update{if $migrations|@count gt 1}s{/if}. {if $user_is_admin}Before you proceed, 
        <a href="{$site_root_path}install/backup.php">back up your current ThinkUp database</a>.{else}<br />If you haven't already, <a href="http://thinkup.com/docs/install/backup.html">back up your current installation's data first</a>.{/if}
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
        name="Submit" class="linkbutton emphasized" 
        value="Update ThinkUp's Database" type="submit" style="font-size:24px;line-height:2.2em;">
        </form>
        </div>
     </div>
     
     <div id="upgrade-error" class="alert urgent" style="margin: 20px 0px; padding: 0.5em 0.7em; display: none;">
     Error
     </div>

     <div id="migration-status-details" style="margin: 20px; display: none;"><p><a href="javascript:jchange('migration-status');" class="linkbutton">Show update details:</a></p></div>
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
        id="migrate_spinner" width="50" height="50">
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
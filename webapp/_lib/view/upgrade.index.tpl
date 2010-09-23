{include file="_header.tpl"}
{include file="_statusbar.tpl"}
<div class="container_24 thinkup-canvas round-all">
  <div class="prepend_20">
    <h1>Upgrade</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
    {include file="_usermessage.tpl"}
    </div>
  </div>

    <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: 0.5em 0.7em;">
    {if ! $migrations[0]}
    
    <!-- no upgrade needed -->
    <p>Your database is up to date. There is no upgrade needed.</p>
    {else}
        <div style="text-align: center;" id="migration-info">
        There {if $migrations|@count gt 1}are{else}is{/if}
        {$migrations|@count} database migration{if $migrations|@count gt 1}s{/if} to run
        </div>
        <script type="text/javascript">
        var sql_array = {$migrations_json};
        </script>
    {/if}
    </div>
    {if $migrations[0]}
    <div class="clearfix">
        <div class="grid_10 prefix_9 left">
        <form name="upgrade" method="get" action="" id="upgrade-form" onsubmit="return false;">
        <input id="migration-submit" 
        name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" 
        value="Run Migrations" type="submit" style="font-size:24px;line-height:2.2em;">
        </form>
        </div>
     </div>
     
     <div id="upgrade-error" class="ui-state-error ui-corner-all" 
     style="margin: 20px 0px; padding: 0.5em 0.7em; display: none;">
     Error...
     </div>
     <div style="text-align:center; height: 31px;">
        <img src="{$site_root_path}assets/img/loading.gif" style="display: none;" 
        id="migrate_spinner" width="31" height="31">
     </div>
     <div id="migration-status" style="margin: 20px;">
     </div>
    {/if}

<br />&nbsp;<br />
    
</div>

<script type="text/javascript" src="{$site_root_path}assets/js/upgrade.js"></script>


{include file="_footer.tpl"}

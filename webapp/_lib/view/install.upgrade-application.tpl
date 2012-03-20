{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas round-all clearfix" style="margin-top : 10px;">
    
   <div class="prepend_20">
    <h1>Upgrade Your ThinkUp Application</h1>
  </div>
    
    <div class="clearfix prepend_20 append_20">

    {include file="_usermessage.tpl"}

    {if $show_try_again_button}
    <br>
    <div>
        <a href="upgrade-application.php" class="linkbutton emphasized">Try Again</a></div><br><br>
    </div>
    {/if}
    {if $updateable} 
     <div class="alert helpful">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           Ready to upgrade ThinkUp to version {$latest_version}.
         </p>
    </div>
    <br>
    <div>
        <p>
        <a href="{$site_root_path}install/upgrade-application.php?run_update=1" onclick="$('#update-spinner').show();" class="linkbutton emphasized">Upgrade ThinkUp</a>
        </p>
        <p id="update-spinner" style="text-align: center; display: none;">
            <img src="{$site_root_path}assets/img/loading.gif" width="50" height="50" />
        </p>
    </div>
    {/if}
    {if $updated}
     <div class="alert helpful">
         <p>
           <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
           Success! You're running the latest version of ThinkUp.
         </p>
     </div>
     <br>
        <div>
            <p><a href="{$site_root_path}install/upgrade-database.php" class="linkbutton emphasized">Upgrade ThinkUp's database</a></p>
        </div>
    {/if}
    </div>
    </div>
</div>

{include file="_footer.tpl"}

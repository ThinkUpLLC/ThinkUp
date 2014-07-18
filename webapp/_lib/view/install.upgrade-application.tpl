{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

<div id="main" class="container">


            {include file="_usermessage.tpl"}

                <header>
                    <h1>Upgrade Your ThinkUp Application</h1>
                </header>

                    {if $show_try_again_button}
                    <br>
                    <div>
                        <a href="upgrade-application.php" class="btn btn-primary btn-large">Try Again</a></div><br><br>
                    </div>
                    {/if}
                    {if $updateable} 
                     <div class="alert helpful">
                         <p>
                           <span class="fa fa-check"></span>
                           Ready to upgrade ThinkUp to version {$latest_version}.
                         </p>
                    </div>
                    <br>
                    <div>
                        <p>
                        <a href="{$site_root_path}install/upgrade-application.php?run_update=1" onclick="$('#update-spinner').show();" class="btn btn-primary btn-large">Upgrade ThinkUp</a>
                        </p>
                        <p id="update-spinner" style="text-align: center; display: none;">
                            <img src="{$site_root_path}assets/img/loading.gif" width="50" height="50" />
                        </p>
                    </div>
                    {/if}
                    {if $updated}
                     <div class="alert helpful">
                         <p>
                           <span class="fa fa-check"></span>
                           Success! You're running the latest version of ThinkUp.
                         </p>
                     </div>
                     <br>
                        <div>
                            <p><a href="{$site_root_path}install/upgrade-database.php" class="btn btn-primary btn-large">Upgrade ThinkUp's database</a></p>
                        </div>
                    {/if}
                    </div>




{include file="_footer.tpl"}

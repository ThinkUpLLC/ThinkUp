{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<!--  we are upgrading -->
<div id="main" class="container">

    <div class="row">
        <div class="col-md-3">
            
        </div>
        <div class="col-md-9">

            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">ThinkUp's database needs an upgrade.</h3>
                </div>
                <div class="panel-body">
                    
                    {if $user_is_admin}
                    <h3><a href="{$site_root_path}install/upgrade-database.php" class="btn btn-primary btn-large" style="font-weight: 800;">Begin upgrade now</a></h3>
                    
                    {else}

                    <p>ThinkUp is currently in the process of upgrading. Please try back again in a little while.</p>
                    <p>If you are the administrator of this ThinkUp installation, check your email to complete the upgrade process.
                    (<a href="http://thinkup.com/docs/troubleshoot/messages/upgrading.html">What? Help!</a>)</p>

                    <p>
                        <form method="get" action="{$site_root_path}install/upgrade-database.php">
                        <p>If you have an
                        <a href="http://thinkup.com/docs/troubleshoot/messages/upgrading.html">
                        upgrade token</a>, you can enter it here:
                        <input type="text" name="upgrade_token" class="form-control" />
                        <input type="submit" value="Submit Token" class="form-control" />
                        </form>
                    </p>
                    
                    {/if}
                </div>
            </div>

        </div>
    </div>

        
</div>
  

  
{include file="_footer.tpl"}

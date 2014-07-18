{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

<div id="main" class="container">


    <header>
        <h1>Error creating config file</h1>
    </header>
               

    <form class="input form-horizontal" name="form1" method="post" action="index.php?step=3">

        {include file="_usermessage.tpl"}

        <fieldset>

             <div class="form-group">
                    <span class="help-block">
                    If you need to manually create your config.inc.php file, or want to inspect its contents, you can view 
                    the config file that ThinkUp has generated for you.</span>
            </div>
            
        </fieldset>

                <header>
                        <h1><a class="btn " data-toggle="collapse" data-target="#config-inc-setup">Show config.inc.php <i class="fa fa-chevron-down icon-white"></i></a></h1>
                </header>


        <fieldset class="in collapse" id="config-inc-setup" style="height: auto;">
                
                        <textarea style="width: 100%; font-face: monospace; font-size: smaller;" rows="15">{$config_file_contents}</textarea>  
                    {foreach from=$_POST key=k item=v}
                       <input type="hidden" name="{$k}" value="{$v}" />
                    {/foreach}                        
    
        </fieldset>

        <input type="submit" name="Submit" class="btn btn-primary btn-circle btn-submit" value="Save Config">

    </form>
        
</div>
  
{include file="_footer.tpl"}

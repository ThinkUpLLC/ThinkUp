{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}

<div id="main" class="container">

    <div class="navbar">
        <div class="navbar-inner">
        <span class="brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav pull-left">
            <li><a> <h4><i class="icon-ok-circle "></i> Check System Requirements</h4></a></li>
            <li class="active"><a class="disabled"> <h4><i class="icon-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="icon-lightbulb"></i> Finish</h4></a></li>
        </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="span3">
            
        </div>
        <div class="span9">

            <form class="input form-horizontal" name="form1" method="post" action="index.php?step=3">

                {include file="_usermessage.tpl" enable_bootstrap=1}

            <fieldset style="padding-bottom : 0px;">

                <legend>Error creating config file</legend>
               
                 <div class="control-group">
                    <label class="control-label">config.inc.php</label>
                    
                    <div class="controls">
                        <span class="help-inline">
                        If you need to manually create your config.inc.php file, or want to inspect its contents, you can view 
                        the config file that ThinkUp has generated for you here.</span>
                        <a class="btn " data-toggle="collapse" data-target="#config-inc-setup" style="margin-top: 12px;">Show config.inc.php <i class="icon-chevron-down icon-white"></i></a>

                        <div class="in collapse" id="config-inc-setup" style="height: auto;">                  
                            <textarea style="width : 90%; margin-bottom : 30px; margin-top: 10px; font-face: monospace; font-size: smaller;" rows="15">{$config_file_contents}</textarea>
                        </div>    
                        {foreach from=$_POST key=k item=v}
                           <input type="hidden" name="{$k}" value="{$v}" />
                        {/foreach}                        
                    </div>
                </div>

                <div class="form-actions">
                    <input type="submit" name="Submit" class="btn btn-primary" value="Save Config">
                </div>
                
            </fieldset>

            </form>

        </div>
    </div>

        
</div>
  
{include file="_footer.tpl" enable_bootstrap=1}

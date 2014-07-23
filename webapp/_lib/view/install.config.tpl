{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div id="main" class="container">

    <div class="navbar">
        <div class="navbar-inner">
        <span class="brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav pull-left">
            <li><a> <h4><i class="fa fa-check "></i> Check System Requirements</h4></a></li>
            <li class="active"><a class="disabled"> <h4><i class="fa fa-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="fa fa-lightbulb"></i> Finish</h4></a></li>
        </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            
        </div>
        <div class="col-md-9">

            <form class="input form-horizontal" name="form1" method="post" action="index.php?step=3">

                {include file="_usermessage.tpl"}

            <fieldset style="padding-bottom : 0px;">

                <legend>Error creating config file</legend>
               
                 <div class="form-group">
                    <label class="col-sm-2">config.inc.php</label>
                    
                    <div class="col-sm-8">
                        <span class="help-block">
                        If you need to manually create your config.inc.php file, or want to inspect its contents, you can view 
                        the config file that ThinkUp has generated for you here.</span>
                        <a class="btn " data-toggle="collapse" data-target="#config-inc-setup" style="margin-top: 12px;">Show config.inc.php <i class="fa fa-chevron-down icon-white"></i></a>

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
  
{include file="_footer.tpl"}

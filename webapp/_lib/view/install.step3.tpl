{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div id="main" class="container">

    <div class="navbar">
        <span class="navbar-brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav navbar-nav nav-pills pull-left">
            <li><a> <h4><i class="fa fa-check "></i> Check System Requirements</h4></a></li>
            <li><a class="disabled"> <h4><i class="fa fa-check"></i> Configure ThinkUp</h4></a></li>
            <li class="active"><a class="disabled"> <h4><i class="fa fa-lightbulb"></i> Finish</h4></a></li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="embossed-block">
                <ul>
                    <li>Congratulations!</li>
                </ul>
            </div>            
        </div>
        <div class="col-md-9">
            <div class="alert alert-success">
                <div><i class="fa fa-check"></i> ThinkUp has been installed successfully. Check your email account; an account activation message has been sent.</div>
                
                <a href="{$site_root_path}session/login.php" class="btn btn-success btn-lg" style="margin-top: 16px; clear: left;" ><i class="fa fa-signin icon-white"></i> Log In</a>
            </div>

        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div>&nbsp;

            </div>            
        </div>
        <div class="col-md-9">
            <div class="alert alert-info insight-item">


            <div class="insight-attachment-detail none">
        
                    <i class="fa fa-envelope icon-muted"></i>
                    <a href="http://thinkup.com/docs/troubleshoot/common/emaildisabled.html">Didn't get the email?</a>
            </div>

            </div>

        </div>
    </div>

        
</div>
  
{include file="_footer.tpl"}
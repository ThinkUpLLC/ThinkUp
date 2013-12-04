{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container">

<div class="row">
    <div class="col-md-3">
          &nbsp;
    </div><!--/col-md-3-->
    <div class="col-md-9">


            {include file="_usermessage.tpl"}
            
            <div class="panel panel-default">

            <form name="login-form" method="post" action="{$site_root_path}session/login.php" class="login form-horizontal">

            <fieldset>
            <legend class="panel-heading">Welcome to ThinkUp!</legend>
            
                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-sm-2" for="email">Email</label>
                        <div class="col-sm-8 input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input class="form-control" type="email" name="email" id="email" {if isset($email)} value="{$email|filter_xss}"{/if} autofocus="autofocus">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2" for="pwd">Password</label>
                        <div class="col-sm-8 input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input class="form-control" type="password" name="pwd" id="pwd">
                        </div>
                    </div>

                    <div class="form-group form-actions">
                        <label class="col-sm-2"></label>
                        <div class="col-sm-8">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Log In">
                            <span class="pull-right">
                                <div class="btn-group">
                                    {if $is_registration_open}<a href="{$site_root_path}session/register.php" class="btn btn-xs hidden-phone">Register</a>{else}{/if}
                                    <a href="forgot.php" class="btn btn-xs">Forgot password</a>
                                    {insert name="help_link" id='register'}
                                </div>
                            </span>
                        </div>
                        
                    </div>

                </div>

            </fieldset>


            </form>
        </div><!-- /panel -->

    </div>
</div>

{include file="_footer.tpl"}

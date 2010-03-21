{include file="_header.tpl" title="Sign In" statusbar="no"}

<div class="container_24 thinktank-canvas round-all center">

    <div class="clearfix prepend_20">
        <div class="grid_17 prefix_3 left">
    	{if isset($errormsg)}<div class="error"> {$errormsg} </div>{/if}
    	{if isset($successmsg)}<div class="success"> {$successmsg} </div>{/if}
        </div>
    </div>
    
    <form name="form1" method="post" action="" class="login">
    
    <div class="clearfix">
        <div class="grid_4 prefix_5 right"><label>Email:</label></div>
        <div class="grid_10 left"><input name="email" type="text" id="email"></div>
    </div>
    
    <div class="clearfix">
        <div class="grid_4 prefix_5 right"><label>Password:</label></div>
        <div class="grid_10 left"><input name="pwd" type="password" id="pwd"></div>
    </div>
    
    <div class="clearfix">
        <div class="grid_10 prefix_9 left">
            <input type="submit" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Login">
        </div>
    </div>
    
    <div class="center prepend_20 append_20">
        <a href="register.php">Register</a> | <a href="forgot.php">Forgot password</a>
    </div>

    
    </form>

</div>

{include file="_footer.tpl" stats="no"}
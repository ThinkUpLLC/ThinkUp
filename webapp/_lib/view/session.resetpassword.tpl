{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas round-all clearfix" style="margin-top : 30px;">
      {if isset($error_msg)}
        <div class="grid_18 alert urgent" style="margin-bottom : 20px; margin-left : 100px;">
          {$error_msg}
        </div>
      {/if}
      {if isset($success_msg)}
        <div class="grid_18 alert helpful" style="margin-bottom : 20px; margin-left : 100px;">
             <p>
               <span class="ui-icon ui-icon-check" style="float: left; margin:.3em 0.3em 0 0;"></span>
               {$success_msg}
             </p>
         </div> 
      {/if}

{if !isset($error_msg) && !isset($success_msg)}
<div class="grid_18 section" style="margin-bottom : 100px; margin-left : 100px;">
    {insert name="help_link" id='reset'}

    <h2>Reset Your Password</h2>

    <div class="article">
    <form name="form1" method="post" action="" class="login append_20">
      <div class="clearfix">
        <div class="grid_6 prefix_2 right">
            <label for="password">
              New password:
            </label>
        </div>
        <div class="grid_8 left">
            <input type="password" name="password" id="password">
        </div>
      </div>
      
    <div class="clearfix">
      <div class="grid_6 prefix_2 right">
        <label for="password_confirm">
          Retype password:
        </label>
      </div>
      <div class="grid_8 left">
        <input type="password" name="password_confirm" id="password_confirm">
      </div>
    </div>
      <div class="clearfix">
        <div class="grid_10 prefix_9 left">
          <input type="submit" id="login-save" name="Submit" class="linkbutton emphasized" value="Submit">
        </div>
      </div>
    </form>
    </div>
    <div class="view-all">
      <a href="register.php">Register</a> |
      <a href="login.php">Log In</a>
    </div>
</div>
</div>
{/if}

{include file="_footer.tpl"}

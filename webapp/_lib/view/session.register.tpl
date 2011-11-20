{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas clearfix round-all" style="margin-top : 30px;">

    <div class="grid_18 section" style="margin-bottom : 100px; margin-left : 100px;">
        {insert name="help_link" id='register'}
        <h2>Register</h2>
        
        <div class="article">
        
        <div style="margin-right : 20px;">
            {include file="_usermessage.tpl"}<br>
        </div>
        
        {if !$closed and !$has_been_registered}
        <form name="form1" method="post" id="registerform" action="register.php{if $invite_code}?code={$invite_code|filter_xss}{/if}" class="login append_20">
          <div class="clearfix">
            <div class="grid_4 prefix_2 right">
              <label for="full_name">
                Name:
              </label>
            </div>
            <div class="grid_10 left">
              <input name="full_name" type="text" id="full_name"{if  isset($name)} value="{$name|filter_xss}"{/if}>
              <small>
                <br>
                Example: Angelina Jolie
              </small>
            </div>
          </div>
          <div class="clearfix">
            <div class="grid_9 prefix_6 left">
              {include file="_usermessage.tpl" field="email"}
            </div>
            <div class="grid_4 prefix_2 right">
              <label for="email">
                Email:
              </label>
            </div>
            <div class="grid_10 left">
              <input name="email" type="text" id="email"{if  isset($mail)} value="{$mail|filter_xss}"{/if}>
              <small>
                <br>
                Example: angie@example.com
              </small>
            </div>
          </div>
          <div class="clearfix">
            <div class="grid_9 prefix_6 left">
                {include file="_usermessage.tpl" field="password"}
            </div>
            <div class="grid_4 prefix_2 right">
              <label for="pass1">
                Password:
              </label>
            </div>
            <div class="grid_10 left">
              <input name="pass1" type="password" id="pass1" class="password" onfocus="$('#password-meter').show();">
                <div class="password-meter" style="display:none;" id="password-meter">
                    <div class="password-meter-message"></div>
                    <div class="password-meter-bg">
                        <div class="password-meter-bar"></div>
                    </div>
                </div>
            </div>
          </div>
          <div class="clearfix">
            <div class="grid_6 prefix_0 right">
              <label for="pass2">
                Retype password:
              </label>
            </div>
            <div class="grid_10 left">
              <input name="pass2" type="password" id="pass2" class="password">
              <small>
                <br>
              </small>
            </div>
          </div>
          <div class="clearfix">
            <div class="grid_9 prefix_6 left">
                {include file="_usermessage.tpl" field="captcha"}
            </div>
            <div class="grid_6 prefix_0 right">
              <label for="user_code">
                Prove you&rsquo;re human:
              </label>
            </div>
            <div class="grid_10 left">
              <div class="captcha">
                {$captcha}
              </div>
            </div>
          </div>
          <div class="clearfix">
            <div class="grid_10 prefix_7 left">
              <input type="submit" name="Submit" id="login-save" class="linkbutton emphasized" value="Register">
            </div>
          </div>
        </form>
        {/if}
        
        </div>
        
        <div class="view-all">
            {if !$success_msg}
            <a href="login.php">Log In</a> |
            <a href="forgot.php">Forgot password</a>
            {/if}
        </div>
        
    </div>
</div>

{include file="_footer.tpl"}
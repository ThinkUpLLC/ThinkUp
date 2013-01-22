  {if $linkify neq 'false'}
  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {/if}

{if $enable_bootstrap}

    </div><!-- end container -->

    <div id="sticky-footer-fix-clear"></div>
</div><!-- #sticky-footer-fix-wrapper -->

      <footer>
        <div class="container footer">
          <p class="pull-right"><a href="#">Back to top <i class="icon-chevron-up"></i></a></p>
          <p><a href="http://thinkup.com">ThinkUp{if $thinkup_version} {$thinkup_version}{/if}</a> <span class="hidden-phone hidden-tablet">&#8226; 
          <a href="http://thinkupapp.com/docs/">Documentation</a>  &#8226; 
          <a href="https://groups.google.com/forum/?fromgroups#!forum/thinkupapp">Mailing List</a> &#8226;
          <a href="{$site_root_path}dashboard.php">Old-School Dashboard</a>
          </span>
          
          </p>  
          <p class="hidden-phone hidden-tablet">
          <a href="http://twitter.com/thinkup"><img src="{$site_root_path}assets/img/favicon_twitter.png"></a>
          <a href="http://facebook.com/thinkupapp"><img src="{$site_root_path}assets/img/favicon_facebook.png"></a>
          <a href="http://gplus.to/thinkup"><img src="{$site_root_path}assets/img/favicon_googleplus.png"></a>
          &copy; ThinkUp LLC 2012-2013. It is nice to be nice.
          </p>
        </div>
      </footer>

  <div id="login-modal" class="modal hide">
    <form name="login-form" method="post" action="{$site_root_path}session/login.php" class="login form-horizontal">

      <fieldset>

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3>Log In</h3>
        </div>

        <div class="control-group">
          <label class="control-label" for="email">Email</label>
          <div class="controls">
            <input class="input-xlarge" type="email" name="email" id="email" autofocus="autofocus">
          </div>
        </div>

        <div class="control-group">
          <label class="control-label" for="pwd">Password</label>
          <div class="controls">
            <input class="input-xlarge" type="password" name="pwd" id="pwd">
          </div>
        </div>

        <div class="form-actions">
          <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Log In">
        </div>

        <div class="modal-footer">
          <a href="{$site_root_path}session/register.php" class="btn btn-mini hidden-phone">Register</a>
          <a href="{$site_root_path}session/forgot.php" class="btn btn-mini">Forgot password</a>
          {insert name="help_link" id='login'}
        </div>

      </fieldset>

    </form>
  </div>
  {literal}
    <script type="text/javascript">
      $('.login-link').click(function(e) {
        e.preventDefault();
        $('#login-modal').modal('show');
      });
    </script>
  {/literal}

{else}

  <div class="small center" id="footer">
    <div id="ft" role="contentinfo">
    <div id="" class="">
      <p>
       <a href="http://thinkupapp.com">ThinkUp{if $thinkup_version} {$thinkup_version}{/if}</a> &#8226; 
       <a href="http://thinkupapp.com/docs/">Documentation</a> 
       &#8226; <a href="http://groups.google.com/group/thinkupapp">Mailing List</a> 
       &#8226; <a href="http://webchat.freenode.net/?channels=thinkup">IRC Channel</a><br />
        It is nice to be nice.
        <br /> <br /><a href="http://twitter.com/thinkup"><img src="{$site_root_path}assets/img/favicon_twitter.png"></a>
        <a href="http://facebook.com/thinkupapp"><img src="{$site_root_path}assets/img/favicon_facebook.png"></a>
        <a href="http://gplus.to/thinkup"><img src="{$site_root_path}assets/img/favicon_googleplus.png"></a>
      </p>
    </div>
    </div> <!-- #ft -->

  </div> <!-- .content -->

<div id="screen"></div>

{/if} <!-- end bootstrap loop -->

</body>

</html>

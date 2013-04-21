  {if $linkify neq 0}
  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {/if}

{if $enable_bootstrap eq 1}

    </div><!-- end container -->

    <div id="sticky-footer-fix-clear"></div>
</div><!-- #sticky-footer-fix-wrapper -->

      <footer>
        <div class="container footer">
          <p class="pull-right"><a href="#">Back to top <i class="icon-chevron-up icon-white"></i></a></p>
          <p><a href="http://thinkup.com">ThinkUp{if $thinkup_version} {$thinkup_version}{/if}</a> <span class="hidden-phone hidden-tablet">&#8226; 
          <a href="http://thinkup.com/docs/">Documentation</a>  &#8226; 
          <a href="https://groups.google.com/forum/?fromgroups#!forum/thinkupapp">Mailing List</a> &#8226;
          <a href="{$site_root_path}dashboard.php">Old-School Dashboard</a>
          </span>
          
          </p>  
          <p class="hidden-phone hidden-tablet">
          <a href="http://twitter.com/thinkup"><i class="icon-twitter icon-white"></i></a>
          <a href="http://facebook.com/thinkupapp"><i class="icon-facebook icon-white"></i></a>
          <a href="http://gplus.to/thinkup"><i class="icon-google-plus icon-white"></i></a>
          &copy; ThinkUp LLC 2012-2013. It is nice to be nice.
          </p>
        </div>
      </footer>

{else}

  <div class="small center" id="footer">
    <div id="ft" role="contentinfo">
    <div id="" class="">
      <p>
       <a href="http://thinkup.com">ThinkUp{if $thinkup_version} {$thinkup_version}{/if}</a> &#8226; 
       <a href="http://thinkup.com/docs/">Documentation</a> 
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

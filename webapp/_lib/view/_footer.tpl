  {if $linkify neq 'false'}
  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {/if}


{if $enable_bootstrap}

</div>

      <footer>
        <div class="container footer">
          <p class="pull-right"><a href="#">Back to top <i class="icon-chevron-up"></i></a></p>
          <p><a href="http://thinkup.com/">ThinkUp</a> 2.0&#945; | <a href="http://thinkupapp.com/docs/">Documentation</a> | <a href="https://groups.google.com/forum/?fromgroups#!forum/thinkupapp">Mailing List</a> | <a href="http://twitter.com/thinkup">@thinkup</a></p>
          <p>&copy; ThinkUp LLC 2012. It is nice to be nice.</p>
    	  </div>
      </footer>
      
    


{else}


  <div class="small center" id="footer">
    <div id="ft" role="contentinfo">
    <div id="">
      <p>
       <a href="http://thinkupapp.com">ThinkUp{if $thinkup_version} {$thinkup_version}{/if}</a> &#8226; 
       <a href="http://thinkupapp.com/docs/">Documentation</a> 
       &#8226; <a href="http://groups.google.com/group/thinkupapp">Mailing List</a> 
       &#8226; <a href="http://webchat.freenode.net/?channels=thinkup">IRC Channel</a><br>
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

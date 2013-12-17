  {if $linkify neq 0}
  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {/if}

    </div><!-- end container -->

    <div id="sticky-footer-fix-clear"></div>
</div><!-- #sticky-footer-fix-wrapper -->

      <footer>
        <div class="container footer">
          <p class="pull-right" style="text-align: right;"><a href="#">Back to top <i class="icon-chevron-up icon-white"></i></a><br />
          &copy; ThinkUp LLC 2012-2013</p>
            <p class="hidden-xs"> 
                <a href="http://thinkup.com/docs/">Documentation</a> &#8226; 
                <a href="https://groups.google.com/forum/?fromgroups#!forum/thinkupapp" >Mailing List</a> &#8226; 
                <a href="http://twitter.com/thinkup"><i class="icon-twitter icon-white"></i></a>
                <a href="http://facebook.com/thinkupapp"><i class="icon-facebook icon-white"></i></a>
                <a href="http://gplus.to/thinkup"><i class="icon-google-plus icon-white"></i></a>
            </p>
            <p>
                <a href="http://thinkup.com">ThinkUp</a>
                {if $thinkup_version} {$thinkup_version}{/if} &#8226;
                <a href="https://github.com/ThinkUpLLC/policy">Privacy & stuff</a> &#8226; It is nice to be nice.
            </p>
        </div>
      </footer>

</body>

</html>

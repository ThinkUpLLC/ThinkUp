  <div class="small center" id="footer">
  {if $linkify neq 'false'}
  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {/if}

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
</body>

</html>

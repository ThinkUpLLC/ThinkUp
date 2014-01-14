
    <footer class="footer">
        <div class="footer-container">
            <p class="pull-right" style="text-align: right;"><a href="#">Back to top <i class="fa fa-chevron-up icon-white"></i></a><br />
            &copy; ThinkUp LLC 2012-2013</p>
            <p class="hidden-xs">
                <a href="http://thinkup.com/docs/">Documentation</a> &#8226;
                <a href="https://groups.google.com/forum/?fromgroups#!forum/thinkupapp" >Mailing List</a> &#8226;
                <a href="http://twitter.com/thinkup"><i class="fa fa-twitter icon-white"></i></a>
                <a href="http://facebook.com/thinkupapp"><i class="fa fa-facebook icon-white"></i></a>
                <a href="http://gplus.to/thinkup"><i class="fa fa-google-plus icon-white"></i></a>
            </p>
            <p>
                <a href="http://thinkup.com">ThinkUp</a>
                {if $thinkup_version} {$thinkup_version}{/if} &#8226;
                <a href="https://github.com/ThinkUpLLC/policy">Privacy &amp; stuff</a> &#8226; It is nice to be nice.
            </p>
        </div>
    </footer>

</div><!-- end page-content -->


    {if ($smarty.get.m neq 'manage') and (!isset($smarty.get.p))}<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="{$site_root_path}assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>{/if}

    {literal}
      <script type="text/javascript">

        $(document).ready(function() {

            $(".collapse").collapse();
            $(function () {
                $('#settingsTabs a:first').tab('show');
            })

    {/literal}
        {if $logged_in_user}
    {literal}
            $('#search-keywords').focus(function() {
                $('#search-refine').dropdown();
                if ($('#search-keywords').val()) {
                    $('#search-refine a span.searchterm').text($('#search-keywords').val());
                }
            }).blur(function() {
                $('#search-refine').dropdown();
            });

            $('#search-keywords').keyup(function() {
                $('#search-refine a span.searchterm').text($('#search-keywords').val());
            });
        });

      function searchMe(_baseu) {
        var _mu = $("input#search-keywords").val();
        if (_mu != "null") {
          document.location.href = _baseu + encodeURIComponent(_mu);
        }
      }
    {/literal}
    {else}
    {literal}
        });
    {/literal}
    {/if}
    </script>

    {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
    {/foreach}
    <script src="{$site_root_path}assets/js/vendor/bootstrap.min.js"></script>
    <script src="{$site_root_path}assets/js/vendor/jpanelmenu.js"></script>
    <script src="//platform.twitter.com/widgets.js"></script>
    <script src="{$site_root_path}assets/js/thinkup.js "></script>
    {if $linkify neq 0}
    <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
    {/if}
</body>

</html>

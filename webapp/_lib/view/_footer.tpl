
    <footer class="footer">
        <div class="footer-container">
            <div class="copyright-privacy">
                <div class="copyright">&copy;2014 ThinkUp, LLC</div>
                <a class="privacy" href="https://github.com/ThinkUpLLC/policy">Privacy and stuff</a>
            </div>
            <div class="motto">It is nice to be nice.</div>
            <div class="follow-wrapper">
                <ul class="follow-links">
                    <li class="twitter"><a href="https://twitter.com/thinkup"><i class="fa fa-twitter"></i></a></li>
                    <li class="facebook"><a href="https://facebook.com/thinkupapp"><i class="fa fa-facebook-square"></i></a></li>
                    <li class="google-plus"><a href="https://plus.google.com/109397312975756759279" rel="publisher"><i class="fa fa-google-plus"></i></a></li>
                    <li class="github"><a href="https://github.com/ginatrapani/ThinkUp"><i class="fa fa-github"></i></a></li>
                </ul>
            </div>
        </div>
    </footer>

</div><!-- end page-content -->


    {if ($smarty.get.m neq 'manage') and (!isset($smarty.get.p))}<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="{$site_root_path}assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
    {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
    {/foreach}
    {/if}

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

    <script src="{$site_root_path}assets/js/vendor/bootstrap.min.js"></script>
    <script src="{$site_root_path}assets/js/vendor/jpanelmenu.js"></script>
    <script src="//platform.twitter.com/widgets.js"></script>
    <script src="{$site_root_path}assets/js/thinkup.js "></script>
    {if $linkify neq 0}
    <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
    {/if}

    {literal}<script>
      var _gaq=[['_setAccount','UA-76614-5'],['_trackPageview']];
      (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
      g.src='//www.google-analytics.com/ga.js';
      s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>

    <script type="text/javascript">
    var _sf_async_config={uid:2383,domain:"thinkup.com"};
    (function(){
      function loadChartbeat() {
        window._sf_endpt=(new Date()).getTime();
        var e = document.createElement('script');
        e.setAttribute('language', 'javascript');
        e.setAttribute('type', 'text/javascript');
        e.setAttribute('src',
           (("https:" == document.location.protocol) ? "https://a248.e.akamai.net/chartbeat.download.akamai.com/102508/" : "http://static.chartbeat.com/") +
           "js/chartbeat.js");
        document.body.appendChild(e);
      }
      var oldonload = window.onload;
      window.onload = (typeof window.onload != 'function') ?
         loadChartbeat : function() { oldonload(); loadChartbeat(); };
    })();

    </script>{/literal}

</body>

</html>

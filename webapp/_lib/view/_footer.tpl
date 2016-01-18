{if !isset($share_mode)}

    <footer class="footer">
        <div class="footer-container">
            <div class="copyright-privacy">
                <div class="copyright">&copy;2014-2016 ThinkUp, LLC</div>
                {if isset($thinkupllc_endpoint)}
                <div class="privacy"><a href="https://www.thinkup.com/join/about/privacy.php">Privacy</a> and <a href="https://www.thinkup.com/join/about/terms.php">Terms</a></div>
                {/if}
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


    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="{$site_root_path}assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
    {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
    {/foreach}

    {if isset($include_jstz) and $include_jstz}
    <script type="text/javascript">
    {literal}
    var tz_info = jstz.determine();
    var regionname = tz_info.name().split('/');
    var tz_option_id = '#tz-' + regionname[1];
    if( $('#timezone option[value="' + tz_info.name() + '"]').length > 0) {
        if( $(tz_option_id) ) {
            $('#timezone').val( tz_info.name());
        }
    }
    {/literal}
    </script>
    {/if}
    {literal}
      <script type="text/javascript">

        $(document).ready(function() {

            $(".collapse").collapse();
            $(function () {
                $('#settingsTabs a:first').tab('show');
            })
    {/literal}
        {if !$logged_in_user}
    {literal}
            $('#search-keywords').focus(function() {
                $('#search-refine').dropdown();
            }).blur(function() {
                $('#search-refine').dropdown();
            });
        });
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


    {if isset($thinkupllc_endpoint)}

        {literal}<script type="text/javascript">
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
    {/if}

{else} <!-- in share mode -->
    <script>window.jQuery || document.write('<script src="{$site_root_path}assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <script src="{$site_root_path}assets/js/vendor/jpanelmenu.js"></script>
    <script src="{$site_root_path}assets/js/thinkup.js "></script>
{/if} <!-- /not in share mode -->

{if isset($thinkupllc_endpoint)}

    {literal}<script>
      var _gaq=[['_setAccount','UA-76614-5'],['_trackPageview']];
      (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
      g.src='//www.google-analytics.com/ga.js';
      s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>{/literal}

{/if}

</body>

</html>

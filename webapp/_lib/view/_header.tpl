<!DOCTYPE html>
<html lang="en" itemscope itemtype="http://schema.org/Article">
<head>
    <meta charset="utf-8">
    <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{$site_root_path}assets/img/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$site_root_path}assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$site_root_path}assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$site_root_path}assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{$site_root_path}assets/ico/apple-touch-icon-57-precomposed.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- styles -->
    <script type="text/javascript" src="//use.typekit.net/zpi4jiv.js"></script>
    <script type="text/javascript">{literal}try{Typekit.load();}catch(e){}{/literal}</script>
    <link href="{$site_root_path}assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$site_root_path}assets/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic|' rel='stylesheet' type='text/css'>
    <link href="{$site_root_path}assets/css/thinkup.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="{$site_root_path}assets/js/jquery-1.10.2.min.js"></script>
    <script src="{$site_root_path}assets/js/bootstrap.min.js"></script>
    <script src="{$site_root_path}assets/js/d3.min.js"></script>
 
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


{foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
{/foreach}

{foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
{/foreach}

<script type="text/javascript">
    var site_root_path = '{$site_root_path}';
    {if $logged_in_user}
    var owner_email = '{$logged_in_user}';
    {/if}
    {if $thinkup_api_key}
    var thinkup_api_key = '{$thinkup_api_key}';
    {/if}
</script>

{if $enable_tabs eq 1}
<script type="text/javascript">
    {literal}
      $(function() {
        // Hide all sections
        $('.section').hide();
        // Add event handlers to tab links
        $('#tabs a').click(function(e) {
            $this = $(this);
            $('#tabs li.active').removeClass('active');
            $this.parent().addClass('active');
            // Prevent the default link behavior
            e.preventDefault();
            // Hide all the sections
            $('.section').hide();
            // Show the appropiate section
            $($this.attr('href')).show();
        });
        // Simulate clicking the first tab
        $('#tabs li:first-child a').click();
        // Load the tab if URL has a hash
        if (window.location.hash) {
            $('#tabs a[href="'+window.location.hash+'"]').click();
        }
      });
    {/literal}
</script>
{/if}

  <!-- google chart tools -->
  <!--Load the AJAX API-->
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  <script type="text/javascript" src="{$site_root_path}plugins/twitter/assets/js/widgets.js"></script>
  
  {if $csrf_token}<script type="text/javascript">var csrf_token = '{$csrf_token}';</script>{/if}

{if $post->post_text} 
    <meta itemprop="name" content="{$post->network|ucwords} post by {$post->author_username} on ThinkUp">
    <meta itemprop="description" content="{$post->post_text|strip_tags}">
    <meta itemprop="image" content="http://thinkup.com/assets/img/thinkup-logo_sq.png">
{/if}

</head>
<body class="{if $body_type}{$body_type}{else}insight-stream{/if}">

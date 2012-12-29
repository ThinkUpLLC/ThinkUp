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


{if $enable_bootstrap}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="{$site_root_path}assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$site_root_path}assets/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="{$site_root_path}assets/css/insights.css" rel="stylesheet">
    {foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
    {/foreach} 

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script src="{$site_root_path}assets/js/jquery.js"></script>
    <script src="{$site_root_path}assets/js/bootstrap.js"></script>
    <script type="text/javascript">var site_root_path = '{$site_root_path}';</script>
    <script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/iframe.js"></script>  

  {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
  {/foreach}

    {literal}
      <script type="text/javascript">
    {/literal}
        {if $register_form}
    {literal}
            $.validator.setDefaults({
                errorElement: "span",
                errorClass: "formError",
                wrapper: "span"
            });
        
            $.validator.passwordRating.messages = {
                "similar-to-username": "Too similar to your name",
                "too-short": "Too short",
                "very-weak": "Very weak",
                "weak": "Weak",
                "good": "Good",
                "strong": "Strong"
            };

    {/literal}
        {/if}

    {literal}

        $(document).ready(function() {
            $(".post").hover(
                function() { $(this).children(".metaroll").show(100); },
                function() { $(this).children(".metaroll").hide(); }
            );
            $(".metaroll").hide();
            $(".collapse").collapse();
            $(function () {
                $('#settingsTabs a:first').tab('show');
            })


        });
      </script>
    {/literal}
    
{else} <!-- not bootstrap -->
  
    <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/base.css">
    <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/style.css">
    {foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
    {/foreach}
    <!-- jquery -->
    <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/jquery-ui-1.8.13.css">

    <script type="text/javascript" src="{$site_root_path}assets/js/jquery.min-1.4.js"></script>
    <script type="text/javascript" src="{$site_root_path}assets/js/jquery-ui.min-1.8.js"></script>
  
    {literal}
      <script type="text/javascript">
      $(document).ready(function() {
          $(".post").hover(
            function() { $(this).children(".small").children(".metaroll").show(); },
            function() { $(this).children(".small").children(".metaroll").hide(); }
          );
          $(".metaroll").hide();
        });
      </script>
    {/literal}

    <!-- custom css -->
    {literal}
    <style>
        .line { background:url('{/literal}{$site_root_path}{literal}assets/img/border-line-470.gif') no-repeat center bottom;
            margin: 8px auto;
            height: 1px;
        }
        
    </style>
    {/literal}
  {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
  {/foreach}

{/if}

{if $enable_tabs}
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
  <script type="text/javascript">var site_root_path = '{$site_root_path}';</script>
  {if $csrf_token}<script type="text/javascript">var csrf_token = '{$csrf_token}';</script>{/if}

{if $post->post_text} 
    <meta itemprop="name" content="{$post->network|ucwords} post by {$post->author_username} on ThinkUp">
    <meta itemprop="description" content="{$post->post_text|strip_tags}">
    <meta itemprop="image" content="http://thinkupapp.com/assets/img/thinkup-logo_sq.png">
{/if}

</head>
<body>

{if $enable_bootstrap}
<div id="sticky-footer-fix-wrapper">
{/if}
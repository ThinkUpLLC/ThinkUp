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

    {if isset($smarty.get.p) and $smarty.get.p eq 'twitter' and
    isset($smarty.get.oauth_token) and isset($smarty.get.oauth_verifier)}
    <meta http-equiv="refresh" content="0;url={$site_root_path}account/?p=twitter">
    {/if}
    {if isset($smarty.get.p) and $smarty.get.p eq 'facebook' and
    isset($smarty.get.code) and isset($smarty.get.state)}
    <meta http-equiv="refresh" content="0;url={$site_root_path}account/?p=facebook">
    {/if}

    <!-- styles -->
{if isset($thinkupllc_endpoint)}
{literal}<script type="text/javascript" src="//use.typekit.net/xzh8ady.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>{/literal}
{/if}
    <link href="{$site_root_path}assets/css/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="{$site_root_path}assets/css/vendor/font-awesome.min.css" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic|' rel='stylesheet' type='text/css'>
    <link href="{$site_root_path}assets/css/thinkup.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    {if ($smarty.get.m eq 'manage') or (isset($smarty.get.p))}
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="{$site_root_path}assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
    {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
    {/foreach}

    {/if}

{foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
{/foreach}
{include file="_usermessage-v2.tpl"}

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

  {if $csrf_token}<script type="text/javascript">var csrf_token = '{$csrf_token}';</script>{/if}

{if $post->post_text}
    <meta itemprop="name" content="{$post->network|ucwords} post by {$post->author_username} on ThinkUp">
    <meta itemprop="description" content="{$post->post_text|strip_tags}">
    <meta itemprop="image" content="http://thinkup.com/assets/img/thinkup-logo_sq.png">
{/if}

</head>
<body class="{if $body_type}{$body_type}{else}insight-stream{/if}">

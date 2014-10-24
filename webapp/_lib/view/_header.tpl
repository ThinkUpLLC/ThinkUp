<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#" itemscope itemtype="http://schema.org/Article">
<head>
    <meta charset="utf-8">
    <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{$site_root_path}assets/img/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$site_root_path}assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$site_root_path}assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$site_root_path}assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{$site_root_path}assets/ico/apple-touch-icon-57-precomposed.png">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="{if isset($logged_in_user)}{$logged_in_user}{/if}">

    {if count($insights) eq 1}
    <meta property="og:site_name" content="ThinkUp" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="{$twitter_card}" />
    <meta name="twitter:site" content="@thinkup" />
    <meta name="twitter:domain" content="thinkup.com" />

    <meta property="og:url" content="{$thinkup_application_url}?u={$insights[0]->instance->network_username|urlencode_network_username}&n={$insights[0]->instance->network}&d={$insights[0]->date|date_format:'%Y-%m-%d'}&s={$insights[0]->slug}" />

    <meta itemprop="name" content="{$insights[0]->headline|strip_tags:true|strip|truncate:100}" />
    <meta name="twitter:title" content="{$insights[0]->headline|strip_tags:true|strip|truncate:70}" />
    <meta property="og:title" content="{$insights[0]->headline|strip_tags:true|strip|truncate:100}" />

    {capture name=desc_default}Check out {$insights[0]->instance->network_username}'s insight{/capture}
    <meta itemprop="description" content="{$insights[0]->text|strip_tags:true|strip|truncate:200|default:$smarty.capture.desc_default}" />
    <meta name="description" content="{$insights[0]->text|strip_tags:true|strip|truncate:200|default:$smarty.capture.desc_default}" />
    <meta name="twitter:description" content="{$insights[0]->text|strip_tags:true|strip|truncate:200|default:$smarty.capture.desc_default}" />

    <meta itemprop="image" content="{$insight_image}">
    <meta property="og:image" content="{$insight_image}" />
    <meta property="og:image:secure" content="{$insight_image}" />
    <meta name="twitter:image:src" content="{$insight_image}" />
    <meta name="twitter:image:width" content="540" />

    <meta property="og:image:type" content="image/png">

    {if ($insights[0]->instance->network eq 'twitter')}
    <meta name="twitter:creator" content="@{$insights[0]->instance->network_username}" />
    {/if}
    {else}
    <meta name="description" content="{if $controller_title}{$controller_title} | {/if}{$app_title}" />
    {/if}





    <!-- styles -->
    <link href="{$site_root_path}assets/css/vendor/bootstrap.min.css" rel="stylesheet">
    <link href="{$site_root_path}assets/css/vendor/font-awesome.min.css" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic|' rel='stylesheet' type='text/css'>
    {if isset($thinkupllc_endpoint)}
    {literal}<script type="text/javascript" src="//use.typekit.net/xzh8ady.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>{/literal}
    {/if}
    <link href="{$site_root_path}assets/css/thinkup.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

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

{if isset($thinkupllc_endpoint)}
    {literal}<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-76614-5', 'auto');
  ga('send', 'pageview');

</script>{/literal}
{/if}

  <!-- google chart tools -->
  <!--Load the AJAX API-->
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>

  {if $csrf_token}<script type="text/javascript">var csrf_token = '{$csrf_token}';</script>{/if}

</head>
<body {if isset($body_classes)} class="{$body_classes}"{else}class="{if $body_type}{$body_type}{else}insight-stream{/if}"{/if}{if isset($body_id)} id="{$body_id}"{/if}>

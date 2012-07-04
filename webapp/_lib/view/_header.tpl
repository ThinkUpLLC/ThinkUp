<!DOCTYPE html>
<html lang="en" itemscope itemtype="http://schema.org/Article">
<head>
  <meta charset="utf-8">
  <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>
  <link rel="shortcut icon" type="image/x-icon" href="{$site_root_path}assets/img/favicon.png">
 
{if $smarty.get.v eq "insights"}

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./assets/css/bootstrap.css" rel="stylesheet">
    <style>
    {literal}
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    {/literal}
    </style>
    

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

{else}
  
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/base.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/style.css">
  {foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
  {/foreach}
  <!-- jquery -->
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/jquery-ui-1.8.13.css">
  <script type="text/javascript" src="{$site_root_path}assets/js/jquery.min-1.4.js"></script>
  <script type="text/javascript" src="{$site_root_path}assets/js/jquery-ui.min-1.8.js"></script>


    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$site_root_path}assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$site_root_path}assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$site_root_path}assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{$site_root_path}assets/ico/apple-touch-icon-57-precomposed.png">

  
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


{/if}


  <!-- google chart tools -->
  <!--Load the AJAX API-->
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>

  <script type="text/javascript" src="{$site_root_path}plugins/twitter/assets/js/widgets.js"></script>
  <script type="text/javascript">var site_root_path = '{$site_root_path}';</script>
  {if $csrf_token}<script type="text/javascript">var csrf_token = '{$csrf_token}';</script>{/if}
  {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
  {/foreach}

{if $enable_tabs}
<script type="text/javascript">
    {literal}
      // tabs functionality
      var current_query_key = 'updates';
      $(function() {
        $("#tabs").tabs( { select: function(event, ui) { current_query_key =  ui.panel.id  } } );
      });
      
      // buttons functionality
      $(function() {
        //all hover and click logic for buttons
        $(".linkbutton:not(.ui-state-disabled)")
        .hover(
          function() {
            $(this).addClass("ui-state-hover"); 
          },
          function() {
            $(this).removeClass("ui-state-hover"); 
          }
        )
        .mousedown(function() {
            $(this).parents('.linkbuttonset-single:first').find(".linkbutton.ui-state-active").removeClass("ui-state-active");
            if ($(this).is('.ui-state-active.linkbutton-toggleable, .linkbuttonset-multi .ui-state-active')) {
              $(this).removeClass("ui-state-active");
            }
            else {
              $(this).addClass("ui-state-active");
            }
        })
        .mouseup(function() {
          if (! $(this).is('.linkbutton-toggleable, .linkbuttonset-single .linkbutton,  .linkbuttonset-multi .linkbutton') ) {
            $(this).removeClass("ui-state-active");
          }
        });
      });
    {/literal}
</script>
{/if}

  <!-- custom css -->
  {literal}
  <style>
  .line { background:url('{/literal}{$site_root_path}{literal}assets/img/border-line-470.gif') no-repeat center bottom;
  margin: 8px auto;
  height: 1px;
  }

  </style>
  {/literal}

{if $post->post_text} 
<meta itemprop="name" content="{$post->network|ucwords} post by {$post->author_username} on ThinkUp">
<meta itemprop="description" content="{$post->post_text|strip_tags}">
<meta itemprop="image" content="http://thinkupapp.com/assets/img/thinkup-logo_sq.png">
{/if}
</head>
<body>

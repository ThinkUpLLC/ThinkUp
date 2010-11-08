<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>
  <link rel="shortcut icon" type="image/x-icon" href="{$site_root_path}assets/img/favicon.png">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/base.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/positioning.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/style.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/jquery-ui-1.7.1.custom.css">

  <!-- jquery -->
  <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <script type="text/javascript">var site_root_path = '{$site_root_path}';</script>
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
        $(".tt-button:not(.ui-state-disabled)")
        .hover(
          function() {
            $(this).addClass("ui-state-hover"); 
          },
          function() {
            $(this).removeClass("ui-state-hover"); 
          }
        )
        .mousedown(function() {
            $(this).parents('.tt-buttonset-single:first').find(".tt-button.ui-state-active").removeClass("ui-state-active");
            if ($(this).is('.ui-state-active.tt-button-toggleable, .tt-buttonset-multi .ui-state-active')) {
              $(this).removeClass("ui-state-active");
            }
            else {
              $(this).addClass("ui-state-active");
            }
        })
        .mouseup(function() {
          if (! $(this).is('.tt-button-toggleable, .tt-buttonset-single .tt-button,  .tt-buttonset-multi .tt-button') ) {
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
  .grid_4 a, .grid_10 a {color:#6184b5;}
  .thinkup-canvas a {color:#6184b5;}
  </style>
  {/literal}
</head>
<body>

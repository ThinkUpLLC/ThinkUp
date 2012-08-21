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
    <link href="{$site_root_path}assets/css/bootstrap.css" rel="stylesheet">
    {foreach from=$header_css item=css}
    <link type="text/css" rel="stylesheet" href="{$site_root_path}{$css}" />
    {/foreach}
    
    <style>
    {literal}
    
    form .control-group {
        margin : 6px;
    }

    .detail-btn {
        margin-right : 6px;
        margin-left : 12px;
    }
      
    .metaroll {
        color : #999;
        display : inline;
        font-size : small;
        line-height : 100%;
    }


    .label {
        padding: 1px 4px 2px;
        -webkit-border-radius: 1px;
        -moz-border-radius: 1px;
        border-radius: 1px;
    }

    .label .icon-white {
        margin-top : 2px;
    }
    
    .alert {
        padding: 6px;
    }

    .alert p .label {
        margin-left : -12px;
        z-index: 999;
    }
    
    .alert p a {
        font-weight : bolder;
    }
    
    .lead {
        padding-left : 22px;
        margin-right : 10px;
        margin-bottom : 0px;
        margin-top : 6px;
        color : #666;
    }
    
    .alert table {
        margin-top : 10px;
        width : 90%;
        margin-left : 22px;
    }
    
    .alert table td {
        border : 0;
    }
    
    .alert .chart {
        padding-top : 12px;
        margin-left : auto;
        margin-right : auto;
    }

    .service-user-icons {
        float : right;
    }

    .service-user-icons img {
        filter: gray; /* IE6-9 */
        -webkit-filter: grayscale(1); /* Google Chrome & Safari 6+ */
        opacity:0.4;
        padding-left : 6px;
    }
    
    .service-user-icons img:hover {
        filter: none;
        -webkit-filter: grayscale(0);
        opacity:1;
    }
    
    .alert table img {
        max-width : 200%;
    }
    
    .alert table .avatar2 {
        padding-top : 6px;
    }
    
    .avatar-data {
        width : 50px;
    }
    
    .alert table p {
        color : #666;
    }
    
    .password-meter {
    }
    .password-meter-message {
        color: #E41B17;
        margin-left : 10px;
    }
    .password-meter-bg, .password-meter-bar {
        height : 2px;
    }
    .password-meter-bg {
        background: transparent;
        width : 220px;
    }
    
    .password-meter-message-very-weak {
        color: #aa0033;
    }
    .password-meter-message-weak {
        color: #f5ac00;
    }
    .password-meter-message-good {
        color: #6699cc;
    }
    .password-meter-message-strong {
        color: #008000;
    }
    
    .password-meter-bg .password-meter-very-weak {
        background: #aa0033;
        width: 20%;
    }
    .password-meter-bg .password-meter-weak {
        background: #f5ac00;
        width: 30%;
    }
    .password-meter-bg .password-meter-good {
        background: #6699cc;
        width: 66%;
    }
    .password-meter-bg .password-meter-strong {
        background: #008000;
        width: 100%;
    }
    span.formError {
        color: #E41B17;
        margin-left : 10px;
        padding-top : 4px;
    }

    #wrap {
        background-color : transparent;
    }

    {/literal}
    </style>    

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script src="{$site_root_path}assets/js/jquery.js"></script>
    <script src="{$site_root_path}assets/js/bootstrap-collapse.js"></script>
    <script src="{$site_root_path}assets/js/bootstrap-tab.js"></script>
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
{if !$user_is_admin}
    <script type="text/javascript">

    window.location = "{$site_root_path}insights.php"

    </script>
{/if}

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

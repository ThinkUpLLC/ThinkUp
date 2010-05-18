<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>ThinkTank {$title}</title>
  <link rel="shortcut icon" type="image/x-icon" href="{$cfg->site_root_path}assets/img/favicon.ico">
  {if $cfg->bitly_api_key}
    <script type="text/javascript" src="http://bit.ly/javascript-api.js?version=latest&amp;login={$cfg->bitly_login}&amp;apiKey={$cfg->bitly_api_key}"></script>
  {/if}
  
  <!-- jquery -->
  <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

  <!-- custom css -->
  <link type="text/css" rel="stylesheet" href="{$cfg->site_root_path}assets/css/base.css">
  <link type="text/css" rel="stylesheet" href="{$cfg->site_root_path}assets/css/positioning.css">
  <link type="text/css" rel="stylesheet" href="{$cfg->site_root_path}assets/css/style.css">

  <script type="text/javascript">
    {literal}
      // tabs functionality
      $(function() {
        $("#tabs").tabs();
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
    {if $load neq 'no'}
      {literal}
        $(document).ready(function() {
          // References
          var sections = $(".menu li");
          
          var loading = $("#loading");
          var loading_replies = $("#loading_replies");
          var loading_followers = $("#loading_followers");
          var loading_friends = $("#loading_friends");
          var loading_links =  $("#loading_links");
          
          var posts_content = $("#posts_content");
          var replies_content = $("#replies_content");
          var followers_content = $("#followers_content");
          var friends_content = $("#friends_content");
          var links_content =  $("#links_content");
      {/literal}
      showLoading();
      posts_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$post_tabs[0]->short_name}", hideLoading);
      replies_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$replies_tabs[0]->short_name}", hideLoading);
      followers_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$followers_tabs[0]->short_name}", hideLoading);
      friends_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$friends_tabs[0]->short_name}", hideLoading);
      links_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$links_tabs[0]->short_name}", hideLoading);
      {literal}
          // Manage click events.
          sections.click(function() {
            $(this).siblings().removeClass('selected');
            $(this).addClass('selected');
            
            // Show the loading bar.
            showLoading();
            
            // Load selected section.
            switch (this.id) { {/literal}
            //posts tabs
            {foreach from=$post_tabs key=ptkey item=pt name=tabloop}
                case "{$pt->short_name}": 
                    posts_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$pt->short_name}", hideLoading);
                    break;
            {/foreach}
            //replies tabs
            {foreach from=$replies_tabs key=ptkey item=pt name=tabloop}
                case "{$pt->short_name}":
                    replies_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$pt->short_name}", hideLoading);
                    break;
            {/foreach}
            {foreach from=$followers_tabs key=ptkey item=pt name=tabloop}
                case "{$pt->short_name}":
                    followers_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$pt->short_name}", hideLoading);
                    break;
            {/foreach}
            {foreach from=$friends_tabs key=ptkey item=pt name=tabloop}
                case "{$pt->short_name}":
                    friends_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$pt->short_name}", hideLoading);
                    break;
            {/foreach}
            {foreach from=$links_tabs key=ptkey item=pt name=tabloop}
                case "{$pt->short_name}":
                    links_content.load("inline.view.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}&d={$pt->short_name}", hideLoading);
                    break;
            {/foreach}
              default:
                // Hide loading bar if there is no selected section.
                hideLoading();
                break;
            }
          });
      {literal}
          // Show loading bar
          function showLoading() {
            loading
              .css({visibility:"visible", opacity:"1", display:"block"})
            loading_replies
              .css({visibility:"visible", opacity:"1", display:"block"})
            loading_followers
              .css({visibility:"visible", opacity:"1", display:"block"})
            loading_friends
              .css({visibility:"visible", opacity:"1", display:"block"})
            loading_links
              .css({visibility:"visible", opacity:"1", display:"block"})
          }
          
          // Hide loading bar
          function hideLoading() {
            loading.fadeTo(1000, 0);
            loading_replies.fadeTo(1000, 0);
            loading_followers.fadeTo(1000, 0);
            loading_friends.fadeTo(1000, 0);
            loading_links.fadeTo(1000, 0);
          };
        }); // end $(document).ready(function() {
      {/literal}
    {/if}
  </script>
</head>

<body>

{include file="_header.login.tpl"}

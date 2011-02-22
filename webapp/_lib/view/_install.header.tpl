<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>
  <link rel="shortcut icon" type="image/x-icon" href="{$site_root_path}assets/img/favicon.png">
  
  <!-- jquery -->
  <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  {literal}
  <script type="text/javascript">
    $(document).ready(function() {
      $('.toggle-advanced-options').click(function(e) {
        var advanceOptions = $(this).next('#database-advance-options');
        var icon = $('.ui-icon', this);
        
        advanceOptions.slideToggle(500, function() {
          if ( $('#database-advance-options').is(':hidden') ) {
            icon.removeClass('ui-icon-circle-triangle-s')
            icon.addClass('ui-icon-circle-triangle-e');
          } else {
            icon.removeClass('ui-icon-circle-triangle-e');
            icon.addClass('ui-icon-circle-triangle-s');
          }  
        });
        
        e.preventDefault();
        return false;
      });
      
      $('.toggle-help-msg').click(function(e) {
        var helpMessage = $(this).next('#help-no-email-message');
        helpMessage.slideToggle(500);
        e.preventDefault();
        return false;
      });
      var tz_offset = (new Date).getTimezoneOffset() / (-60); //Get Timezone offset in minutes from the user's client.
      $('#timezone').find('option').filter('[title='+tz_offset+']').attr('selected', 'selected');
    });
  </script>
  {/literal}


  <!-- custom css -->
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/base.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/positioning.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/style.css">
  <link type="text/css" rel="stylesheet" href="{$site_root_path}assets/css/installer.css">
</head>
<body>
  <div id="status-bar">&nbsp;</div>
  <div class="container clearfix">
    <div id="app-title">
          <h1><span class="bold">Think</span><span class="gray">Up</span></h1>
          <h2>New ideas</h2>
    </div>
  </div>
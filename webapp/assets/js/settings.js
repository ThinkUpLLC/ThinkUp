
$(function() {
  $(".btnPub").click(function() {
    var element = $(this);
    var u = element.attr("id");
    var dataString = 'u=' + u + "&p=1&csrf_token=" + window.csrf_token; // toggle public on
    $.ajax({
      type: "GET",
      url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
      data: dataString,
      success: function() {
        $('#div' + u).html("<span class='btn btn-success' id='messagepub" + u + "'></span>");
        $('#messagepub' + u).html("Set to public!").hide().fadeIn(1500, function() {
          $('#messagepub' + u);
        });
      }
    });
    return false;
  });

  $(".btnPriv").click(function() {
    var element = $(this);
    var u = element.attr("id");
    var dataString = 'u=' + u + "&p=0&csrf_token=" + window.csrf_token; // toggle public off
    $.ajax({
      type: "GET",
      url: "{/literal}{$site_root_path}{literal}account/toggle-public.php",
      data: dataString,
      success: function() {
        $('#div' + u).html("<span class='btn btn-default' id='messagepriv" + u + "'></span>");
        $('#messagepriv' + u).html("Set to private!").hide().fadeIn(1500, function() {
          $('#messagepriv' + u);
        });
      }
    });
    return false;
  });
});

$(function() {
  $(".btnPlay").click(function() {
    var element = $(this);
    var u = element.attr("id");
    var dataString = 'u=' + u + "&p=1&csrf_token=" + window.csrf_token; // toggle active on
    $.ajax({
      type: "GET",
      url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
      data: dataString,
      success: function() {
        $('#divactivate' + u).html("<span class='btn btn-success' id='messageplay" + u + "'></span>");
        $('#messageplay' + u).html("Started!").hide().fadeIn(1500, function() {
          $('#messageplay' + u);
        });
      }
    });
    return false;
  });

  $(".btnPause").click(function() {
    var element = $(this);
    var u = element.attr("id");
    var dataString = 'u=' + u + "&p=0&csrf_token=" + window.csrf_token; // toggle active off
    $.ajax({
      type: "GET",
      url: "{/literal}{$site_root_path}{literal}account/toggle-active.php",
      data: dataString,
      success: function() {
        $('#divactivate' + u).html("<span class='btn btn-warning' id='messagepause" + u + "'></span>");
        $('#messagepause' + u).html("Paused!").hide().fadeIn(1500, function() {
          $('#messagepause' + u);
        });
      }
    });
    return false;
  });
});

$(function() {
var activateOwner = function(u) {
  //removing the "user" from id here to stop conflict with plugin
  u = u.substr(4);
  var dataString = 'oid=' + u + "&a=1&csrf_token=" + window.csrf_token; // toggle owner active on
  $.ajax({
    type: "GET",
    url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
    data: dataString,
    success: function() {
      $('#spanowneractivation' + u).css('display', 'none');
      $('#messageowneractive' + u).html("Activated!").hide().fadeIn(1500, function() {
        $('#messageowneractive' + u);
      });
      $('#spanownernamelink' + u).css('display', 'inline');
      $('#user' + u).val('Deactivate');
      $('#spanownernametext' + u).css('display', 'none');
      $('#user' + u).removeClass('btn-success').addClass('btn-danger');
      $('#userAdmin' + u).show();
      setTimeout(function() {
          $('#messageowneractive' + u).css('display', 'none');
          $('#spanowneractivation' + u).hide().fadeIn(1500);
        },
        2000
      );
    }
  });
  return false;
};

var deactivateOwner = function(u) {
  //removing the "user" from id here to stop conflict with plugin
  u = u.substr(4);
  var dataString = 'oid=' + u + "&a=0&csrf_token=" + window.csrf_token; // toggle owner active off
  $.ajax({
    type: "GET",
    url: "{/literal}{$site_root_path}{literal}account/toggle-owneractive.php",
    data: dataString,
    success: function() {
      $('#spanowneractivation' + u).css('display', 'none');
      $('#messageowneractive' + u).html("Deactivated!").hide().fadeIn(150, function() {
        $('#messageowneractive' + u);
      });
      $('#spanownernamelink' + u).css('display', 'none');
      $('#spanownernametext' + u).css('display', 'inline');
      $('#user' + u).val('Activate');
      $('#user' + u).removeClass('btn-danger').addClass('btn-success');
      $('#userAdmin' + u).hide();
      setTimeout(function() {
          $('#messageowneractive' + u).css('display', 'none');
          $('#spanowneractivation' + u).hide().fadeIn(1500);
        },
        2000
      );
    }
  });
  return false;
};

var promoteOwner = function(u) {
  //removing the "userAdmin" from id here to stop conflict with plugin
  u = u.substr(9);
  var dataString = 'oid=' + u + "&a=1&csrf_token=" + window.csrf_token; // toggle owner active on
  $.ajax({
    type: "GET",
    url: "{/literal}{$site_root_path}{literal}account/toggle-owneradmin.php",
    data: dataString,
    success: function() {
      $('#spanowneradmin' + u).css('display', 'none');
      $('#messageadmin' + u).html("Promoted!").hide().fadeIn(1500, function() {
        $('#messageadmin' + u);
      });
      $('#spanownernamelink' + u).css('display', 'inline');
      $('#userAdmin' + u).val('Demote');
      $('#spanownernametext' + u).css('display', 'none');
      $('#userAdmin' + u).removeClass('btn-success').addClass('btn-danger');
      setTimeout(function() {
          $('#messageadmin' + u).css('display', 'none');
          $('#spanowneradmin' + u).hide().fadeIn(1500);
        },
        2000
      );
    }
  });
  return false;
};

var demoteOwner = function(u) {
  //removing the "userAdmin" from id here to stop conflict with plugin
  u = u.substr(9);
  var dataString = 'oid=' + u + "&a=0&csrf_token=" + window.csrf_token; // toggle owner active off
  $.ajax({
    type: "GET",
    url: "{/literal}{$site_root_path}{literal}account/toggle-owneradmin.php",
    data: dataString,
    success: function() {
      $('#spanowneradmin' + u).css('display', 'none');
      $('#messageadmin' + u).html("Demoted!").hide().fadeIn(1500, function() {
        $('#messageadmin' + u);
      });
      $('#spanownernamelink' + u).css('display', 'none');
      $('#spanownernametext' + u).css('display', 'inline');
      $('#userAdmin' + u).val('Promote');
      $('#userAdmin' + u).removeClass('btn-danger').addClass('btn-success');
      setTimeout(function() {
          $('#messageadmin' + u).css('display', 'none');
          $('#spanowneradmin' + u).hide().fadeIn(1500);
        },
        2000
      );
    }
  });
  return false;
};

$(".toggleOwnerActivationButton").click(function() {
  if($(this).val() == 'Activate') {
    activateOwner($(this).attr("id"));
  } else {
    deactivateOwner($(this).attr("id"));
  }
});

$(".toggleOwnerAdminButton").click(function() {
  if($(this).val() == 'Promote') {
    promoteOwner($(this).attr("id"));
  } else {
    demoteOwner($(this).attr("id"));
  }
});

$('.manage_plugin').click(function (e) {
  var url = $(this).attr('href');
  var p = url.replace(/.*p=/, '').replace(/#.*/, '');;
  if (window.location.href.indexOf("="+p) >= 0) {
    $('.section').hide();
    $('#manage_plugin').show();
    e.preventDefault();
  }
});
if ((show_plugin && (!window.location.hash || window.location.hash == '' || window.location.hash == '#_=_' ))
|| (window.location.hash && window.location.hash == '#manage_plugin')) {
  $('.section').hide();
  $('#manage_plugin').show();
}

});

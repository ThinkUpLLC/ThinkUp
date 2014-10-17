(function() {
  var $lastActiveDateGroup, animateContentShift, checkUsername, constants, featureTest, pinDateMarker, setActiveDateGroup, setDateGroupData, setFixedPadding, setListOpenData, setNavHeight, timerUsername, toggleDiff, toggleListVisibility, wt;

  wt = window.tu = {};

  featureTest = function(property, value, noPrefixes) {
    var el, mStyle, prop;
    prop = property + ':';
    el = document.createElement('test');
    mStyle = el.style;
    if (!noPrefixes) {
      mStyle.cssText = prop + ['-webkit-', '-moz-', '-ms-', '-o-', ''].join(value + ';' + prop) + value + ';';
    } else {
      mStyle.cssText = prop + value;
    }
    return mStyle[property].indexOf(value) !== -1;
  };

  constants = wt.constants = {};

  constants.colors = {
    pea: "#9dd767",
    pea_dark: "#5fac1c",
    pea_darker: "#417505",
    salmon: "#fc939e",
    salmon_dark: "#da6070",
    salmon_darker: "#d0374b",
    creamsicle: "#ffbb4e",
    creamsicle_dark: "#ff8f41",
    creamsicle_darker: "#f36400",
    sepia: "#c0bdaf",
    sepia_dark: "#a19f8b",
    sepia_darker: "#8a876f",
    historical: "#c0bdaf",
    historical_dark: "#a19f8b",
    historical_darker: "#8a876f",
    purple: "#b690e2",
    purple_dark: "#8e69c2",
    purple_darker: "#7348b0",
    mint: "#41dab3",
    mint_dark: "#24b98f",
    mint_darker: "#1c8e6e",
    bubblegum: "#f576b5",
    bubblegum_dark: "#b3487c",
    bubblegum_darker: "#8f3963",
    seabreeze: "#44c9d7",
    seabreeze_dark: "#198a9c",
    seabreeze_darker: "#126370",
    dijon: "#e4bf28",
    dijon_dark: "#c59301",
    dijon_darker: "#926d01",
    sandalwood: "#fd8560",
    sandalwood_dark: "#d13a0a",
    sandalwood_darker: "#a02c08",
    caramel: "#dd814b",
    caramel_dark: "#9e5e14",
    caramel_darker: "#71430e"
  };

  animateContentShift = function(state) {
    var leftPos, pos, selector;
    pos = state === "open" ? "280px" : "0";
    selector = ".navbar-default";
    if ($(".app-message").length && $("body").hasClass("app-message-visible")) {
      selector += ", .app-message";
    }
    $(selector).animate({
      left: pos
    }, 150, function() {
      if (pos === "0") {
        return $(selector).css("left", "");
      }
    });
    if ($(".date-marker.fixed").length) {
      leftPos = $(".date-marker.fixed").offset().left;
      pos = state === "open" ? "" + (leftPos + 280) + "px" : "" + (leftPos - 280) + "px";
      $(".date-marker.fixed").animate({
        left: pos
      }, 150, function() {
        return $(".date-marker.fixed").css("left", "");
      });
    }
    if ($(window).width() <= 540) {
      pos = state === "open" ? "-280px" : "0";
      return $(".btn-submit").animate({
        right: pos
      }, 150);
    }
  };

  wt.appMessage = {
    paddingChange: wt.navHeight - $(".navbar-default").outerHeight(true),
    create: function(message, type) {
      var $el, msgClass;
      if (type == null) {
        type = "info";
      }
      msgClass = "content";
      if (type === "warning") {
        msgClass += " fa-override-before fa-exclamation-triangle";
      }
      if (type === "success") {
        msgClass += " fa-override-before fa-check-circle";
      }
      $el = $("<div class=\"app-message app-message-" + type + "\" style=\"display: none\">\n  <div class=\"" + msgClass + "\">" + message + "</div>\n  <a href=\"#\" class=\"app-message-close\"><i class=\"fa fa-times-circle icon\"></i></a>\n</div>");
      $("#page-content").append($el);
      return $(".container").animate({
        paddingTop: "+=" + wt.appMessage.paddingChange
      }, 150, function() {
        $(".app-message").fadeIn(150);
        $("body").addClass("app-message-visible");
        if (!$("body").hasClass("account")) {
          return setNavHeight(true);
        }
      });
    },
    destroy: function() {
      $(".app-message").fadeOut(150);
      return $(".container").animate({
        paddingTop: "+=-" + wt.appMessage.paddingChange
      }, 150, function() {
        $("body").removeClass("app-message-visible");
        if (!$("body").hasClass("account")) {
          return setNavHeight(true);
        }
      });
    }
  };

  setNavHeight = function(fixPadding) {
    var oldHeight;
    if (fixPadding == null) {
      fixPadding = false;
    }
    oldHeight = wt.navHeight;
    if ($(".app-message").length && $("body").hasClass("app-message-visible")) {
      wt.navHeight = $(".app-message").outerHeight(true) + $(".app-message").offset().top;
    } else {
      wt.navHeight = $(".navbar").outerHeight(true);
    }
    if (fixPadding && (oldHeight !== wt.navHeight)) {
      return setFixedPadding();
    }
  };

  setFixedPadding = function() {
    $(".container").css("padding-top", wt.navHeight);
    return $(".date-marker").css("top", wt.navHeight + 14);
  };

  timerUsername = null;

  checkUsername = function($el) {
    if (timerUsername) {
      clearTimeout(timerUsername);
    }
    return timerUsername = setTimeout(function() {
      var $group, _ref;
      $group = $el.parent();
      if (((_ref = $el.val().match(/^[\w]{3,15}$/gi)) != null ? _ref.length : void 0) !== 1) {
        $group.removeClass("form-group-ok").addClass("form-group-warning");
        return wt.appMessage.create("Usernames must be 3-15 characters and contain only numbers and letters", "warning");
      } else {
        $group.addClass("form-group-ok").removeClass("form-group-warning");
        return wt.appMessage.destroy();
      }
    }, 500);
  };

  setDateGroupData = function() {
    return $(".date-group").each(function(i) {
      $(this).data("scroll-top", $(this).offset().top);
      return $(this).data("scroll-bottom", $(this).offset().top + $(this).height() - $(this).find(".date-marker").height());
    });
  };

  $lastActiveDateGroup = null;

  setActiveDateGroup = function() {
    var anyActive;
    if ($(window).scrollTop() + $(window).height() <= $("body").height()) {
      anyActive = false;
      $(".date-group").each(function(i) {
        var _ref;
        if (($(this).offset().top < (_ref = $(window).scrollTop() + wt.navHeight) && _ref < $(this).data("scroll-bottom") - 14)) {
          anyActive = true;
          if ($lastActiveDateGroup == null) {
            $lastActiveDateGroup = $(this);
          }
          pinDateMarker($(this));
          if (($lastActiveDateGroup != null) && !$(this).is($lastActiveDateGroup)) {
            return $lastActiveDateGroup = $(this);
          }
        } else {
          return $(this).find(".date-marker").removeClass("fixed absolute").css("top", "");
        }
      });
      if (($lastActiveDateGroup != null) && !anyActive) {
        if ($lastActiveDateGroup.data("scroll-top") < $(window).scrollTop()) {
          pinDateMarker($lastActiveDateGroup, "absolute");
        }
        return pinDateMarker($lastActiveDateGroup.prev(), "absolute", false);
      }
    }
  };

  pinDateMarker = function($container, position, clearClasses) {
    var $dm;
    if (position == null) {
      position = "fixed";
    }
    if (clearClasses == null) {
      clearClasses = true;
    }
    if (clearClasses) {
      $(".date-marker").removeClass("fixed absolute").css("top", "");
    }
    $dm = $container.find(".date-marker");
    $dm.addClass(position);
    if (position === "absolute") {
      return $dm.css("top", $container.data("scroll-bottom"));
    }
  };

  setListOpenData = function(includeClosed, setHeight) {
    if (includeClosed == null) {
      includeClosed = false;
    }
    if (setHeight == null) {
      setHeight = false;
    }
    return $(".body-list-show-some").each(function() {
      var $list, listOpenHeight, padding;
      $list = $(this);
      if (includeClosed) {
        $list.height($list.height());
        $list.data("height-closed", $list.outerHeight(true));
        $list.find(".list-item").show();
      }
      if (($list.data("rows") != null) && ($list.data("row-height") != null)) {
        padding = $list.data("row-padding") ? $list.data("row-padding") : 0;
        listOpenHeight = ($list.data("rows") * $list.data("row-height")) + (($list.data("rows") - 1) * padding);
      } else {
        listOpenHeight = 0;
        $list.find(".list-item").each(function() {
          return listOpenHeight += $(this).outerHeight(true);
        });
      }
      $list.data("height-open", listOpenHeight);
      if (setHeight && $list.hasClass("all-items-visible")) {
        return $list.height(listOpenHeight);
      }
    });
  };

  toggleListVisibility = function($btn) {
    var $list, listHeight;
    $list = $btn.prev(".body-list");
    listHeight = $list.hasClass("all-items-visible") ? $list.data("height-closed") : $list.data("height-open");
    return $list.animate({
      height: listHeight
    }, 250, function() {
      var oldText;
      oldText = $btn.find(".btn-text").text();
      $btn.find(".btn-text").text($btn.data("text"));
      $btn.data("text", oldText);
      $list.toggleClass("all-items-visible");
      return $btn.toggleClass("active");
    });
  };

  toggleDiff = function($link) {
    var $td, text;
    $td = $link.parents(".text-diff");
    $td.find(".bio-diff, .bio-before-after").toggle();
    setListOpenData(false, true);
    text = $link.text();
    return $link.text($link.data("alt-text")).data("alt-text", text);
  };

  $(function() {
    var isjPMon, jPM;
    setNavHeight();
    if (!$(".stream-permalink").length) {
      setListOpenData(true);
    }
    $(window).load(function() {
      setDateGroupData();
      if (!$(".stream-permalink").length) {
        return setListOpenData(true);
      }
    });
    isjPMon = false;
    jPM = $.jPanelMenu({
      openPosition: "280px",
      keyboardShortcuts: false,
      beforeOpen: function() {
        return animateContentShift("open");
      },
      beforeClose: function() {
        return animateContentShift("close");
      },
      afterOff: function() {
        return $(".app-message, .navbar-default").stop(true, true).css("left", "");
      }
    });
    if ((!$("body").hasClass("menu-open") || $(window).width() < 820) && !$("body").hasClass("menu-off")) {
      $("#page-content").css({
        minHeight: $(window).height() - wt.navHeight
      });
      jPM.on();
      isjPMon = true;
    }
    $(window).resize(function() {
      setListOpenData(false, true);
      setNavHeight(true);
      if (!$("body").hasClass("menu-off") && $("body").hasClass("menu-open") && $(window).width() < 820 && (!isjPMon)) {
        jPM.on();
        isjPMon = true;
      }
      if (!$("body").hasClass("menu-off") && $("body").hasClass("menu-open") && $(window).width() >= 820 && isjPMon) {
        jPM.off();
        return isjPMon = false;
      }
    });
    if ($("body").hasClass("insight-stream")) {
      if (featureTest("position", "sticky")) {
        $(".date-marker").addClass("sticky");
      } else {
        $(window).scroll(function() {
          return setActiveDateGroup();
        });
      }
    }
    $("body").on("click", ".panel-body .btn-see-all", function(e) {
      e.preventDefault();
      return toggleListVisibility($(this));
    });
    $(window).load(function() {
      if ($("body").data("app-message-text")) {
        wt.appMessage.create($("body").data("app-message-text"), $("body").data("app-message-type"));
      }
      if ((typeof app_message !== "undefined" && app_message !== null) && (app_message.msg != null) && (app_message.type != null)) {
        return wt.appMessage.create(app_message.msg, app_message.type);
      }
    });
    $("body").on("click", ".app-message .app-message-close", function(e) {
      e.preventDefault();
      return wt.appMessage.destroy();
    });
    $("#control-username").on("keyup", function() {
      return checkUsername($(this));
    });
    $("body").on("click", ".show-section", function(e) {
      var $el;
      e.preventDefault();
      $el = $($(this).data("section-selector"));
      if ($el.length) {
        return $el.show();
      }
    });
    $(".privacy-toggle .toggle-label").click(function() {
      var $target_radio, $this, dataString, p, u;
      $this = $(this);
      $target_radio = $("#" + $this.data("check-field"));
      p = $target_radio.val();
      u = $this.parent().data("id");
      dataString = ("u=" + u + "&p=" + p + "&csrf_token=") + window.csrf_token;
      $.ajax({
        type: "GET",
        url: window.site_root_path + "account/toggle-public.php",
        data: dataString,
        success: function() {
          $this.removeAttr("checked");
          $target_radio.attr("checked", "checked");
          return window.location = window.site_root_path + ("account?p=" + ($this.parent().data("network")));
        }
      });
      return false;
    });
    $(".btn-account-remove").click(function() {
      var $this, action_left, action_speed, label_margin, label_speed, text, vis;
      $this = $(this);
      vis = !$this.hasClass("visible");
      label_margin = vis ? 37 : 0;
      label_speed = vis ? 250 : 500;
      action_speed = vis ? 500 : 250;
      action_left = vis ? 0 : -50;
      text = vis ? $(this).data("label-visible") : $(this).data("label-hidden");
      $(".list-accounts-item").find(".account-label").animate({
        marginLeft: label_margin
      }, label_speed);
      $(".list-accounts-item").find(".account-action-delete").animate({
        left: action_left
      }, action_speed);
      $this.text(text);
      return $this.toggleClass("visible");
    });
    $("body").on("click", ".diff-toggle", function(e) {
      e.preventDefault();
      return toggleDiff($(this));
    });
    $(window).load(function() {
      var delay, delayed;
      delayed = function() {
        return $('body').hide().show();
      };
      return delay = window.setTimeout(delayed, 1000);
    });
    $("body").on("click", ".navbar .btn-signup", function() {
      return ga('send', 'event', 'Signup Button', 'click', 'navbar');
    });
    return $("body").on("click", ".insight-tout .btn-signup", function() {
      return ga('send', 'event', 'Signup Button', 'click', 'tout');
    });
  });

}).call(this);

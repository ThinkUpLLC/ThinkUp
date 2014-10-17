wt = window.tu = {}

# As it says, this lets us test if a browser has a particular CSS feature
featureTest = ( property, value, noPrefixes ) ->
  # Thanks Modernizr! https://github.com/phistuck/Modernizr/commit/3fb7217f5f8274e2f11fe6cfeda7cfaf9948a1f5
  prop = property + ':'
  el = document.createElement( 'test' )
  mStyle = el.style

  if !noPrefixes
    mStyle.cssText = prop + [ '-webkit-', '-moz-', '-ms-', '-o-', '' ].join( value + ';' + prop ) + value + ';'
  else
    mStyle.cssText = prop + value

  mStyle[ property ].indexOf( value ) != -1

# For colors and other non-private constants
constants = wt.constants = {}

constants.colors =
  pea: "#9dd767"
  pea_dark: "#5fac1c"
  pea_darker: "#417505"
  salmon: "#fc939e"
  salmon_dark: "#da6070"
  salmon_darker: "#d0374b"
  creamsicle: "#ffbb4e"
  creamsicle_dark: "#ff8f41"
  creamsicle_darker: "#f36400"
  sepia: "#c0bdaf"
  sepia_dark: "#a19f8b"
  sepia_darker: "#8a876f"
  historical: "#c0bdaf"
  historical_dark: "#a19f8b"
  historical_darker: "#8a876f"
  purple: "#b690e2"
  purple_dark: "#8e69c2"
  purple_darker: "#7348b0"
  mint: "#41dab3"
  mint_dark: "#24b98f"
  mint_darker: "#1c8e6e"
  bubblegum: "#f576b5"
  bubblegum_dark: "#b3487c"
  bubblegum_darker: "#8f3963"
  seabreeze: "#44c9d7"
  seabreeze_dark: "#198a9c"
  seabreeze_darker: "#126370"
  dijon: "#e4bf28"
  dijon_dark: "#c59301"
  dijon_darker: "#926d01"
  sandalwood: "#fd8560"
  sandalwood_dark: "#d13a0a"
  sandalwood_darker: "#a02c08"
  caramel: "#dd814b"
  caramel_dark: "#9e5e14"
  caramel_darker: "#71430e"

##### APP CHROME FUNCTIONS (menu, system messages, etc.)
##### ----------------------------------------------------------

animateContentShift = (state) ->
  # This is called when the menu is opened.
  # We need to move all fixed position elements over 280 pixels
  # Right now, that's the nav, form submits on mobile,
  # app messages, and date markers
  pos = if state is "open" then "280px" else "0"
  selector = ".navbar-default"
  if $(".app-message").length and $("body").hasClass "app-message-visible"
    selector += ", .app-message"
  $(selector).animate(
    left: pos
  , 150
  , -> if pos is "0" then $(selector).css "left", ""
  )
  if $(".date-marker.fixed").length
    leftPos = $(".date-marker.fixed").offset().left
    pos = if state is "open" then "#{leftPos + 280}px" else "#{leftPos - 280}px"
    $(".date-marker.fixed").animate(
      left: pos
    , 150
    , ->
      $(".date-marker.fixed").css "left", ""
    )
  if $(window).width() <= 540
    pos = if state is "open" then "-280px" else "0"
    $(".btn-submit").animate(
      right: pos
    , 150
    )

wt.appMessage =
  paddingChange: wt.navHeight - $(".navbar-default").outerHeight(true)
  create: (message, type = "info") ->
    msgClass = "content"
    if type is "warning" then msgClass += " fa-override-before fa-exclamation-triangle"
    if type is "success" then msgClass += " fa-override-before fa-check-circle"
    $el = $("""<div class="app-message app-message-#{type}" style="display: none">
      <div class="#{msgClass}">#{message}</div>
      <a href="#" class="app-message-close"><i class="fa fa-times-circle icon"></i></a>
    </div>""")
    $("#page-content").append($el)
    $(".container").animate({
        paddingTop: "+=#{wt.appMessage.paddingChange}"
      }
      , 150
      , ->
        $(".app-message").fadeIn(
          150
        )
        $("body").addClass "app-message-visible"
        setNavHeight(true) if not $("body").hasClass "account"
    )
  destroy: ->
    $(".app-message").fadeOut(150)
    $(".container").animate({
        paddingTop: "+=-#{wt.appMessage.paddingChange}"
      }
      , 150
      , ->
        $("body").removeClass "app-message-visible"
        setNavHeight(true) if not $("body").hasClass "account"
    )

setNavHeight = (fixPadding = false)->
  oldHeight = wt.navHeight
  if $(".app-message").length and $("body").hasClass "app-message-visible"
    wt.navHeight = $(".app-message").outerHeight(true) + $(".app-message").offset().top
  else
    wt.navHeight = $(".navbar").outerHeight(true)
  if fixPadding and (oldHeight isnt wt.navHeight) then setFixedPadding()

setFixedPadding = ->
  $(".container").css "padding-top", wt.navHeight
  $(".date-marker").css "top", (wt.navHeight + 14)


##### FORMS
##### -----------------------------------------

timerUsername = null
checkUsername = ($el) ->
  if timerUsername then clearTimeout timerUsername
  timerUsername = setTimeout(->
    $group = $el.parent()
    if $el.val().match(/^[\w]{3,15}$/gi)?.length isnt 1
      $group.removeClass("form-group-ok").addClass("form-group-warning")
      wt.appMessage.create "Usernames must be 3-15 characters and contain only numbers and letters", "warning"
    else
      $group.addClass("form-group-ok").removeClass("form-group-warning")
      wt.appMessage.destroy()
  , 500
  )

##### STREAM AND INSIGHTS
##### ----------------------------------------

# The next two functions make our dates stick to the top on desktop
setDateGroupData = ->
  $(".date-group").each (i) ->
    $(@).data "scroll-top", $(@).offset().top
    $(@).data "scroll-bottom", ($(@).offset().top + $(@).height() - $(@).find(".date-marker").height())

# Keep track of the group that was last active
$lastActiveDateGroup = null
setActiveDateGroup = ->
  if $(window).scrollTop() + $(window).height() <= $("body").height()
    # Tracks if any of our date markers are active
    anyActive = false
    $(".date-group").each (i) ->
      # Is the top of the screen inside a date group?
      # The 45px is to account for the fixed hehader
      if $(@).offset().top < $(window).scrollTop() + wt.navHeight < $(@).data("scroll-bottom") - 14
        anyActive = true
        # Has the active group not been set?
        if not $lastActiveDateGroup? then $lastActiveDateGroup = $(@)
        pinDateMarker $(@)
        # Do we have a new date group?
        if $lastActiveDateGroup? and not $(@).is $lastActiveDateGroup
          $lastActiveDateGroup = $(@)
      else
        $(@).find(".date-marker").removeClass("fixed absolute").css "top", ""
    # Now, what to do if nothing is active
    if $lastActiveDateGroup? and not anyActive
      # Is the group moving out of the viewport at the top
      if $lastActiveDateGroup.data("scroll-top") < $(window).scrollTop()
        pinDateMarker $lastActiveDateGroup, "absolute"
      # We want to make sure we move the previous one to absolute positioning
      pinDateMarker $lastActiveDateGroup.prev(), "absolute", false

pinDateMarker = ($container, position = "fixed", clearClasses = true) ->
  if clearClasses then $(".date-marker").removeClass("fixed absolute").css "top", ""
  $dm = $container.find(".date-marker")
  $dm.addClass position
  if position is "absolute" then $dm.css "top", $container.data "scroll-bottom"


# This sets the size of the open and closed states of a list
# It's called on page load and whenever the screen size changes
setListOpenData = (includeClosed = false, setHeight = false) ->
  $(".body-list-show-some").each ->
    $list = $(@)
    # We only save height-closed on page load
    if includeClosed
      $list.height $list.height()
      $list.data "height-closed", $list.outerHeight(true)
      $list.find(".list-item").show()
    if $list.data("rows")? and $list.data("row-height")?
      padding = if $list.data("row-padding") then $list.data("row-padding") else 0
      listOpenHeight = ($list.data("rows") * $list.data("row-height")) +
      (($list.data("rows") - 1) * padding)
    else
      listOpenHeight = 0
      $list.find(".list-item").each -> listOpenHeight += $(@).outerHeight(true)
    $list.data "height-open", listOpenHeight
    if setHeight and $list.hasClass "all-items-visible" then $list.height listOpenHeight

toggleListVisibility = ($btn) ->
  $list = $btn.prev(".body-list")
  listHeight = if $list.hasClass "all-items-visible" then $list.data "height-closed" else $list.data "height-open"
  $list.animate(
    height: listHeight
  , 250
  , ->
    oldText = $btn.find(".btn-text").text()
    $btn.find(".btn-text").text $btn.data "text"
    $btn.data "text", oldText
    $list.toggleClass "all-items-visible"
    $btn.toggleClass "active"
  )

toggleDiff = ($link) ->
  $td = $link.parents(".text-diff")
  $td.find(".bio-diff, .bio-before-after").toggle()
  setListOpenData(false, true)
  text = $link.text()
  $link.text($link.data "alt-text")
  .data("alt-text", text)


$ ->
  setNavHeight()
  setListOpenData(true) unless $(".stream-permalink").length
  $(window).load ->
    setDateGroupData()
    # Run this again to catch any images in the stream
    setListOpenData(true) unless $(".stream-permalink").length

  # Initialize our menu
  isjPMon = false
  jPM = $.jPanelMenu(
    openPosition: "280px"
    keyboardShortcuts: false
    beforeOpen: -> animateContentShift "open"
    beforeClose: -> animateContentShift "close"
    afterOff: ->
      $(".app-message, .navbar-default").stop(true, true).css "left", ""
  )

  if (!$("body").hasClass("menu-open") or $(window).width() < 820) and !$("body").hasClass("menu-off")
    $("#page-content").css({minHeight: $(window).height() - wt.navHeight})
    jPM.on()
    isjPMon = true

  # Change a few things when the user resizes their browser
  $(window).resize ->
    setListOpenData(false, true)
    setNavHeight(true)
    # $("#page-content").css({minHeight: $(window).height() - wt.navHeight})
    if !$("body").hasClass("menu-off") and $("body").hasClass("menu-open") and
    $(window).width() < 820 and (not isjPMon)
      jPM.on()
      isjPMon = true
    if !$("body").hasClass("menu-off") and $("body").hasClass("menu-open") and
    $(window).width() >= 820  and isjPMon
      jPM.off()
      isjPMon = false

  # Test if the browser can use position: sticky.
  # If not, load our sticky dates script.
  # NOTE: We may still get bugs in Android tablets
  if $("body").hasClass "insight-stream"
    if featureTest "position", "sticky"
      $(".date-marker").addClass "sticky"
    else
      $(window).scroll -> setActiveDateGroup()

  # Toggle hidden list items
  $("body").on "click", ".panel-body .btn-see-all", (e) ->
    e.preventDefault()
    toggleListVisibility $(@)

  $(window).load ->
    if $("body").data "app-message-text"
      wt.appMessage.create $("body").data("app-message-text"), $("body").data("app-message-type")
    if app_message? and app_message.msg? and app_message.type?
      wt.appMessage.create app_message.msg, app_message.type

  $("body").on "click", ".app-message .app-message-close", (e) ->
    e.preventDefault()
    wt.appMessage.destroy()

  $("#control-username").on "keyup", -> checkUsername($(@))

  $("body").on "click", ".show-section", (e) ->
    e.preventDefault()
    $el = $($(@).data("section-selector"))
    if $el.length then $el.show()

  $(".privacy-toggle .toggle-label").click ->
    $this = $(@)
    $target_radio = $("#"+ $this.data("check-field"))
    p = $target_radio.val()
    u = $this.parent().data("id")
    dataString = "u=#{u}&p=#{p}&csrf_token=" + window.csrf_token
    $.ajax({
      type: "GET"
      url: window.site_root_path + "account/toggle-public.php"
      data: dataString
      success: ->
        $this.removeAttr "checked"
        $target_radio.attr "checked", "checked"
        window.location = window.site_root_path + "account?p=#{$this.parent().data("network")}"
    })
    return false

  $(".btn-account-remove").click ->
    $this = $(@)
    vis = not $this.hasClass "visible"
    label_margin = if vis then 37 else 0
    label_speed  = if vis then 250 else 500
    action_speed = if vis then 500 else 250
    action_left  = if vis then 0 else -50
    text = if vis then $(@).data("label-visible") else $(@).data("label-hidden")

    $(".list-accounts-item").find(".account-label").animate({marginLeft: label_margin}, label_speed)
    $(".list-accounts-item").find(".account-action-delete").animate({left: action_left}, action_speed)
    $this.text text
    $this.toggleClass "visible"

  $("body").on "click", ".diff-toggle", (e) ->
    e.preventDefault()
    toggleDiff $(@)

  # This is a temporary fix for a font rendering issue.
  $(window).load ->
    delayed = -> $('body').hide().show()
    delay = window.setTimeout delayed, 1000

  # GOOG event tracking
  $("body").on "click", ".navbar .btn-signup", ->
    ga('send', 'event', 'Signup Button', 'click', 'navbar');

  $("body").on "click", ".insight-tout .btn-signup", ->
    ga('send', 'event', 'Signup Button', 'click', 'tout');

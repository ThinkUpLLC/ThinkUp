pipeToTemplate = (insight) ->
  """
  <div class="panel panel-default insight #{insight.classes}">
    <div class="panel-heading #{insight.heading_classes}">
      <h2 class="panel-title">#{insight.title}</h2>
      #{insight.subtitle}
      #{insight.header_graphic}
    </div>
    <div class="panel-body">
      #{insight.hero_image}
      #{insight.body}
    </div>
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-#{insight.network_icon} icon icon-network"></i>
        <a class="permalink" href="#">#{insight.date}</a>
      </div>
      <div class="share-menu"><a class="share-button" href="#"><i class="fa fa-share-square-o icon icon-share"></i></a></div>
    </div>
  </div>
  """

createInsight = (data) ->
  cd = data.content
  heading_classes = []
  insight_classes = []

  # Set the easy stuff
  td = template_data =
    title: cd.title
    network_icon: data.network_icon
    date: data.date

  # Conditional items
  td.subtitle = if cd.subtitle? then "<p class=\"panel-subtitle\">#{cd.subtitle}</p>" else ""
  
  if cd.header_graphic?
    hg = cd.header_graphic
    heading_classes.push "panel-heading-illustrated"
    td.header_graphic = """<img src="#{hg.asset_url}" alt="#{hg.alt_text}" width="50" height="50" class="img-circle userpic userpic-featured">"""
  else
    td.header_graphic = ""

  if cd.body.hero_image?
    td.hero_image = """<img src="#{cd.body.hero_image.asset_url}" alt="#{cd.body.hero_image.alt_text}" class="img-responsive">"""
  else
    td.hero_image = ""

  # We'll be filling this in.
  body_content = ""

  # Adding different body parts
  # This is just plain text.
  if cd.body.text?
    body_content += """<p>#{cd.body.text}</p>"""
  else
    ""

  # Buttons will always go below the text!
  if cd.body.action_button?
    body_content += """<a href="#{cd.body.action_button.url}" class="btn btn-default">#{cd.body.action_button.text}</a>"""

  # We may want some tweets
  if cd.body.tweets?
    if cd.body.tweets.length is 1
      tweet = cd.body.tweets[0]
      body_content += """
      <div class="tweet clearfix">
        <img src="#{tweet.user.profile_image_url}" alt="#{tweet.user.name}" width="60" height="60" class="img-circle pull-left">
        <p class="byline"><strong>#{tweet.user.name}</strong> <span class="username">#{tweet.user.screenname}</span></p>
        <p class="tweet-body">#{tweet.html}</p>
      </div>
      """

  # Stuffing the parts into the container
  if body_content.length
    td.body = """<div class="panel-body-inner">#{body_content}</div>"""
  else
    td.body = ""

  # Add our classes
  insight_classes.push "insight-#{data.insight_type.replace "_", "-"}"
  switch data.insight_type
    when "editorial"
      insight_classes.push "insight-hero"
    when "time_machine"
      insight_classes.push "insight-historical"

  td.heading_classes = heading_classes.join(" ")
  td.classes = insight_classes.join(" ")


  console.log td
  return pipeToTemplate td


loopThroughInsights = (insightsArray) ->
  date_string = null
  html = ""
  for insight in insightsArray
    if date_string is null
      date_string = insight.date
      html += """
      <div class="date-group date-group-today">
        <div class="date-marker">
          <div class="relative">Today</div>
          <div class="absolute">#{insight.date}, 2013</div>
        </div>
      """
      html += createInsight insight
    else if date_string is insight.date
      html += createInsight insight
    else
      date_string = insight.date
      html += """
      </div>
      <div class="date-group">
        <div class="date-marker">
          <div class="relative">Yesterday</div>
          <div class="absolute">#{insight.date}, 2013</div>
        </div>
      """
      html += createInsight insight

    console.log createInsight insight

  # Close it
  html += "</div>"

  # Add it all!
  $(".stream").append html


# These are the insights that we want to display in HTML
canonical_insights = [
  {
    insight_type: "biggest_fan"
    network_icon: "facebook-square"
    date: "Nov 12"
    content:
      title: "Jennifer is your biggest fan from the past week"
      subtitle: "She liked 23 of your status updates!"
      header_graphic:
        asset_url: "https://www.thinkup.com/join/assets/img/hilary-mason.jpg"
        alt_text: "Hillary Mason"
      body:
        hero_image: null
        text: "Let Jennifer know you appreciate her support."
        action_button:
          text: "Send message"
          url: "#"
  },
  {
    insight_type: "editorial"
    network_icon: "twitter-square"
    date: "Nov 12"
    content:
      title: "You tweeted about #snowpocalypse — need to warm up?"
      subtitle: "Your friends @billjones and @vanessegg had the best weather of anyone you talked to yesterday. Maybe ask them to tweet you a picture?"
      body:
        hero_image:
          asset_url: "http://distilleryimage0.ak.instagram.com/5603f97068cc11e29ca422000a1fb149_7.jpg"
          alt_text: "A pretty beach"
  },
  {
    insight_type: "time_machine"
    network_icon: "twitter-square"
    date: "Nov 11"
    content:
      title: "On this day last year &hellip;"
      subtitle: "This tweet you wrote got 32 favorites"
      body:
        tweets: [
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """I just called in with the <a href="https://twitter.com/search?q=%23Halo4Flu">#Halo4Flu</a>. I feel like it's gonna last all week."""
          }
        ]
  }

]

# These are our insight types and their predefined values
insightTypes =
  biggest_fan:
    theme: "default"
    is_historical: false


$ ->
  loopThroughInsights canonical_insights
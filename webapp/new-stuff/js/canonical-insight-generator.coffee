# For our globally accessible variables
wt = window.tu = {}

# INSIGHT GENERATOR
# This is temporary code that I (Matt) am using to test a variety of template
# styles and layouts. This will not be in production.

genInsightTemplate = (insight) ->
  """
  <div class="panel panel-default insight #{insight.classes}" id="#{insight.id}">
    <div class="panel-heading #{insight.heading_classes}">
      <h2 class="panel-title">#{insight.title}</h2>
      #{insight.subtitle}
      #{insight.header_graphic}
    </div>
    <div class="panel-desktop-right">
      <div class="panel-body">
        #{insight.hero_image}
        #{insight.body}
      </div>
      <div class="panel-footer">
        <div class="insight-metadata">
          <i class="fa fa-#{insight.network_icon} icon icon-network"></i>
          <a class="permalink" href="#">#{insight.date}</a>
        </div>
        <div class="share-menu">
          <a class="share-button-open" href="#"><i class="fa fa-share-square-o icon icon-share"></i></a>
          <ul class="share-services">
            <li class="share-service"><a href="#"><i class="fa fa-twitter icon icon-share"></i></a></li>
            <li class="share-service"><a href="#"><i class="fa fa-facebook icon icon-share"></i></a></li>
            <li class="share-service"><a href="#"><i class="fa fa-google-plus icon icon-share"></i></a></li>
            <li class="share-service"><a href="#"><i class="fa fa-envelope icon icon-share"></i></a></li>
          </ul>
          <a class="share-button-close" href="#"><i class="fa fa-times-circle icon icon-share"></i></a>
        </div>
      </div>
    </div>
  </div>
  """

genTweetTemplate = (tweet) ->
  """
  <blockquote class="tweet #{tweet.classes}">
    <img src="#{tweet.user.profile_image_url}" alt="#{tweet.user.name}" width="60" height="60" class="img-circle pull-left tweet-photo">
    <div class="byline"><strong>#{tweet.user.name}</strong> <span class="username">@#{tweet.user.screen_name}</span></div>
    <div class="tweet-body">#{tweet.html}</div>
    <div class="tweet-actions">
      <a href="#" class="tweet-action"><i class="fa fa-reply icon"></i></a>
      <a href="#" class="tweet-action"><i class="fa fa-retweet icon"></i></a>
      <a href="#" class="tweet-action"><i class="fa fa-star icon"></i></a>
  </blockquote>
  """

genUserTemplate = (user, network_icon) ->
  if not user.classes? then user.classes = ""
  """
  <div class="user #{user.classes}"><a href="#{user.profile_link}">
    <img src="#{user.profile_image_url}" alt="#{user.name}" class="img-circle pull-left user-photo">
    <div class="user-about">
      <div class="user-name">#{user.name} <i class="fa fa-#{network_icon} icon icon-network"></i></div>
      <div class="user-text">#{user.related_text}</div>
    </div>
  </a></div>
  """

genLinkTemplate = (link) ->
  """
  <div class="link">
    <div class="link-title"><a href="#{link.url}">#{link.title}</a></div>
    <div class="link-metadata">Posted by <a href="#{link.user.profile_url}">@#{link.user.screen_name}</a> from <a href="#{link.root_url}">#{link.site_name}</a></div>
  </div>
  """


buildInsight = (data) ->
  cd = data.content
  heading_classes = []
  insight_classes = []

  # Set the easy stuff
  td = template_data =
    title: cd.title
    network_icon: data.network_icon
    date: data.date
    id: data.id

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
    body_content += """<ul class="body-list tweet-list">""" if insightTypes[data.insight_type].is_list
    for tweet,i in cd.body.tweets
      body_content += """<li class="list-item">""" if insightTypes[data.insight_type].is_list
      tweet.classes = if insightTypes[data.insight_type].tweets.include_photo then "tweet-with-photo" else ""
      body_content += genTweetTemplate tweet
      body_content += "</li>" if insightTypes[data.insight_type].is_list
    if insightTypes[data.insight_type].is_list
      body_content += """
      </ul>
      <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all #{cd.body.tweets.length} tweets</span> <i class="fa fa-chevron-down icon"></i></button>"""

  if cd.body.people?
    if cd.body.people.length is 1
      user = cd.body.people[0]
      body_content += genUserTemplate user, td.network_icon
    if (cd.body.people.length > 1) and insightTypes[data.insight_type].is_list
      body_content += """<ul class="body-list user-list">"""
      for user,i in cd.body.people
        body_content += """<li class="list-item">"""
        body_content += genUserTemplate user, td.network_icon
        body_content += "</li>"
    if (cd.body.people.length > 1) and insightTypes[data.insight_type].is_list
      body_content += """
      </ul>
      <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all #{cd.body.people.length} people</span> <i class="fa fa-chevron-down icon"></i></button>"""

  if cd.body.links?
    if cd.body.links.length is 1
      link = cd.body.links[0]
      body_content += genLinkTemplate link
    if (cd.body.links.length > 1) and insightTypes[data.insight_type].is_list
      body_content += """<ul class="body-list link-list">"""
      for link,i in cd.body.links
        body_content += """<li class="list-item">"""
        body_content += genLinkTemplate link
        body_content += "</li>"
    if (cd.body.links.length > 1) and insightTypes[data.insight_type].is_list
      body_content += """
      </ul>
      <button class="btn btn-default btn-block btn-see-all" data-text="Actually, please hide them"><span class="btn-text">See all #{cd.body.links.length} links</span> <i class="fa fa-chevron-down icon"></i></button>"""


  # Stuffing the parts into the container
  if body_content.length
    td.body = """<div class="panel-body-inner">#{body_content}</div>"""
  else
    td.body = ""

  # Add our classes
  insight_classes.push "insight-#{data.insight_type.replace "_", "-"}"
  if it = insightTypes[data.insight_type]? and insightTypes[data.insight_type] isnt "default"
    insight_classes.push "insight-#{insightTypes[data.insight_type].theme}"
  switch data.insight_type
    when "editorial"
      insight_classes.push "insight-hero"
      insight_classes.push "insight-wide"
    when "flashback", "favorite_flashback"
      td.date = """<span class="prefix">From</span> #{cd.body.tweets[0].date}""" if cd.body.tweets?

  td.heading_classes = heading_classes.join(" ")
  td.classes = insight_classes.join(" ")

  return genInsightTemplate td


wt.loopThroughInsights = (insightsArray, callback) ->
  date_string = null
  change_count = 0
  html = ""
  for insight,i in insightsArray
    insight.id = "insight-#{i+1}"
    if date_string is null
      date_string = insight.date
      html += """
      <div class="date-group date-group-today">
        <div class="date-marker">
          <div class="relative">Today</div>
          <div class="absolute">#{insight.date}, 2013</div>
        </div>
      """
      html += buildInsight insight
    else if date_string is insight.date
      html += buildInsight insight
    else
      date_string = insight.date
      change_count++
      html += """
      </div>
      <div class="date-group">
        <div class="date-marker">
          <div class="relative">#{if change_count is 1 then "Yesterday" else "#{change_count} days ago"}</div>
          <div class="absolute">#{insight.date}, 2013</div>
        </div>
      """
      html += buildInsight insight

  # Close it
  html += "</div>"

  # Add it all!
  $(".stream").append html

  callback()


# These are the insights that we want to display in HTML
wt.canonical_insights = [
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
  }
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
  }
  {
    insight_type: "flashback"
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
            date: "Nov 11, 2012"
          }
        ]
  }
  {
    insight_type: "all_about_you"
    network_icon: "twitter-square"
    date: "Nov 11"
    content:
      title: "You mentioned yourself <strong>48 times</strong> in the last week"
      body:
        text: """That's <strong>48</strong> of @anildash's tweets using the words "I", "me", "my", "mine", or "myself", 6 more times than the week before."""
  }
  {
    insight_type: "archived_posts"
    network_icon: "twitter-square"
    date: "Nov 11"
    content:
      title: "ThinkUp captured 4 days 23 hours 20 minutes 15 seconds of your life."
      body:
        text: """ThinkUp has captured over <strong>28,600 tweets</strong> by @anildash, which really adds up if you estimate 15 seconds per tweet."""
  }
  {
    insight_type: "frequency"
    network_icon: "facebook-square"
    date: "Nov 11"
    content:
      title: "@anildash posted <strong>108 times</strong> in the past week."
      body:
        text: "That's ramping up to 8 more times than the prior week."
  }
  {
    insight_type: "favorite_flashback"
    network_icon: "twitter-square"
    date: "Nov 10"
    content:
      title: "You were quick on the fave trigger"
      subtitle: "On this day last year, you favorited 6 tweets"
      body:
        tweets: [
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """I just called in with the <a href="https://twitter.com/search?q=%23Halo4Flu">#Halo4Flu</a>. I feel like it's gonna last all week."""
            date: "Nov 10, 2012"
          }
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """Oh boy, I am a second tweet in this list of tweets!"""
            date: "Nov 10, 2012"
          }
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """Many of these public “apologies” read as “I'm sorry you found out I’m an asshole and it ruined my career. Also, my friends like me.”"""
            date: "Nov 10, 2012"
          }
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """“You’re a pretty monkey, and you know where *all* the bananas are.” <a href="https://medium.com/p/be7e772b2cb5">https://medium.com/p/be7e772b2cb5</a>"""
            date: "Nov 10, 2012"
          }
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """I just called in with the <a href="https://twitter.com/search?q=%23Halo4Flu">#Halo4Flu</a>. I feel like it's gonna last all week."""
            date: "Nov 10, 2012"
          }
          {
            user:
              name: "Matt Jacobs"
              screen_name: "capndesign"
              profile_image_url: "https://pbs.twimg.com/profile_images/14177592/twitter_bigger.jpg"
            html: """I just called in with the <a href="https://twitter.com/search?q=%23Halo4Flu">#Halo4Flu</a>. I feel like it's gonna last all week."""
            date: "Nov 10, 2012"
          }
        ]
  }
  {
    insight_type: "new_group_memberships"
    network_icon: "twitter-square"
    date: "Nov 10"
    content:
      title: """Do "<a href="http://twitter.com/jalrobinson/media-tech">media-tech</a>", "<a href="http://twitter.com/DunbarProject/design-web-design-2">design-web-design-2</a>" and "<a href="http://twitter.com/DunbarProject/marketing-digital-onli-2">marketing-digital-onli-2</a>" seem like good descriptions of @anildash?"""
      body:
        text: "Those are the 3 lists @anildash got added to this week, bringing the total to <strong>56 lists</strong>."
  }
  {
    insight_type: "response_time"
    network_icon: "twitter-square"
    date: "Nov 10"
    content:
      title: "@anildash has been getting <strong>1 new favorite</strong> every <strong>10 minutes</strong> on tweets over the last week"
      body:
        text: "That's faster than the previous week's average of 1 favorite every 11 minutes."
  }
  {
    insight_type: "link_prompt"
    network_icon: "twitter-square"
    date: "Nov 10"
    content:
      title: "@anildash hasn't tweeted a link on twitter in the last 2 days."
      body:
        text: "Maybe you've got an interesting link to share with your followers."
  }
  {
    insight_type: "biggest_fans"
    network_icon: "facebook-square"
    date: "Nov 9"
    content:
      title: "These people really get you (at least this week)"
      subtitle: "Check out the people who have liked your status updates the most over the last 7 days"
      body:
        people: [
          {
            name: "Matt Jacobs"
            profile_link: "https://facebook.com/mattyjacobs"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/371634_502783489_1338686239_q.jpg"
            related_text: "7 likes"
          }
          {
            name: "Anil Dash"
            profile_link: "https://facebook.com/anildash"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/s200x200/251806_10151280845537897_38625094_n.jpg"
            related_text: "5 likes"
          }
          {
            name: "Matt Jacobs"
            profile_link: "https://facebook.com/mattyjacobs"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/371634_502783489_1338686239_q.jpg"
            related_text: "5 likes"
          }
          {
            name: "Anil Dash"
            profile_link: "https://facebook.com/anildash"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/s200x200/251806_10151280845537897_38625094_n.jpg"
            related_text: "4 likes"
          }
          {
            name: "Matt Jacobs"
            profile_link: "https://facebook.com/mattyjacobs"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/371634_502783489_1338686239_q.jpg"
            related_text: "2 likes"
          }
          {
            name: "Anil Dash"
            profile_link: "https://facebook.com/anildash"
            profile_image_url: "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/s200x200/251806_10151280845537897_38625094_n.jpg"
            related_text: "2 likes"
          }
        ]
  }
  {
    insight_type: "favorited_links"
    network_icon: "twitter-square"
    date: "Nov 9"
    content:
      title: "In case you forgot, you liked these links"
      subtitle: "This week, you favorited 4 tweets that contained links"
      body:
        links: [
          {
            title: "12 Ways Your Cat is Ruining Your Code Review Process"
            url: "http://buzzfake.com/article.html"
            site_name: "BuzzFake"
            root_url: "http://buzzfake.com"
            user:
              screen_name: "ftrain"
              profile_link: "https://twitter.com/ftrain"
          }
          {
            title: "This Site's Insight Will Make You Weep"
            url: "http://upwordy.com/article.html"
            site_name: "Upwordy"
            root_url: "http://upwordy.com"
            user:
              screen_name: "anildash"
              profile_link: "https://twitter.com/anildash"
          }
          {
            title: "12 Ways Your Cat is Ruining Your Code Review Process"
            url: "http://buzzfake.com/article.html"
            site_name: "BuzzFake"
            root_url: "http://buzzfake.com"
            user:
              screen_name: "ftrain"
              profile_link: "https://twitter.com/ftrain"
          }
          {
            title: "This Site's Insight Will Make You Weep"
            url: "http://upwordy.com/article.html"
            site_name: "Upwordy"
            root_url: "http://upwordy.com"
            user:
              screen_name: "anildash"
              profile_link: "https://twitter.com/anildash"
          }
        ]
  }

]

# These are our insight types and their predefined values
insightTypes =
  biggest_fan:
    theme: "default"
  biggest_fans:
    theme: "default"
    is_list: true
  flashback:
    theme: "historical"
    tweets:
      include_photo: false
  editorial:
    theme: "green"
  all_about_you:
    theme: "default"
  archived_posts:
    theme: "purple"
  frequency:
    theme: "default"
  favorite_flashback:
    theme: "historical"
    is_list: true
    tweets:
      include_photo: true
  favorited_links:
    theme: "default"
    is_list: true
{if isset($smarty.get.s) eq 1 and !isset($logged_in_user)}

{assign var='tout_headline' value="You can get fun insights just like these!"}
{if ($i->filename eq 'activityspike')}
  {assign var='tout_headline' value="What's really leaving an impression online? Find out!"}
{elseif ($i->filename eq 'allaboutyou')}
  {assign var='tout_headline' value="Find out how much you mention yourself."}
{elseif ($i->filename eq 'amplifier')}
  {assign var='tout_headline' value="See which friends you're helping out on Twitter or Facebook."}
{elseif ($i->filename eq 'bestie')}
  {assign var='tout_headline' value="See who your BFF was each month on Twitter and Facebook."}
{elseif ($i->filename eq 'biggestfan')}
  {assign var='tout_headline' value="Find out who loved your stuff the most on Instagram, Facebook or Twitter."}
{elseif ($i->filename eq 'bigreshare')}
  {assign var='tout_headline' value="See who helped you reach new audiences on Twitter and Facebook."}
{elseif ($i->filename eq 'biotracker')}
  {assign var='tout_headline' value="Find out what's changed in your friends' profiles."}
{elseif ($i->filename eq 'congratscount')}
  {assign var='tout_headline' value="Are you sharing a kind word with your friends often enough? Find out!"}
{elseif ($i->filename eq 'exclamationcount')}
  {assign var='tout_headline' value="You can find out how much you're exclaiming online, too."}
{elseif ($i->filename eq 'facebookprofileprompt')}
  {assign var='tout_headline' value="We can help you keep your online profiles up to date."}
{elseif ($i->filename eq 'favoritedlinks')}
  {assign var='tout_headline' value="Want a handy list of the links you've liked? We can help!"}
{elseif ($i->filename eq 'favoriteflashbacks')}
  {assign var='tout_headline' value="Remember what you found interesting online on this day in years past."}
{elseif ($i->filename eq 'flashbacks')}
  {assign var='tout_headline' value="Get a look back at what you were doing on this day in years past."}
{elseif ($i->filename eq 'followercountvisualizer')}
  {assign var='tout_headline' value="How many school buses could your followers fill up? Find out!"}
{elseif ($i->filename eq 'frequency')}
  {assign var='tout_headline' value="Find out how much time you're spending on Facebook, Instagram &amp; Twitter each week."}
{elseif ($i->filename eq 'interestingfollowers')}
  {assign var='tout_headline' value="Get a list of your most interesting new followers."}
{elseif ($i->filename eq 'listmembership')}
  {assign var='tout_headline' value="Find out how people are describing you on Twitter and Facebook."}
{elseif ($i->filename eq 'localfollowers')}
  {assign var='tout_headline' value="See which new friends you've made in your neighborhood."}
{elseif ($i->filename eq 'locationawareness')}
  {assign var='tout_headline' value="Do you know how often you're really sharing your location? Find out."}
{elseif ($i->filename eq 'lolcount')}
  {assign var='tout_headline' value="Get more LOL out of your time online. Try ThinkUp now."}
{elseif ($i->filename eq 'longlostcontacts')}
  {assign var='tout_headline' value="Are there friends you've fallen out of touch with? ThinkUp can tell you."}
{elseif ($i->filename eq 'metapostscount')}
  {assign var='tout_headline' value="Find out what you're really sharing on your social networks."}
{elseif ($i->filename eq 'metweet')}
  {assign var='tout_headline' value="Find out how often you're retweeting things people say about you."}
{elseif ($i->filename eq 'newdictionarywords')}
  {assign var='tout_headline' value="Have you been expanding your vocabulary? ThinkUp can tell you!"}
{elseif ($i->filename eq 'outreachpunchcard')}
  {assign var='tout_headline' value="What time of day you get the biggest responses on Instagram, Twitter and Facebook?"}
{elseif ($i->filename eq 'responsetime')}
  {assign var='tout_headline' value="What really gets a response on Instagram, Twitter or Facebook? Find out!"}
{elseif ($i->filename eq 'thankscount')}
  {assign var='tout_headline' value="How thankful have you been to your friends online?"}
{elseif ($i->filename eq 'timespent')}
  {assign var='tout_headline' value="How much of your life have you spent on your social networks? Find out!"}
{elseif ($i->filename eq 'topwords')}
  {assign var='tout_headline' value="See what you're talking about the most."}
{elseif ($i->filename eq 'twitterage')}
  {assign var='tout_headline' value="Find out just how ahead of the curve you were in joining Twitter!"}
{elseif ($i->filename eq 'twitterbirthday')}
  {assign var='tout_headline' value="Join ThinkUp and you'll be celebrating your Twitter birthday, too!"}
{elseif ($i->filename eq 'weeklybests')}
  {assign var='tout_headline' value="Find out your most popular Instagram, Facebook and Twitter posts each week!"}
{/if}

{if $i->slug|strpos:'eoy_'===0}
  {assign var='tout_headline' value="What's <em>your</em> Best of 2015? Find out!"}
{/if}

<div class="panel panel-default insight insight-default insight-hero insight-wide insight-message insight-tout">
  <div class="panel-heading">
    <h2 class="panel-title">{$tout_headline}</h2>
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      <div class="panel-body-inner">
        <p><a href="https://thinkup.com/?utm_source=permalink_tout&utm_medium=banner&utm_campaign=touts" class="btn btn-signup">Try ThinkUp for Free</a></p>
      </div>
    </div>
  </div>
</div>
{/if}

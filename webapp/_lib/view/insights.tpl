{include file="_header.tpl"}
{include file="_navigation.tpl"}

<div class="container">
  {if $message_header}
    {if !isset($thinkupllc_endpoint)}
    <div class="no-insights">
    {$message_header}
    {$message_body}
    </div>
    {else}
      {include file="_insights.firstrun.tpl"}
    {/if}
  {/if}
  <div class="stream{if isset($smarty.get.s)} stream-permalink{/if}">

{assign var='cur_date' value=''}
{assign var='previous_date' value=''}
{foreach from=$insights key=tid item=i name=insights}
  {assign var='previous_date' value=$cur_date}
  {assign var='cur_date' value=$i->date}
  {capture name=permalink assign="permalink"}{$thinkup_application_url}?u={$i->instance->network_username|urlencode_network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}{/capture}
  {if $i->instance->network eq 'twitter'}
    {capture name="share_link" assign="share_link"}
      <a class="{$i->instance->network}" href="https://twitter.com/intent/tweet?related=thinkup&amp;text={$i->headline|strip_tags:true|strip|truncate:100}&amp;url={$permalink|html_entity_decode|escape:'url'}&amp;via=thinkup">Tweet this</a>
    {/capture}
  {elseif $i->instance->network eq 'facebook'}
    {capture name="share_link" assign="share_link"}
      <a class="{$i->instance->network}" href="https://www.facebook.com/sharer.php?u={$permalink|html_entity_decode|escape:'url'}">Share on Facebook</a>
    {/capture}
  {/if}

  {math equation="x % 10" x=$i->id assign=random_color_num}
  {if $i->slug eq 'posts_on_this_day_popular_flashback' | 'favorites_year_ago_flashback'}{assign var='color' value='historical'}
  {elseif $random_color_num eq '0'}{assign var='color' value='pea'}
  {elseif $random_color_num eq '1'}{assign var='color' value='creamsicle'}
  {elseif $random_color_num eq '2'}{assign var='color' value='purple'}
  {elseif $random_color_num eq '3'}{assign var='color' value='mint'}
  {elseif $random_color_num eq '4'}{assign var='color' value='bubblegum'}
  {elseif $random_color_num eq '5'}{assign var='color' value='seabreeze'}
  {elseif $random_color_num eq '6'}{assign var='color' value='dijon'}
  {elseif $random_color_num eq '7'}{assign var='color' value='sandalwood'}
  {elseif $random_color_num eq '8'}{assign var='color' value='caramel'}
  {else}{assign var='color' value='salmon'}
  {/if}

{if $previous_date neq $cur_date and !$smarty.foreach.insights.first}
    </div><!-- end date-group -->
{/if}

        {if $smarty.foreach.insights.first or ($cur_date neq $previous_date)}
    <div class="date-group{if $i->date|relative_day eq "today"} today{/if}">
        <div class="date-marker">

            {if $i->date|relative_day eq "today" }
            <div class="relative">
                {if $i->instance->crawler_last_run eq 'realtime'}Updated in realtime{else}{$i->instance->crawler_last_run|relative_datetime|ucfirst} ago{/if}
            </div>
            <div class="absolute">Today</div>
            {else}
            <div class="relative">
                {$i->date|relative_day|ucfirst}
            </div>
            <div class="absolute">{$i->date|date_format:"%b %d, %Y"}</div>
            {/if}
        </div>
        {/if}

<div class="panel panel-default insight insight-default insight-{$i->slug|replace:'_':'-'}
  {if $i->emphasis > '1'}insight-hero{/if} insight-{$color|strip} {if
  (isset($i->related_data.hero_image) and $i->emphasis > '1') | $i->slug eq 'weekly_graph'}insight-wide{/if} {if $i->slug|strstr:'eoy'}insight-eoy insight-wide{/if}" id="insight-{$i->id}">
  <div class="panel-heading{if $i->header_image neq ''} panel-heading-illustrated{/if}">
    <h2 class="panel-title">{$i->headline}</h2>
    {if ($i->slug eq 'posts_on_this_day_popular_flashback')}
    <p class="panel-subtitle">{$i->text|link_usernames_to_twitter}</p>{/if}
    {if $i->header_image neq ''}
    <img src="{$i->header_image|use_https}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
    {/if}
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      {if isset($i->related_data.hero_image)}<figure class="insight-hero-image">{if
      isset($i->related_data.hero_image.img_link)}<a href="{$i->related_data.hero_image.img_link}">{/if}
      {if isset($i->related_data.hero_image.url)}<img src="{$i->related_data.hero_image.url}" alt="{$i->related_data.hero_image.alt_text}" class="img-responsive">{/if}
      {if isset($i->related_data.hero_image.img_link)}
        <figcaption class="insight-hero-credit">{$i->related_data.hero_image.credit}</figcaption>{/if}
      {if isset($i->related_data.hero_image.img_link)}</a>{/if}</figure>{/if}
      <div class="panel-body-inner">
      {if $i->text neq '' and $i->slug neq 'posts_on_this_day_popular_flashback'}
        <p id="insight-text-{$i->id}">{$i->text|link_usernames_to_twitter}</p>{/if}

      {if $i->filename neq ''}
          {assign var='tpl_filename' value=$i->filename|cat:'.tpl'}
          <!-- including {$tpl_filename} -->
          {include file=$tpl_path|cat:$tpl_filename}
      {/if}
      </div>
    </div>
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-{$i->instance->network}-square icon icon-network"></i>
        <a class="permalink" href="{$permalink}">{$i->date|date_format:"%b %e"}</a>
      </div>
      <div class="share-menu">
        {if $i->instance->is_public eq 1}
          {$share_link}
        {else}
        <i class="fa fa-lock icon icon-share text-muted" title="This {$i->instance->network} account and its insights are private."></i>
        {/if}
      </div>
    </div>
  </div>
</div>

{/foreach}

{if isset($smarty.get.s) eq 1 and !isset($logged_in_user)}

{assign var='tout_headline' value="Get more out of the time you spend online."}
{if ($i->filename eq 'allaboutyou')}
  {assign var='tout_headline' value="Find out how much you mention yourself."}
{elseif ($i->filename eq 'amplifier')}
  {assign var='tout_headline' value="See which friends you're helping out on Twitter or Facebook."}
{elseif ($i->filename eq 'biggestfan')}
  {assign var='tout_headline' value="Find out who your biggest fans were on Facebook or Twitter."}
{elseif ($i->filename eq 'bigreshare')}
  {assign var='tout_headline' value="See who helped you reach new audiences on Twitter and Facebook."}
{elseif ($i->filename eq 'biotracker')}
  {assign var='tout_headline' value="We'll help you find out what's changed in your friends' profiles."}
{elseif ($i->filename eq 'congratscount')}
  {assign var='tout_headline' value="Are you sharing a kind word with your friends often enough? Find out!"}
{elseif ($i->filename eq 'exclamationcount')}
  {assign var='tout_headline' value="You can find out how much you're exclaiming online, too."}
{elseif ($i->filename eq 'facebookprofileprompt')}
  {assign var='tout_headline' value="We can help you keep your online profiles up to date."}
{elseif ($i->filename eq 'favoritedlinks')}
  {assign var='tout_headline' value="Want a handy list of the links you've favorited? We can help!"}
{elseif ($i->filename eq 'favoriteflashbacks')}
  {assign var='tout_headline' value="Remember what you found interesting online on this day in years past."}
{elseif ($i->filename eq 'fbombcount')}
  {assign var='tout_headline' value="How much do you curse online? Find out."}
{elseif ($i->filename eq 'flashbacks')}
  {assign var='tout_headline' value="Get a look back at what you were doing on this day in years past."}
{elseif ($i->filename eq 'followercountvisualizer')}
  {assign var='tout_headline' value="How many school buses could your followers fill up? Find out!"}
{elseif ($i->filename eq 'frequency')}
  {assign var='tout_headline' value="Find out how much time you're spending on Facebook and Twitter each week."}
{elseif ($i->filename eq 'genderanalysis')}
  {assign var='tout_headline' value="Does your Facebook activity get more responses from men or women? Find out!"}
{elseif ($i->filename eq 'interactions')}
  {assign var='tout_headline' value="Find out who you spent the most time talking to on Facebook and Twitter each week."}
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
{elseif ($i->filename eq 'olympics2014')}
  {assign var='tout_headline' value="Find out how you've connected to the most important events online."}
{elseif ($i->filename eq 'oscars2014')}
  {assign var='tout_headline' value="You'll have proof of exactly how clever your tweets are."}
{elseif ($i->filename eq 'outreachpunchcard')}
  {assign var='tout_headline' value="Find out what time of day you get the biggest responses on Twitter and Facebook."}
{elseif ($i->filename eq 'thankscount')}
  {assign var='tout_headline' value="How thankful have you been to your friends online? We'll let you know."}
{elseif ($i->filename eq 'timespent')}
  {assign var='tout_headline' value="How much of your life have you spent on your social networks? Find out!"}
{elseif ($i->filename eq 'twitterage')}
  {assign var='tout_headline' value="Find out just how ahead of the curve you were in joining Twitter!"}
{elseif ($i->filename eq 'twitterbirthday')}
  {assign var='tout_headline' value="Join ThinkUp and you'll be celebrating your Twitter birthday, too!'"}
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

    </div><!-- end date-group -->

    <div class="stream-pagination-control">
      <ul class="pager">
      {if $next_page}
        <li class="previous">
          <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="fa fa-arrow-left"></i> Older</a>
        </li>
      {/if}
      {if $last_page}
        <li class="next">
          <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="fa fa-arrow-right"></i></a>
        </li>
      {/if}
      </ul>
    </div>

  </div><!-- end stream -->
</div><!-- end container -->

{include file="_footer.tpl" linkify=1}

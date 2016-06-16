{include file="_header.tpl"}
{if !isset($share_mode)}
  {include file="_navigation.tpl" display_search_box="true"}
{/if}

<script>{literal}
var app_message = {};
app_message.msg = {/literal}"<strong>ThinkUp is closing down on July 18.</strong> Get <a href=\"https://medium.com/p/e600bc46cc56#.133usdh6u\">details &amp; refund info &raquo;</a>"{literal};
app_message.type = "info";
{/literal}</script>


<div class="container">
  {if $is_year_end}
    {if $year_end_year eq '2014'}
      {capture name=img_date assign="img_date"}
        20141223
      {/capture}
    {else}
      {capture name=img_date assign="img_date"}
      {if $smarty.now|date_format:"%Y%m%d" > 20151130 && $smarty.now|date_format:"%Y%m%d" < 20151224}
        {$smarty.now|date_format:"%Y%m%d"}
      {elseif $smarty.now|date_format:"%Y%m%d" > 20151223}
        20151223
      {else}
        20151201
      {/if}
      {/capture}
    {/if}

    <div class="stream-yearend-header">
       <h1>
         <img src="{$site_root_path}assets/img/yearend/calendar-{$img_date|strip|substr:1:8}.png" class="calendar">{if isset($thinkup_username)}{$thinkup_username}'s {else}My {/if}Best of {$year_end_year}

         <span class="share-buttons">
           <a class="btn btn-yearend" href="https://twitter.com/intent/tweet?related=thinkup&amp;text={if isset($thinkup_username)}{$thinkup_username}'s+{else}Your+{/if}Best+of+{$year_end_year}&amp;url={$thinkup_application_url}{$year_end_year}/&amp;via=thinkup"><i class="fa fa-fw fa-twitter"></i></a><a class="btn btn-yearend" href="https://www.facebook.com/sharer.php?u={$thinkup_application_url}{$year_end_year}/"><i class="fa fa-fw fa-facebook"></i></a>
         </span>
       </h1>

    </div>
  {/if}

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

  <div class="stream{if isset($smarty.get.s) && !isset($smarty.get.square)} stream-permalink{/if}{if $is_year_end} stream-yearend{/if}">

  {if $is_year_end && $tomorrows_teaser && $year_end_year eq '2015'}
    <div class="date-group">
      <div class="date-marker">
        <div class="relative">Tomorrow</div>
      </div>

      <div class="panel panel-default insight insight-hero insight-yearend-tease">
        <div class="panel-heading">
          <div class="panel-title">{$tomorrows_teaser}</div>
        </div>
      </div>
    </div>
  {/if}

{assign var='cur_date' value=''}
{assign var='previous_date' value=''}
{foreach from=$insights key=tid item=i name=insights}
  {assign var='previous_date' value=$cur_date}
  {assign var='cur_date' value=$i->date}
  {capture name=permalink assign="permalink"}{$thinkup_application_url}?u={$i->instance->network_username|urlencode_network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}{/capture}
  {if $i->instance->network eq 'twitter'}
    {capture name="share_link" assign="share_link"}
      <a class="{$i->instance->network}" href="https://twitter.com/intent/tweet?related=thinkup&amp;text={$i->headline|strip_tags:true|strip|truncate:100|urlencode}&amp;url={$permalink|html_entity_decode|escape:'url'}&amp;via=thinkup">Tweet this</a>
    {/capture}
  {elseif $i->instance->network eq 'facebook'}
    {capture name="share_link" assign="share_link"}
      <a class="{$i->instance->network}" href="https://www.facebook.com/sharer.php?u={$permalink|html_entity_decode|escape:'url'}">Share on Facebook</a>
    {/capture}
  {elseif $i->instance->network eq 'instagram' && isset($thinkupllc_endpoint)}
    {capture name="share_link" assign="share_link"}
      <a class="{$i->instance->network}" href="https://shares.thinkup.com/insight?tu={$install_folder}&u={$i->instance->network_username|urlencode_network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}&square=1&share=1">Share on Instagram</a>
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
  (isset($i->related_data.hero_image) and $i->emphasis > '1') | $i->slug eq 'weekly_graph'}insight-wide{/if}{if $i->slug|strpos:'eoy_'===0}insight-yearend insight-wide{/if}" id="insight-{$i->id}">
  {if $i->slug|strpos:'eoy_'===0}
  <div class="insight-yearend-header">
    <img src="{$site_root_path}assets/img/thinkup-logo-white.png" alt="ThinkUp" class="logo"> Best of {$i->date|date_format:"%Y"}
    <a class="btn" href="{$site_root_path}{$i->date|date_format:'%Y'}/">See more</a>
  </div>
  {/if}
  <div class="panel-heading{if $i->header_image neq ''} panel-heading-illustrated{/if}">
    <h2 class="panel-title">{$i->headline}</h2>
    {if ($i->slug eq 'posts_on_this_day_popular_flashback')}
    <p class="panel-subtitle">{$i->text|link_usernames_to_network:$i->network}</p>{/if}
    {if $i->header_image neq ''}
    <img src="{insert name='user_avatar' avatar_url=$i->header_image image_proxy_sig=$image_proxy_sig}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
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
        <p id="insight-text-{$i->id}">{$i->text|link_usernames_to_network:$i->network}</p>{/if}

      {if $i->filename neq ''}
          {assign var='tpl_filename' value=$i->filename|cat:'.tpl'}
          <!-- including {$tpl_filename} -->
          {include file=$tpl_path|cat:$tpl_filename}
      {/if}
      </div>
    </div>
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-{$i->instance->network}{if ($i->instance->network neq 'instagram')}-square{/if} icon icon-network"></i>
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

{if !isset($share_mode)}
  {include file="_insight.touts.tpl"}
{/if}

    </div><!-- end date-group -->

    <div class="stream-pagination-control">
      <ul class="pager">
      {if $next_page}
        <li class="previous">
          <a href="{$site_root_path}{if $is_year_end}{$year_end_year}/{else}insights.php{/if}?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="fa fa-arrow-left"></i> Older</a>
        </li>
      {/if}
      {if $last_page}
        <li class="next">
          <a href="{$site_root_path}{if $is_year_end}{$year_end_year}/{else}insights.php{/if}?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="fa fa-arrow-right"></i></a>
        </li>
      {/if}
      </ul>
    </div>

  </div><!-- end stream -->
</div><!-- end container -->

{include file="_footer.tpl" linkify=1}

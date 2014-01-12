{include file="_header.tpl"}
{include file="_navigation.tpl"}

<div class="container">
  <div class="stream{if count($insights) eq 1} stream-permalink{/if}">

{include file="_usermessage.tpl"}
{if $message_header}
  {$message_header}
  {$message_body}
{/if}
{assign var='cur_date' value=''}
{assign var='previous_date' value=''}
{foreach from=$insights key=tid item=i name=insights}
  {assign var='previous_date' value=$cur_date}
  {assign var='cur_date' value=$i->date}

  {math equation="x % 5" x=$i->id assign=random_color_num}
  {if $i->slug eq 'posts_on_this_day_popular_flashback' | 'favorites_year_ago_flashback'}{assign var='color' value='historical'}
  {elseif $random_color_num eq '0'}{assign var='color' value='mint'}
  {elseif $random_color_num eq '1'}{assign var='color' value='purple'}
  {elseif $random_color_num eq '2'}{assign var='color' value='orange'}
  {elseif $random_color_num eq '3'}{assign var='color' value='green'}
  {else}{assign var='color' value='red'}
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
            {if $i->emphasis >= '1'}insight-hero{/if}
            insight-{$color|strip}
            " id="insight-{$i->id}">
  <div class="panel-heading ">
    <h2 class="panel-title">{$i->headline}</h2>
    {if ($i->slug eq 'posts_on_this_day_popular_flashback' or $i->slug eq 'interactions')}
    <p class="panel-subtitle">{$i->text|link_usernames_to_twitter}</p>{/if}
    {if $i->header_image neq ''}
    <img src="{$i->header_image}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
    {/if}
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">

      <div class="panel-body-inner">
      {if $i->text neq '' and $i->slug neq 'posts_on_this_day_popular_flashback'
      and $i->slug neq 'interactions'}<p id="insight-text-{$i->id}">{$i->text|link_usernames_to_twitter}</p>{/if}

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
        <a class="permalink" href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->date|date_format:"%b %e"}</a>
      </div>
      <div class="share-menu">
        <a class="share-button-open" href="#"><i class="fa fa-share-square-o icon icon-share"></i></a>
        <ul class="share-services">
        <li class="share-service"><a href="#"><i class="fa fa-twitter icon icon-share"></i></a></li>
        <li class="share-service"><a href="#"><i class="fa fa-facebook icon icon-share"></i></a></li>
        </ul>
        <a class="share-button-close" href="#"><i class="fa fa-times-circle icon icon-share"></i></a>
      </div>
    </div>
  </div>
</div>

{/foreach}

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
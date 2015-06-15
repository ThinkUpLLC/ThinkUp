{include file="_header.tpl"}
{include file="_navigation.tpl"}

{assign var='color' value='blue'}
<div class="container">
  {if $message_header}
    <div class="no-insights">
    {$message_header}
    {$message_body}
    </div>
  {/if}
  <div class="stream {if count($instances_search_results) eq 1} stream-permalink{/if}">

    <div class="date-group{if $i->date|relative_day eq "today"} today{/if}">
        <div class="date-marker">

            <div class="relative"></div>
        </div>

{foreach from=$instances_search_results key=ir item=i name=instance_results}
<div class="panel panel-default insight insight-default insight-{$ir.instance->slug|replace:'_':'-'}
  {if $ir.instance->emphasis > '1'}insight-hero{/if} insight-{$color|strip} {if
  isset($ir.instance->related_data.hero_image) and $ir.instance->emphasis > '1'}insight-wide{/if}" id="insight-{$ir.instance->id}">
  <div class="panel-heading ">
    <h2 class="panel-title">
      {if $i.search_results|@count > 0}
        {if $i.search_results|@count == 20} {* This is a full page of followers, there may be more beyond this *}
        Lots of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers {if $i.search_results|@count eq 1}has{else}have{/if} "{$smarty.get.q|replace:'name:':''}" in their bio
        {else}
        {$i.search_results|@count} of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers {if $i.search_results|@count eq 1}has{else}have{/if} "{$smarty.get.q|replace:'name:':''}" in their bio
        {/if}
      {else}
        Hmm, no luck finding "{$smarty.get.q|replace:'name:':''}" in {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s followers bios
      {/if}
    </h2>
    <!--
    <p class="panel-subtitle">
        Here are the {if $current_page eq 1}first {$posts|@count} {/if}results
    </p>
    -->
    {if $i.instance->header_image neq ''}
    <img src="{$i.instance->header_image|use_https}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
    {/if}
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      <div class="panel-body-inner">
        {if $i.search_results|@count > 0}
          {include file=$tpl_path|cat:"_users.tpl" users=$i.search_results }
        {else}
          <p>Seems like none of of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers has "{$smarty.get.q|replace:'name:':''}" in their bio.</p>
        {/if}

        </div><!-- / panel-body-inner -->
      </div><!-- / panel-body -->
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-{$u->network}-square icon icon-network"></i>
        <a class="permalink" href="{$permalink}">{$ir.instance->date|date_format:"%b %e"}</a>
      </div>
      <div class="share-menu">
        <i class="fa fa-lock icon icon-share text-muted" title="Search results are private."></i>
      </div>
    </div>
  </div>
</div>
{/foreach}

    <div class="stream-pagination-control">

      <p class="text-muted ">Results seem incomplete? ThinkUp may not have captured your latest data.</p>

      <ul class="pager">
      {if $next_page}
        <li class="previous">
          <a href="{$site_root_path}search.php?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}{if $smarty.get.c}c={$smarty.get.c|urlencode}&amp;{/if}{if $smarty.get.q}q={$smarty.get.q|urlencode}&amp;{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="fa fa-arrow-left"></i> Older</a>
        </li>
      {/if}
      {if $last_page}
        <li class="next">
          <a href="{$site_root_path}search.php?{if $smarty.get.v}v={$smarty.get.v}&amp;{/if}{if $smarty.get.u}u={$smarty.get.u}&amp;{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&amp;{/if}{if $smarty.get.c}c={$smarty.get.c|urlencode}&amp;{/if}{if $smarty.get.q}q={$smarty.get.q|urlencode}&amp;{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="fa fa-arrow-right"></i></a>
        </li>
      {/if}
      </ul>
    </div>


  </div><!-- end stream -->
</div><!-- end container -->



{include file="_footer.tpl" linkify=1}

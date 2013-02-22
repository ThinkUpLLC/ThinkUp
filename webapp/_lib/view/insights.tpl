{include file="_header.tpl" enable_bootstrap=$enable_bootstrap}
{include file="_statusbar.tpl" enable_bootstrap=$enable_bootstrap}

    <div id="main" class="container">

    {if $logged_in_user}


        <div class="row" id="search-refine">
            <div class="span3">&nbsp;</div>
            <div class="span9">

                    <ul class="nav nav-tabs nav-stacked">
                    {foreach from=$instances key=tid item=i}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}&c=posts&q=');" href="#"><i class="icon-{$i->network}{if $i->network eq 'google+'} icon-google-plus{/if} icon-muted icon-2x"></i> Find <span class="searchterm"></span> in {if $i->network eq 'twitter'}@{/if}{$i->network_username}'s {if $i->network eq 'twitter'}tweets{elseif $i->network eq 'foursquare'}checkins{else}{$i->network|ucwords} posts{/if}</a></li>
                        {if $i->network eq 'twitter'}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}&c=followers&q=');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search @{$i->network_username}'s followers' bios for <span class="searchterm"></span></a></li>
                        {/if}
                    {/foreach}
                    </ul>

            </div>
        </div>

    {/if}

{include file="_usermessage.tpl"}


{if $message_header}

<div class="row">
    <div class="span3">&nbsp;</div>
    <div class="span9">
        <div class="page-header">
          <h1>{$message_header}</h1>
          <h2><small>{$message_body}</small></h2>
        </div>
    </div>
</div>

{/if}


{assign var='cur_date' value=''}
{foreach from=$insights key=tid item=i name=foo}
<div class="row">
    {if $i->text neq ''}
        {if $cur_date neq $i->date}
    <div class="span3">
      <div class="embossed-block">
        <ul>
          <li>
            {if $i->date|relative_day eq "today" }
                {if $i->instance->crawler_last_run eq 'realtime'}Updated in realtime{else}{$i->instance->crawler_last_run|relative_datetime|ucfirst} ago{/if}
            {else}
                {$i->date|relative_day|ucfirst}
            {/if}
          </li>
        </ul>
      </div>
    </div><!--/span3-->

            {assign var='cur_date' value=$i->date}

        {else}

    <div class="span3"></div>
        {/if}

    <div class="span9">
        {if $i->filename neq ''}
            {assign var='tpl_filename' value=$i->filename|cat:'.tpl'}
            <!-- including {$tpl_path}{$tpl_filename} -->
            {include file=$tpl_path|cat:$tpl_filename}
        {/if}
    </div><!--/span9-->
   {/if}
</div><!--/row-->
{/foreach}

<div class="row">
    <div class="span3">&nbsp;</div>
    <div class="span9">

        <ul class="pager">
        {if $next_page}
          <li class="previous">
            <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="icon-arrow-left"></i> Older</a>
          </li>
        {/if}
        {if $last_page}
          <li class="next">
            <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="icon-arrow-right"></i></a>
          </li>
        {/if}
        </ul>

    </div>
</div>
{include file="_footer.tpl"}

{include file="_header.tpl"}
{include file="_navigation.tpl"}

<div class="container">
  <div class="stream">

{include file="_usermessage.tpl"}

{if $message_header}
  {$message_header}
  {$message_body}
{/if}

{assign var='cur_date' value=''}
{assign var='previous_date' value=''}
{foreach from=$insights key=tid item=i name=foo}
        {if $previous_date neq $cur_date}
    </div><!-- end date-group -->
        {/if}
 
        {if $cur_date neq $i->date}
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
            {if $i->slug eq 'outreach_punchcard' | $i->slug eq 'interactions' | $i->emphasis eq '2'}insight-wide{/if}
            {if $i->emphasis >= '1'}insight-hero{/if}
            {if $i->emphasis >= '1'}insight-purple{/if}
            " id="insight-{$i->id}">
  <div class="panel-heading ">
    <h2 class="panel-title">{$i->headline}</h2>
    {if isset($i->header_image)}
    <img src="$i->header_image" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
    {/if}
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">

      <div class="panel-body-inner">

      {if $i->text neq ''}<p>{$i->text|link_usernames_to_twitter}</p>{/if}

      {if $i->filename neq ''}
          {assign var='tpl_filename' value=$i->filename|cat:'.tpl'}
          <!-- including {$tpl_filename} -->
          {include file=$tpl_path|cat:$tpl_filename}
      {/if}

      </div>

    </div>
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-twitter-square icon icon-network"></i>
        <a class="permalink" href="?u={$i->instance->network_username}&amp;n={$i->instance->network}&amp;d={$i->date|date_format:'%Y-%m-%d'}&amp;s={$i->slug}">{$i->date|date_format:"%b %e"}</a>
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

{assign var='cur_date' value=$i->date}
{assign var='previous_date' value=$i->date}

{/foreach}

    </div><!-- end date-group -->

  </div><!-- end stream -->
</div><!-- end container -->

<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-9">

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
</div>
{include file="_footer.tpl" linkify=1}
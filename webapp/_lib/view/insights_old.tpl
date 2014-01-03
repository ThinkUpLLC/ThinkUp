{include file="_header.tpl"}
{include file="_navigation.tpl"}

    <div id="main" class="container">

{include file="_usermessage.tpl"}

{if $message_header}

<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-9">
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
    <div class="col-md-3">
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
    </div><!--/col-md-3-->

            {assign var='cur_date' value=$i->date}

        {else}

    <div class="col-md-3"></div>
        {/if}

    <div class="col-md-9">
        {if $i->filename neq ''}
            {assign var='tpl_filename' value=$i->filename|cat:'.tpl'}
            <!-- including {$tpl_filename} -->
            {include file=$tpl_path|cat:$tpl_filename}
        {/if}
    </div><!--/col-md-9-->
   {/if}
</div><!--/row-->
{/foreach}

<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-9">

        <ul class="pager">
        {if $next_page}
          <li class="previous">
            <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="fa fa-arrow-left"></i> Older</a>
          </li>
        {/if}
        {if $last_page}
          <li class="next">
            <a href="{$site_root_path}insights.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="fa fa-arrow-right"></i></a>
          </li>
        {/if}
        </ul>

    </div>
</div>
{include file="_footer.tpl" linkify=1}
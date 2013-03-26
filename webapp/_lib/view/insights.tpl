{include file="_header.tpl" enable_bootstrap=$enable_bootstrap}
{include file="_statusbar.tpl" enable_bootstrap=$enable_bootstrap}

    <div id="main" class="container">

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

{assign var='is_start_multiple' value=false}
{assign var='is_mid_multiple' value=false}
{assign var='is_end_multiple' value=false}
{assign var='prev_filename' value=''}
{assign var='cur_date' value=''}
{foreach from=$insights key=tid item=i name=foo}

{if $i->emphasis < 2 and $i->text neq ''}
    {if $i->filename eq $prev_filename}
        {if $is_start_multiple}
            {assign var='is_mid_multiple' value=true}
        {else}
            {assign var='is_start_multiple' value=true}
            {assign var='is_mid_multiple' value=false}
        {/if}
    {else}
        {if $is_start_multiple or $is_mid_multiple}
            {assign var='is_end_multiple' value=true}
            {assign var='is_mid_multiple' value=false}
            {assign var='is_start_multiple' value=false}
        {/if}
    {/if}
{/if}

{if $cur_date neq $i->date}
    {assign var='is_start_multiple' value=false}
    {assign var='is_mid_multiple' value=false}
{/if}

{if $is_start_multiple}
    {if $is_mid_multiple}
<!--mid-more collapse-->
    {else}
<div class="row">
    <div class="span3"></div>
    <div class="span9" style="text-align:center;margin-top:-15px;margin-bottom:3px">
        <a data-toggle="collapse" data-target="#more{$i->id}"><i class="icon-reorder icon-white"></i></a>
    </div>
</div>
<!--start collapse in--> <div class="collapse in" id="more{$i->id}">
{/if}{/if}
{if $is_end_multiple}{assign var="is_end_multiple" value=false}</div> <!--end collapse in--> {/if}


<div class="row">
    {if $i->text neq ''}
        {if $cur_date neq $i->date}
        {assign var='is_start_multiple' value=false}
        {assign var='is_mid_multiple' value=false}
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
            <!-- including {$tpl_filename} -->
            {include file=$tpl_path|cat:$tpl_filename}
        {/if}

    </div><!--/span9-->
   {/if}
</div><!--/row-->
{assign var='prev_filename' value=$i->filename}
{/foreach}

{* Close up any remaining More.. divs before the paging row *}
{if $i->filename eq $prev_filename}</div> <!--end collapse in--> {/if}

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

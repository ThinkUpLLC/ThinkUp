{include file="_header.tpl" enable_bootstrap=$enable_bootstrap}
{include file="_statusbar.tpl" enable_bootstrap=$enable_bootstrap}

    <div id="main" class="container">

{if sizeof($insights) eq 0}

<div class="row">
    <div class="span3">&nbsp;</div>
    <div class="span9">
        <div class="page-header">
          <h1>ThinkUp doesn't have any insights for you yet.</h1>
          <h2><small>Check back later, or click "Capture Data".</small></h2>
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
          <div class="sidebar-nav">
            <ul class="nav nav-list">
              <li class="">
                  {if $i->date|relative_day eq "today" }
                      {if $instance->crawler_last_run eq 'realtime'}Updated in realtime{else}{$instance->crawler_last_run|relative_datetime|ucfirst} ago{/if}
                  {else}
                      {$i->date|relative_day|ucfirst}
                  {/if}
              </li>
            </ul>
          </div><!--/.well -->
    </div><!--/span3-->

            {assign var='cur_date' value=$i->date}
            
        {else}

    <div class="span3">&nbsp;</div>
        {/if}

    <div class="span9">
        <div class="alert {if $i->emphasis eq '1'}alert-info{elseif $i->emphasis eq '2'}alert-info{elseif $i->emphasis eq '3'}alert-error{else}alert-success{/if} {$i->emphasis} insight-item">
            <p>
{$i->instance->network_username} {$i->instance->network|capitalize}<br>
    <!-- begin {$i->related_data_type} attachment data -->
                {if $i->related_data_type eq 'users'}
                    {include file="_insights.users.tpl"}
                {elseif $i->related_data_type eq 'post'}
                    {include file="_insights.post.tpl" post=$i->related_data}
                {elseif $i->related_data_type eq 'posts'}
                    {include file="_insights.posts.tpl"}
                {elseif $i->related_data_type eq 'count_history'}
                    {include file="_insights.count_history.tpl"}
                {/if}
    <!--end {$i->related_data_type} attachment data-->
             </p>
        </div>
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

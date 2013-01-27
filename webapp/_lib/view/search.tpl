{include file="_header.tpl" enable_bootstrap=$enable_bootstrap}
{include file="_statusbar.tpl" enable_bootstrap=$enable_bootstrap}

    <div id="main" class="container">

{include file="_usermessage.tpl"}

<div class="row">
    <div class="span3">
      <div class="embossed-block">
        <ul>
          <li>
Search results for "{$smarty.get.q}"
          </li>
        </ul>
      </div>
    </div><!--/span3-->

    <div class="span9">
    {if $posts|@count > 0}
    {foreach from=$posts key=pid item=post name=bar}
        <div class="alert insight-item">
        {include file=$tpl_path|cat:"_post.tpl" post=$post hide_insight_header='1'}
        {include file=$tpl_path|cat:'_footer.tpl'}
    {/foreach}
    {else}
     <h2>No posts found.</h2>
    {/if}
    </div><!--/span9-->
</div><!--/row-->

<div class="row">
    <div class="span3">&nbsp;</div>
    <div class="span9">

        <ul class="pager">
        {if $next_page}
          <li class="previous">
            <a href="{$site_root_path}search.php?{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.q}q={$smarty.get.q}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page" class="pull-left btn btn-small"><i class="icon-arrow-left"></i> Older</a>
          </li>
        {/if}
        {if $last_page}
          <li class="next">
            <a href="{$site_root_path}search.php?{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.q}q={$smarty.get.q}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page" class="pull-right btn btn-small">Newer <i class="icon-arrow-right"></i></a>
          </li>
        {/if}
        </ul>

    </div>
</div>


{include file="_footer.tpl"}

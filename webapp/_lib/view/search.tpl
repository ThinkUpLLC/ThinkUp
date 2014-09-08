{include file="_header.tpl"}
{include file="_navigation.tpl"}

<div class="container">
  {if $message_header}
    <div class="no-insights">
    {$message_header}
    {$message_body}
    </div>
  {/if}
  <div class="stream{if count($insights) eq 1} stream-permalink{/if}">

    <div class="date-group{if $i->date|relative_day eq "today"} today{/if}">
        <div class="date-marker">

            <div class="relative"></div>
            <div class="absolute"></div>
        </div>

<div class="panel panel-default insight insight-default insight-{$i->slug|replace:'_':'-'}
  {if $i->emphasis > '1'}insight-hero{/if} insight-{$color|strip} {if
  isset($i->related_data.hero_image) and $i->emphasis > '1'}insight-wide{/if}" id="insight-{$i->id}">
  <div class="panel-heading ">
    <h2 class="panel-title">
        {if $smarty.get.c eq 'posts'}
          {if $posts|@count > 0}
            Are these the posts you were looking for?
          {else}
            Hmm, didn't find anything for that.
          {/if}
        {/if}
        {if $smarty.get.c eq 'followers'}
          {if $users|@count > 0}
            Looks like {$users|@count} people answer to "{$smarty.get.q}".
          {else}
            Hmm, no luck looking for "{$smarty.get.q}" people.
          {/if}
        {/if}
    </h2>
    <!--
    <p class="panel-subtitle">
        Here are the {if $current_page eq 1}first {$posts|@count} {/if}results
    </p>
    -->
    {if $i->header_image neq ''}
    <img src="{$i->header_image|use_https}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
    {/if}
  </div>
  <div class="panel-desktop-right">
    <div class="panel-body">
      <div class="panel-body-inner">
    {if $smarty.get.c eq 'posts'}
        {if $posts|@count > 0}
            {include file=$tpl_path|cat:"_posts.tpl" posts=$posts}
        {else}
         <p>Sorry, ThinkUp couldn't find anything for your search.</p>
        {/if}
    {/if}<!-- / posts -->
    {if $smarty.get.c eq 'searches'}
        {if $posts|@count > 0}
            {include file=$tpl_path|cat:"_posts.tpl" posts=$posts}
        {else}
         <p>ThinkUp couldn't find any matching results.</p>
        {/if}
    {/if}<!-- / searches -->
    {if $smarty.get.c eq 'followers'}
        {if $users|@count > 0}
          {include file=$tpl_path|cat:"_users.tpl" users=$users }
        {else}
          <p>Sorry, that search doesn't turn up any followers.</p>
        {/if}
    {/if}<!-- / followers -->

        </div><!-- / panel-body-inner -->
      </div><!-- / panel-body -->
    <div class="panel-footer">
      <div class="insight-metadata">
        <i class="fa fa-{$u->network}-square icon icon-network"></i>
        <a class="permalink" href="{$permalink}">{$i->date|date_format:"%b %e"}</a>
      </div>
      <div class="share-menu">
        <i class="fa fa-lock icon icon-share text-muted" title="Search results are private."></i>
      </div>
    </div>
  </div>
</div>



    <div class="stream-pagination-control">
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

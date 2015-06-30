{include file="_header.tpl"}
{include file="_navigation.tpl" display_search_box="true"}

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
        <div class="panel panel-default insight insight-default insight-{$ir|replace:'_':'-'} insight-{$color|strip}" id="insight-{$ir.instance->id}">
          <div class="panel-heading ">
            <h2 class="panel-title">
              {if $i.search_results|@count > 0}
                {if $i.search_results|@count == 20} {* This is a full page of followers, there may be more beyond this *}
                Lots of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers {if $i.search_results|@count eq 1}has{else}have{/if} "{$query}" in their bio
                {else}
                {$i.search_results|@count} of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers {if $i.search_results|@count eq 1}has{else}have{/if} "{$query}" in their bio
                {/if}
              {else}
                Aw, no "{$query}" here!
              {/if}
            </h2>
            {if $i.instance->header_image neq ''}
            <img src="{$i.instance->header_image|use_https}" alt="" width="50" height="50" class="img-circle userpic userpic-featured">
            {/if}
          </div>
          <div class="panel-desktop-right">
            <div class="panel-body">
              <div class="panel-body-inner">
                {if $i.search_results|@count == 20}
                  <p>Here are the 20 with the most followers:</p>
                {/if}
                {if $i.search_results|@count > 0}
                  {include file=$tpl_path|cat:"_users.tpl" users=$i.search_results }
                {else}
                  <p>Seems like none of {if $i.instance->network eq "twitter"}@{/if}{$i.instance->network_username}'s {$i.instance->network|ucfirst} followers has "{$query}" in their bio.</p>
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
        </div>

    </div><!-- /date-group -->

  </div><!-- end stream -->
</div><!-- end container -->



{include file="_footer.tpl" linkify=1}

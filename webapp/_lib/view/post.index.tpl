{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24">
  <div class="clearfix">
    
    <div class="grid_4 alpha omega" style=""> <!-- begin left nav -->
      <div id="nav">
        <ul id="top-level-sidenav">
        
        {if $post}

          <li><a href="{insert name=dashboard_link}">Dashboard</a></li>
          <li{if $smarty.get.v eq ''} class="selected"{/if}>
          <a href="?t={$post->post_id}&n={$post->network|urlencode}">Replies&nbsp;&nbsp;&nbsp;</a>
          </li>
          {if $post->reply_count_cache && $post->reply_count_cache > 1}
            <li id="grid_search_input" style="padding:10px;">
          <form id="grid_search_form" action="{$site_root_path}post">
          <input type="hidden" name="t" value="{$post->post_id}" />
          <input type="hidden" name="n" value="{$post->network}" />
            <input type="text" name="search" id="grid_search_sidebar_input" onclick="clickclear(this, 'Search')" onblur="clickrecall(this,'Search')" value="Search" style="margin-top: 3px;" size="5"/>&nbsp;<input type="submit" href="#" class="grid_search" onclick="$('#grid_search_form').submit(); return false;" value="Go">
          </form>
            </li>
<script type="text/javascript">{literal}
function clickclear(thisfield, defaulttext) {
if (thisfield.value == defaulttext) {
thisfield.value = "";
}
}

function clickrecall(thisfield, defaulttext) {
if (thisfield.value == "") {
thisfield.value = defaulttext;
}
}{/literal}
</script>
            
          {/if}
        {/if}
        {if $sidebar_menu}
          {foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
              <li{if $smarty.get.v eq $smkey} class="selected"{/if}><a href="?v={$smkey}&t={$post->post_id}&n={$post->network|urlencode}">{$sidebar_menu_item->name}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}
        {/if}
        </ul>

      </div>
    </div> <!-- end left nav -->

    <div class="thinkup-canvas round-all grid_20 alpha omega prepend_20 append_20" style="min-height:340px">
      <div class="prefix_1">

        {include file="_usermessage.tpl"}

        {if $data_template}
            {include file=$data_template}
        {else}

          {if $post}
            <div class="clearfix alert stats">

{include file="post.index._top-post.tpl" show_embed=true}

              <div class="grid_6 omega center keystats">
                <div class="big-number">
                    <h1>{$post->reply_count_cache|number_format}</h1>
                    <h3>{if $post->reply_count_cache eq 1}reply{else}replies{/if}
                    
                     in {$post->adj_pub_date|relative_datetime}</h3>

{if !$post->is_protected}
<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>

   <a href="https://twitter.com/share" class="twitter-share-button"
      data-via="thinkupapp"
      data-text="{$post->post_text|strip_tags}"
      data-related="thinkupapp,expertlabs,ginatrapani"
      data-count="none">Tweet</a>
{literal}

<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>

<g:plusone size="medium" annotation="none"></g:plusone>
{/literal}
{/if}
                </div>
              </div>
            </div> <!-- /.clearfix -->
          {/if} <!-- end if post -->
            {if $disable_embed_code != true}
                <div class="alert stats" style="display:none;" id="embed-this-thread">
                <div style="float: right; margin: 0px 10px 0px 0px;">
                <a href="javascript:;" title="Embed this thread"
                onclick="$('#embed-this-thread').hide(); return false;">
                <span class="ui-icon ui-icon-circle-close"></span></a>
                </div>

                <h6>Embed this thread:</h6>
                <textarea cols="55" rows="2" id="txtarea" onClick="SelectAll('txtarea');">&lt;script src=&quot;http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.SERVER_NAME}{$site_root_path}api/embed/v1/thinkup_embed.php?p={$smarty.get.t}&n={$smarty.get.n|urlencode}&quot;>&lt;/script></textarea>
                </div>
                {literal}
                <script type="text/javascript">
                function SelectAll(id) {
                document.getElementById(id).focus();
                document.getElementById(id).select();
                }
                </script>
                {/literal}
            {/if}
          
          {if $replies}
            <div class="prepend">
              <div class="append_20 clearfix">
                {include file="_post.word-frequency.tpl"}
                {if $replies}
                    {include file="_grid.search.tpl" version2=true}
                {/if}
                <div id="post-replies-div" class="section"{if $search_on} style="display: none;"{/if}>
                    <h2>Replies</h2>
                  <div id="post_replies clearfix alert stats">
                  {foreach from=$replies key=tid item=t name=foo}
                    {include file="_post.author_no_counts.tpl" post=$t scrub_reply_username=true}
                  {/foreach}
                
                  </div>
                </div>
                <script src="{$site_root_path}assets/js/extlib/Snowball.stemmer.min.js" type="text/javascript"></script>
                {if $search_on}<script type="text/javascript">grid_search_on = true</script>{/if}
                <script src="{$site_root_path}assets/js/word_frequency.js" type="text/javascript"></script>
                {if !$logged_in_user && $private_reply_count > 0}
                <div class="stream-pagination">
                  <span style="font-size:12px">Not showing {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if}.</span>
                </div>
                {/if}
              </div>
            </div>
          {/if}
        {/if}
          
      </div> <!-- /.prefix_1 -->
    </div> <!-- /.thinkup-canvas -->
  </div> <!-- /.clearfix -->
</div> <!-- /.container_24 -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  {if $replies}
    <script type="text/javascript">post_username = '{$post->author_username}';</script>
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
  {/if}
{include file="_footer.tpl"}

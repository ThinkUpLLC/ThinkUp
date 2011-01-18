{include file="_header.tpl"}
{include file="_statusbar.tpl"}

{literal}
<script type="text/javascript">
$(document).ready(function() {
    
     //Default Action
     $(".tab_content").hide(); //Hide all content
     $("ul.tabs li:first").addClass("active").show(); //Activate first tab
     $(".tab_content:first").show(); //Show first tab content
    
     //On Click Event
     $("ul.tabs li").click(function() {
     $("ul.tabs li").removeClass("active"); //Remove any "active" class
     $(this).addClass("active"); //Add "active" class to selected tab
     $(".tab_content").hide(); //Hide all tab content
     var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
     $(activeTab).fadeIn(); //Fade in the active content
     return false;
     });
});
</script>
{/literal}

<div class="thinkup-canvas round-all container_24">
<div class="clearfix prepend_20 append_20">
<div class="clearfix prefix_1 suffix_1">
{include file="_usermessage.tpl"}
<div class="clearfix append_20"> <!-- POST DETAILS -->
{include file="_post_detail.tpl"}
</div> <!-- end .clearfix -->

</div>
</div>
</div>

<!-- TABS -->
<div class="container_24 prepend_20">
<div class="clearfix post-stats prefix_1 suffix_1">
<ul class="tabs">
<li><div class="grid_5 center">
<a href="#tab1"><div class="round-tl round-tr tab-button">
<h1>{$post->reply_count_cache|number_format}</h1>
Repl{if $post->reply_count_cache == 1}y{else}ies{/if}
</div></a>
</div></li>
<li><div class="grid_5 center">
<a href="#tab2"><div class="round-tl round-tr tab-button">
<h1>{$retweets|@count|number_format}|{$retweet_reach|number_format}</h1>
Forwards &amp; Reach
</div></a>
</div></li>
<li><div class="grid_5 center">
<a href="#tab3"><div class="round-tl round-tr tab-button">
<h1>All</h1>
Search, Filter &amp; Export
</div></a>
</div></li>
</ul>
</div>
</div>

<div class="thinkup-canvas round-all container_24">
<div class="clearfix prepend_20 append_20">

<div class="tab_container prefix_1 suffix_1">

<!-- TABS CONTENT -->
<div id="tab1" class="tab_content">
<!-- REPLIES -->
{if $replies}
<div class="append_20 clearfix">
{foreach from=$replies key=tid item=t name=foo}
{include file="_post_full.tpl" t=$t sort='no' scrub_reply_username=true activity='false'}
{/foreach}
{if !$logged_in_user && $private_reply_count > 0}
<span style="font-size:12px">Not showing {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if}.</span>
{/if}
</div>
{/if}
</div>
<div id="tab2" class="tab_content">
<!-- FORWARDS -->
{if $retweets}
<div class="append_20 clearfix">
{foreach from=$retweets key=tid item=t name=foo}
{include file="_post_full.tpl" t=$t sort='no' scrub_reply_username=false activity='false'}
{/foreach}
</div>
{/if}
</div>
<div id="tab3" class="tab_content">
<div class="clearfix">
{if $replies && $logged_in_user}
<a href="#" class="tt-button ui-state-default tt-button-icon-left ui-corner-all" onclick="return false;" id="grid_search_icon">
<span class="ui-icon ui-icon-search"></span>
Search
</a>
{/if}
<a href="{$site_root_path}post/export.php?u={$post->author_username}&n={$post->network}&post_id={$post->post_id}&type=replies" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
<span class="ui-icon ui-icon-disk"></span>
Export
</a>
</div>
<iframe id="grid_iframe" src="/thinkup/assets/img/ui-bg_glass_65_ffffff_1x400.png" frameborder="0" scrolling="no"></iframe>
</div>
</div> <!-- .tab_container -->

</div>
</div> <!-- end .thinkup-canvas -->

<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
{if $replies && $logged_in_user}
{include file="_grid.search.tpl"}
<script type="text/javascript">post_username = '{$post->author_username}';</script>
<script type="text/javascript">var GS_NO_OVERLAY = true;</script>
<script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
{/if}
{include file="_footer.tpl"}
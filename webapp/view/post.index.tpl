{include file="_header.tpl" load="no" title="Post detail"}

<div class="container_24">

  <div role="application" id="tabs">
    
    <ul>
      <li><a href="#posts">Post</a></li>
      {if $retweets}<li><a href="#forwards">Forwards</a></li>{/if}
      {if $likely_orphans}<li><a href="#replies">Likely Replies</a></li>{/if}
      {if $replies}<li><a href="#followers">Public/Republishable Replies</a></li>{/if}
    </ul>
    
    <div class="section" id="posts">
      <div class="thinkup-canvas clearfix">
        <!--<a {if $instance}href="{$site_root_path}?u={$instance->twitter_username}">{else}href="#" onClick="history.go(-1)">{/if}&larr; back</a>-->
        <div class="clearfix prepend_20">
          {include file="_usermessage.tpl"}
          <div class="grid_2 prefix_1 alpha">
            <img src="{$post->author_avatar}" class="avatar2">
          </div>
          <div class="grid_20 omega">
            <h1 class="post">
              {if $post->post_text}
                {$post->post_text}
              {else}
                <span class="no-post-text">No post text</span>
              {/if}
            </h1>
          </div>
        </div>
        <div class="clearfix append_20">
          <div class="grid_11 prefix_11 alpha omega small gray">
            <img src="{$site_root_path}assets/img/social_icons/{$post->network}.png" class="float-l">
            Posted {$post->adj_pub_date|relative_datetime} at {$post->adj_pub_date} via {$post->source}<br>
            From: {$post->location}
            {if $post->is_geo_encoded eq 1}
              <div>
              <a href="{$site_root_path}map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon">
              </a>
              </div>
            {/if}
          </div>
        </div>
        <div class="grid_1 alpha">&nbsp;</div>
        <div class="grid_23 omega append_20">
          {if $replies}
            <div>
              <h2 class="subhead">
              {if $post->reply_count_cache eq 1}Reply{else}{$post->reply_count_cache} Replies{/if}
              ({$private_reply_count} private)
              </h2>
              <div class="sort_links right">
                <a href="#" id="sortOutreachReplies" class="bold">Sort by Reach</a> | 
                <a href="#" id="sortProximityReplies">Sort by Proximity</a>
                </div>
            </div>
            <br>
          {/if}
          {foreach from=$replies key=tid item=t name=foo}
            <div class="clearfix default_replies">
              {include file="_post.other.tpl" t=$t}
                <div id="locationReplies">
                <div id="div{$t->post_id}" class="grid_22 prefix_10
                {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}
                {else}__NULL__{/if}">
                  <form action="" class="post-setparent">
                  <select name="pid{$t->post_id}" id="pid{$t->post_id}">
                    <option value="0">No Post in Particular (Mark as standalone)</option>
                    {assign var='current_post_selected' value='false'}
                    <option disabled>Set as a reply to:</option>
                    {foreach from=$all_tweets key=aid item=a}
                      <option value="{$a->post_id}" {if $a->post_id == $post->post_id} selected="true"{/if}>
                        &nbsp;&nbsp;{$a->post_text|truncate_for_select}
                      </option>
                      {if $a->post_id == $post->post_id}{assign var='current_post_selected' value='true'}{/if}
                    {/foreach}
                    {if $current_post_selected != 'true'}
                      <option value="{$post->post_id}" selected="selected">
                        &nbsp;&nbsp;{$post->post_text|truncate_for_select}
                      </option>
                    {/if}
                  </select>  
                  <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                </form>
              </div>
            </div>
            </div>
          {/foreach}
          {foreach from=$replies_by_location key=tid item=t name=foo}
            <div class="clearfix sort_replies" style="display:none">
              {include file="_post.other.tpl" t=$t}
                <div id="locationReplies">
                <div id="div{$t->post_id}" class="grid_22 prefix_10
                {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}
                {else}__NULL__{/if}">
                  <form action="" class="post-setparent">
                  <select name="pid{$t->post_id}" id="pid{$t->post_id}">
                    <option value="0">No Post in Particular (Mark as standalone)</option>
                    {assign var='current_post_selected' value='false'}
                    <option disabled>Set as a reply to:</option>
                    {foreach from=$all_tweets key=aid item=a}
                      <option value="{$a->post_id}" {if $a->post_id == $post->post_id} selected="true"{/if}>
                        &nbsp;&nbsp;{$a->post_text|truncate_for_select}
                      </option>
                      {if $a->post_id == $post->post_id}{assign var='current_post_selected' value='true'}{/if}
                    {/foreach}
                    {if $current_post_selected != 'true'}
                      <option value="{$post->post_id}" selected="selected">
                        &nbsp;&nbsp;{$post->post_text|truncate_for_select}
                      </option>
                    {/if}
                  </select>  
                  <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                </form>
              </div>
            </div>
            </div>
          {/foreach}
        </div>
        <div class="append prepend clearfix" style="margin-left:30px">
          <a href="#" class="show_replies tt-button ui-state-default tt-button-icon-left ui-corner-all "
             style="display:none;">
            <span class="ui-icon ui-icon-circle-arrow-w"></span>
            Show All Replies
          </a>
        </div>
      </div> <!-- end .thinkup-canvas -->
    </div> <!-- end #posts -->
    
    {if $retweets}
      <div class="section" id="forwards">
        <div class="thinkup-canvas clearfix">
          <div class="clearfix prepend_20 append_20">
            <div class="grid_2 prefix_1 alpha">
              <img src="{$post->author_avatar}" class="avatar2">
            </div>
            <div class="grid_13">
              <h1 class="post">
                {if $post->post_text}
                  {$post->post_text}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              </h1>
              {if $post->is_geo_encoded eq 1}
              <div class="small gray right" style="margin-right:55px">
              {else}
              <div class="small gray right">
              {/if}
                Posted {$post->adj_pub_date|relative_datetime} at {$post->pub_date} via {$post->source} <br>
            From: {$post->location}
              </div>
              {if $post->is_geo_encoded eq 1}
              <div>
              <a href="{$site_root_path}map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon" style="margin-right:0px">
              </a>
              </div>
            {/if}
            </div>
            <div class="grid_7 center big-number omega">
              <div class="bl">
                <div class="key-stat">
                  <h1>{$retweet_reach|number_format}</h1>
                  <h3>forwards to followers</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="grid_22 prefix_1 alpha omega">
            <h2 class="subhead">Forwards</h2>
            <div class="sort_links right">
                <a href="#" id="sortOutreachRetweets" class="bold">Sort by Reach</a> | 
                <a href="#" id="sortProximityRetweets">Sort by Proximity</a>
                </div>
            <br>
            {foreach from=$retweets key=tid item=t name=foo}
              <div class="clearfix default_retweets">
                {include file="_post.other.tpl" t=$t}
                <div id="locationRetweets">
                  <div id="div{$t->post_id}" class="grid_22 prefix_10
                  {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}
                  {else}__NULL__{/if}">
                  <form action="" class="post-setparent">
                    <select name="pid{$t->post_id}" id="pid{$t->post_id}">
                      <option value="0">No Post in Particular (Mark as standalone)</option>
                      {assign var='current_post_selected' value='false'}
                      <option disabled>Set as a reply to:</option>
                      {foreach from=$all_tweets key=aid item=a}
                        <option value="{$a->post_id}" {if $a->post_id == $post->post_id} selected="true"{/if}>
                          &nbsp;&nbsp;{$a->post_text|truncate_for_select}
                        </option>
                        {if $a->post_id == $post->post_id}{assign var='current_post_selected' value='true'}{/if}
                      {/foreach}
                      {if $current_post_selected != 'true'}
                        <option value="{$post->post_id}" selected="selected">
                          &nbsp;&nbsp;{$post->post_text|truncate_for_select}
                        </option>
                      {/if}
                    </select>  
                    <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                  </form>
                </div>
              </div>
              </div>
            {/foreach}
            {foreach from=$retweets_by_location key=tid item=t name=foo}
              <div class="clearfix sort_retweets" style="display:none">
                {include file="_post.other.tpl" t=$t}
                <div id="locationRetweets">
                  <div id="div{$t->post_id}" class="grid_22 prefix_10
                  {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}
                  {else}__NULL__{/if}">
                  <form action="" class="post-setparent">
                    <select name="pid{$t->post_id}" id="pid{$t->post_id}">
                      <option value="0">No Post in Particular (Mark as standalone)</option>
                      {assign var='current_post_selected' value='false'}
                      <option disabled>Set as a reply to:</option>
                      {foreach from=$all_tweets key=aid item=a}
                        <option value="{$a->post_id}" {if $a->post_id == $post->post_id} selected="true"{/if}>
                          &nbsp;&nbsp;{$a->post_text|truncate_for_select}
                        </option>
                        {if $a->post_id == $post->post_id}{assign var='current_post_selected' value='true'}{/if}
                      {/foreach}
                      {if $current_post_selected != 'true'}
                        <option value="{$post->post_id}" selected="selected">
                          &nbsp;&nbsp;{$post->post_text|truncate_for_select}
                        </option>
                      {/if}
                    </select>  
                    <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                  </form>
                </div>
              </div>
              </div>
            {/foreach}
          </div>
          <div class="append prepend clearfix" style="margin-left:30px">
            <a href="#" class="show_forwards tt-button ui-state-default tt-button-icon-left ui-corner-all "
                style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Forwards
            </a>
          </div>
        </div>
      </div>
    {/if}
    
    {if $likely_orphans}
      <div class="section" id="replies">
        <div class="thinkup-canvas clearfix">
          <div class="clearfix prepend_20 append_20">
            <div class="grid_2 prefix_1 alpha">
              <img src="{$post->author_avatar}" class="avatar2">
            </div>
            <div class="grid_13">
              <h1 class="post">
                {if $post->post_text}
                  {$post->post_text}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              </h1>
            </div>
            <div class="grid_7 center big-number omega">
              <div class="bl">
                <div class="key-stat">
                  <h1>{$retweet_reach|number_format}</h1>
                  <h3>forwards to followers</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="grid_1 alpha">&nbsp;</div>
          <div class="grid_23 omega append_20">
            <h2 class="subhead">Possible replies posted near the time of this update</h2>
            {foreach from=$likely_orphans key=tid item=t name=foo}
              <div class="clearfix">
                {include file="_post.other.tpl" t=$t}
                <div id="div{$t->post_id}" class="grid_22 prefix_10">
                  <form action="" class="post-setparent">
                    <select name="pid{$t->post_id}" id="pid{$t->post_id}">
                      <option value="0">No Post in Particular (Mark as standalone)</option>
                      {assign var='current_post_selected' value='false'}
                      <option disabled>Set as a reply to:</option>
                      {foreach from=$all_tweets key=aid item=a}
                        <option value="{$a->post_id}" {if $a->post_id == $post->post_id} selected="true"{/if}>
                          &nbsp;&nbsp;{$a->post_text|truncate_for_select}
                        </option>
                        {if $a->post_id == $post->post_id}{assign var='current_post_selected' value='true'}{/if}
                      {/foreach}
                      {if $current_post_selected != 'true'}
                        <option value="{$post->post_id}" selected="selected">
                          &nbsp;&nbsp;{$post->post_text|truncate_for_select}
                        </option>
                      {/if}
                    </select>  
                    <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                  </form>
                </div>
              </div>
            {/foreach}
          </div>
        </div>
      </div>
    {/if}
    
    {if $replies}
      <div class="section" id="followers">
        <div class="thinkup-canvas clearfix">
          <div class="clearfix prepend_20 append_20">
            <div class="grid_2 prefix_1 alpha">
              <img src="{$post->author_avatar}" class="avatar2">
            </div>
            <div class="grid_17 omega">
              <h1 class="post">
                {if $post->post_text}
                  {$post->post_text}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              </h1>
            </div>
          </div>
          <div class="grid_1 alpha">&nbsp;</div>
          <div class="grid_23 omega append_20">
            {foreach from=$replies key=tid item=t}
              <div class="reply">
                <strong>{if $t->is_protected}Anonymous{else}{$t->author_username}{/if} says: </strong>
                "{$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+ /":""}"
              </div>
            {/foreach}
          </div>
        </div>
      </div>
    {/if}
    
  </div>
</div> <!-- end .container_24 -->

<script type="text/javascript" src="{$site_root_path}assets/js/easytooltip.js"></script>
<script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/locationfilter.js"></script>
<script type="text/javascript">
  {literal}
  $(function() {
    // Begin reply assignment actions.
    $(".button").click(function() {
      var element = $(this);
      var Id = element.attr("id");
      var oid = Id;
      var pid = $("select#pid" + Id + " option:selected").val();
      var u = '{/literal}{$instance->network_username}{literal}';
      var t = 'post.index.tpl';
      var ck = '{/literal}{$post->post_id}{literal}';
      var dataString = 'u=' + u + '&pid=' + pid + '&oid[]=' + oid + '&t=' + t + '&ck=' + ck;
      $.ajax({
        type: "GET",
        url: "{/literal}{$site_root_path}{literal}post/mark-parent.php",
        data: dataString,
        success: function() {
          $('#div' + Id).html("<div class='success' id='message" + Id + "'></div>");
          $('#message' + Id).html("<p>Saved!</p>").hide().fadeIn(1500, function() {
            $('#message' + Id);
          });
        }
      });
      return false;
    });
  });
  {/literal}
</script>

{include file="_footer.tpl" stats="no"}
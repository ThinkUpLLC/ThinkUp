{include file="_public.header.tpl"}
{include file="_public.header.statusbar.tpl" mode="public"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {include file="_usermessage.tpl"}
        {if $post}
          <div class="clearfix">
            <div class="grid_2 alpha">
            <div class="avatar-container">
              <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
             </div>
            </div>
            <div class="{if $replies or $retweets}grid_13{else}grid_19{/if}">
              <span class="tweet">
                {if $post->post_text}
                  {$post->post_text|link_usernames_to_twitter}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              </span>
              {if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
                <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">
                  {$post->link->expanded_url}
                </a>
              {/if}
              <div class="grid_10 omega small gray {if $replies or $retweets}prefix_3 prepend{else}prefix_10{/if}">
                <img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" class="float-l">
                Posted at {$post->adj_pub_date}{if $post->source} via {$post->source}{/if}<br>
                {if $post->location}From: {$post->location}{/if}
                {if $post->is_geo_encoded eq 1}
                <div>
                <a href="{$site_root_path}map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                  <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
                </a>
                </div>
                {/if}
              </div>
            </div>
            <div class="grid_7 center big-number omega">
              <div class="bl">
                <div class="key-stat">
                  {if $replies}
                    <h1>{$post->reply_count_cache|number_format}</h1>
                    <h3>replies in {$post->adj_pub_date|relative_datetime} (<a href="{$site_root_path}post/export.php?u={$post->author_username}&n={$post->network}&post_id={$post->post_id}&type=replies">CSV</a>)</h3>
                  {else}
                    <h1><a href="#fwds" name="fwds">{$retweets|@count|number_format}</a>
                    fwds to<br><a href="#fwds">{$retweet_reach|number_format}</a></h1>
                    <h3>total reach</h3>
                  {/if}
                </div>
              </div>
            </div>
          </div> <!-- end .clearfix -->
          {if $replies}
            <div class="append_20 clearfix"><br />
              {foreach from=$replies key=tid item=t name=foo}
                {include file="_post.tpl" t=$t sort='no' scrub_reply_username=true}
              {/foreach}
              {if !$logged_in_user && $private_reply_count > 0}
              <span style="font-size:12px">Not showing {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if}.</span>
              {/if}
              
            </div>
          {/if}
          <div class="append prepend clearfix">
            <a href="#" class="show_replies tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Replies
            </a>
          </div>
          <div class="clearfix">
            <div class="{if $retweets}grid_13{else}grid_19{/if}">
              <span class="tweet"></span>
              <div class="grid_10 omega small gray {if $retweets}prefix_3 prepend{else}prefix_10{/if}"></div>
            </div>
            
            {if $retweets and $replies|@count > 0}
              <div class="grid_7 center big-number omega">
                <div class="bl">
                  <div class="key-stat">
                    <h1><a href="#fwds" name="fwds">{$retweets|@count|number_format}</a>
                    fwds to<br /> <a href="#fwds">{$retweet_reach|number_format}</a></h1>
                    <h3>total reach</h3>
                  </div>
                </div>
              </div>
            {/if}
          </div> <!-- end .clearfix -->
          {if $retweets}
            <div class="append_20 clearfix">
              {foreach from=$retweets key=tid item=t name=foo}
                {include file="_post.public.tpl" t=$t sort='no' scrub_reply_username=false}
              {/foreach}
            </div>
          {/if}
          <div class="append prepend clearfix">
            <a href="#" class="show_forwards tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Forwards
            </a>
          </div>
        <div class="append prepend clearfix">
          <a href="{$site_root_path}index.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
            <span class="ui-icon ui-icon-circle-arrow-w"></span>
            Back home
          </a>
        </div>
        {else}
          &nbsp;
        {/if}
      </div>
    </div>
  </div> <!-- end .thinkup-canvas -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  
{include file="_footer.tpl" stats="no"}
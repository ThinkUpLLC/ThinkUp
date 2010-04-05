<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>ThinkTank Public Timeline</title>
  <link rel="shortcut icon" href="{$cfg->site_root_path}favicon.ico">
  <link type="text/css" href="{$cfg->site_root_path}cssjs/jquery-ui-1.7.1.custom.css" rel="stylesheet">
  <link type="text/css" href="{$cfg->site_root_path}cssjs/style.css" rel="stylesheet">
</head>

<body>
  {include file="_header.login.tpl" mode="public"}
  
  <div class="thinktank-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {if $post and ($replies OR $retweets)}
          <div class="append prepend clearfix">
            <a href="{$site_root}public.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Back to the public timeline
            </a>
          </div>
          <div class="clearfix">
            <div class="grid_2 alpha">
              <img src="{$post->author_avatar}" class="avatar2">
            </div>
            {if $retweets}<div class="grid_13">{else}<div class="grid_19">{/if}
              <span class="tweet">{$post->post_text|link_usernames_to_twitter}</span>
              {if $retweets}<div class="grid_10 prefix_3 omega small gray prepend">{else}<div class="grid_10 prefix_10 omega small gray">{/if}
                <img src="{$cfg->site_root_path}/cssjs/images/social_icons/{$post->network}.png" class="float-l">
                Posted {$post->adj_pub_date|relative_datetime} at {$post->adj_pub_date} via {$post->source}
              </div>
            </div>
            {if $retweets}
              <div class="grid_7 center big-number omega">
                <div class="bl">
                  <div class="key-stat">
                    <h1>{$rtreach|number_format}</h1>
                    <h3>retweets to followers</h3>
                  </div>
                </div>
              </div>
            {/if}
          </div> <!-- end .clearfix -->
          
          {if $replies}
            <div class="append_20 clearfix">
              {foreach from=$replies key=tid item=t name=foo}
                {include file="_post.public.tpl" t=$t sort='no'}
              {/foreach}
            </div>
          {/if}
          
          {if $retweets}
            <div class="append_20 clearfix">
              {foreach from=$retweets key=tid item=t name=foo}
                {include file="_post.public.tpl" t=$t sort='no'}
              {/foreach}
            </div>
          {/if}
        {else}
          &nbsp;
        {/if}
        
        {if $posts}{include file="_pagination.tpl"}{/if}
        
        {if $header}<h1>{$header}</h1>{/if}
        {if $description}<h4>{$description}</h4>{/if}
        
        {if $posts}
          {foreach from=$posts key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t}
          {/foreach}
          {include file="_pagination.tpl"}
        {/if}
      </div>
    </div>
  </div> <!-- end .thinktank-canvas -->

  <script type="text/javascript" src="{$cfg->site_root_path}cssjs/linkify.js"></script>

  {include file="_footer.tpl" stats="no"}
</body>

</html>

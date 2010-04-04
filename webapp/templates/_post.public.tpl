{if $smarty.foreach.foo.first}
  <div class="header clearfix"> 
        <div class="grid_1 alpha">&nbsp;</div> 
        <div class="grid_3 right">name</div> 
        <div class="grid_3 right">{if $sort eq 'no'}date{else}<a href="{$cfg->site_root_path}public.php">date</a>{/if}</div>
        <div class="grid_7">post</div>
        <div class="grid_2">{if $sort eq 'no'}&nbsp;{else}<a href="{$cfg->site_root_path}public.php?v=photos">w/ photos</a>{/if}</div>
        <div class="grid_2">{if $sort eq 'no'}&nbsp;{else}<a href="{$cfg->site_root_path}public.php?v=links">w/ links</a>{/if}</div> 
        <div class="grid_2 center">{if $sort eq 'no'}replies{else}<a href="{$cfg->site_root_path}public.php?v=mostreplies">replies</a>{/if}</div> 
        <div class="grid_2 center omega">{if $sort eq 'no'}forwards{else}<a href="{$cfg->site_root_path}public.php?v=mostretweets">forwards</a>{/if}</div>
        <!--
        <div class="grid_1 center">{if $sort eq 'no'}photo{else}<a href="{$cfg->site_root_path}public.php?v=photos">photo</a>{/if}</div>
        <div class="grid_1 center omega">{if $sort eq 'no'}link{else}<a href="{$cfg->site_root_path}public.php?v=links">link</a>{/if}</div> 
        -->
    </div> 
{/if}

<div class="individual-tweet post clearfix">
    <div class="grid_1 alpha">
        <img src="{$t->author_avatar}" class="avatar">
    </div>
    <div class="grid_3 right small">
        {$t->author_username}
        {if $t->author->follower_count > 0}<br />{$t->author->follower_count|number_format} followers{/if}
    </div>
    <div class="grid_3 right small">
        {$t->adj_pub_date|relative_datetime}
    </div>
    <div class="grid_11">
    {if $t->link->is_image}<div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>{/if}

    <p>{$t->post_text|link_usernames_to_twitter} {if $t->in_reply_to_post_id && $smarty.session.user }[<a href="{$cfg->site_root_path}post/?t={$t->in_reply_to_post_id}">in reply to</a>]{/if}</p>
    
    {if $t->link->expanded_url and !$t->link->is_image}<ul><li><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a></li></ul>{/if}
    
    {if $t->author->location}<div class="small gray">Location: {$t->author->location}</div>{/if}
    </div>
    <div class="grid_2 center">
    {if $t->mention_count_cache > 0}<span class="reply-count"><a href="{$site_root}public.php?t={$t->post_id}">{$t->mention_count_cache}<!-- repl{if $t->mention_count_cache eq 1}y{else}ies{/if}--></a></span>{else}&nbsp;{/if} 
    </div>
    <div class="grid_2 center omega">
    {if $t->retweet_count_cache > 0}<span class="reply-count"><a href="{$site_root}public.php?t={$t->post_id}">{$t->retweet_count_cache}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>{else}&nbsp;{/if} 
  </div>
  <!--
  <div class="grid_1 center">{if $t->link->is_image}x{else}&nbsp;{/if}</div>
  <div class="grid_1 center omega">&nbsp;</div>
  -->
    
</div>

{if $smarty.foreach.foo.first}
<div class="header clearfix">
    <div class="grid_1 alpha">
        &nbsp;
    </div>
    <div class="grid_3 right">
        name
    </div>
    <div class="grid_3 right">
        followers
    </div>
    <div class="grid_3 right">
        date
    </div>
    <div class="grid_12 omega">
        post
    </div>
</div>
{/if}
<div class="individual-tweet clearfix{if $t->is_protected} private{/if}">
    <div class="grid_1 alpha">
        <a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->network_username}"><img src="{$t->author_avatar}" width="48" height="48" class="avatar"></a>
    </div>
    <div class="grid_3 right small">
        <a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->network_username}">{$t->author_username}</a>
    </div>
    <div class="grid_3 right small">
        {$t->author->follower_count|number_format}
    </div>
    <div class="grid_3 right small">
        <a href="{$cfg->site_root_path}post/?t={$t->post_id}">{$t->adj_pub_date|relative_datetime}</a>
    </div>
    <div class="grid_12 omega">
        <div class="tweet-body">
            {if $t->link->is_image}<div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>{/if}
            <p>
                {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}{if $t->in_reply_to_post_id} <a href="{$cfg->site_root_path}post/?t={$t->in_reply_to_post_id}">in reply to</a>
                {/if}
            </p>
            {if $t->link->expanded_url}<a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a>{/if}
            {if $t->author->location}
            <div class="small gray">
                Location: {$t->author->location}
            </div>{/if}
            {if $t->author->description}
            <div class="small gray">
                Description: {$t->author->description}
            </div>{/if}
            <div id="div{$t->post_id}">
                <form action="" class="post-setparent">
                    <select name="pid{$t->post_id}" id="pid{$t->post_id}" onselect>
                        <option disabled="disabled">Is in reply to...</option>
                        <option value="0">No particular tweet (standalone)</option>
                        {foreach from=$all_tweets key=aid item=a}<option value="{$a->post_id}">{$a->post_text|truncate_for_select}</option>
                        {/foreach}
                    </select>
					<input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save" />
                </form>
            </div>
        </div>
    </div>
</div>
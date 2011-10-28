{if $all_posts|@count >1}
    <h2>Your Posts</h2>
    {foreach from=$all_posts key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=posts-all&u={$instance->network_username|urlencode}&n={$instance->network}">More...</a></div>
{else}
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
{/if}

{if $wallposts|@count >1}
    <h2>Posts on Your Wall</h2>
    {foreach from=$wallposts key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=posts-toyou&u={$instance->network_username|urlencode}&n={$instance->network}">More...</a></div>
{/if}

{if $most_replied_to|@count >1}
    <h2>Most Replied-To Posts</h2>
    {foreach from=$most_replied_to key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=posts-mostreplies&u={$instance->network_username|urlencode}&n={$instance->network}">More...</a></div>
{/if}

{if $most_liked|@count >1}
    <h2>Most Liked Posts</h2>
    {foreach from=$most_liked key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=posts-mostlikes&u={$instance->network_username|urlencode}&n={$instance->network}">More...</a></div>
{/if}

{if $inquiries|@count >1}
    <h2>Inquiries</h2>
    {foreach from=$inquiries key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=posts-questions&u={$instance->network_username|urlencode}&n={$instance->network}">More...</a></div>
{/if}

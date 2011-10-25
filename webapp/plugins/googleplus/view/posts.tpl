{if $all_posts|@count >1}
    <h2>Your Posts</h2>
    {foreach from=$all_posts key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=all_gplus_posts&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
{else}
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
{/if}

{if $most_replied_to|@count >1}
    <h2>Most Discussed</h2>
    {foreach from=$most_replied_to key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=most_replied_to_gplus&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
{/if}

{if $plus_oned|@count >1}
    <h2>Posts with Most +1s</h2>
    {foreach from=$plus_oned key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=most_plus_oned&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
{/if}

{if $questions|@count >1}
    <h2>Inquiries</h2>
    {foreach from=$questions key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div align="right"><a href="index.php?v=gplus_questions&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
{/if}

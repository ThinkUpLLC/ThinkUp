{if $all_posts|@count >1}
<div class="section">
    <h2>Your Posts</h2>
    {foreach from=$all_posts key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div class="view-all"><a href="?v=posts-all&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
</div>
{else}
<div class="alert urgent">
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
</div>
{/if}

{if $most_replied_to|@count >1}
<div class="section">
    <h2>Most Discussed</h2>
    {foreach from=$most_replied_to key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div class="view-all"><a href="?v=posts-mostreplies&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
</div>
{/if}

{if $plus_oned|@count >1}
<div class="section">
    <h2>Posts with Most +1s</h2>
    {foreach from=$plus_oned key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div class="view-all"><a href="?v=posts-mostplusones&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
</div>
{/if}

{if $questions|@count >1}
<div class="section">
    <h2>Inquiries</h2>
    {foreach from=$questions key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets="true"}
    {/foreach}
    <div class="view-all"><a href="?v=posts-questions&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a></div>
</div>
{/if}

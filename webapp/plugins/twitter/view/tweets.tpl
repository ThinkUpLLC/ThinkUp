{if $all_tweets|@count >1}
<div class="section">
    <h2>Your Tweets</h2>
    {foreach from=$all_tweets key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=tweets-all&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{else}
<div class="alert helpful">
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
</div>
{/if}

{if $messages_to_you|@count >1}
<div class="section">

    <h2>Tweets to You</h2>
    {foreach from=$messages_to_you key=tid item=t name=foo}
        {include file="_post.author_no_counts.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=tweets-messages&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $most_replied_to_tweets|@count >1}
<div class="section">
    <h2>Most Replied-To All Time</h2>
    {foreach from=$most_replied_to_tweets key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=tweets-mostreplies&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $author_replies|@count >1}
<div class="section">
    <h2>Exchanges</h2>
      {foreach from=$author_replies key=tahrt item=r name=foo}
        {include file="_post.qa.tpl" t=$t}
    {/foreach}
</div>
{/if}

{if $most_retweeted|@count >1}
<div class="section">
    <h2>Most Retweeted All Time</h2>
    {foreach from=$most_retweeted key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=tweets-mostretweeted&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $favorites|@count >1}
<div class="section">
    <h2>Favorites</h2>
    {foreach from=$favorites key=tid item=t name=foo}
        {include file="_post.author_no_counts.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=ftweets-all&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $inquiries|@count >1}
<div class="section">
    <h2>Inquiries</h2>
    {foreach from=$inquiries key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div class="view-all"><a href="?v=tweets-questions&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

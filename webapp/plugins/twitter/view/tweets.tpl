{if $all_tweets|@count >1}
    <h2>Your Tweets</h2>
    {foreach from=$all_tweets key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=tweets-all&u={$instance->network_username}&n=twitter">More...</a></div>
{else}
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
{/if}

{if $messages_to_you|@count >1}
    <h2>Tweets to You</h2>
    {foreach from=$messages_to_you key=tid item=t name=foo}
        {include file="_post.author_no_counts.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=tweets-messages&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}

{if $most_replied_to_tweets|@count >1}
    <h2>Most Replied-To All Time</h2>
    {foreach from=$most_replied_to_tweets key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=tweets-mostreplies&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}

{if $author_replies|@count >1}
    <h2>Exchanges</h2>
      {foreach from=$author_replies key=tahrt item=r name=foo}
        {include file="_post.qa.tpl" t=$t}
    {/foreach}
{/if}

{if $most_retweeted|@count >1}
    <h2>Most Retweeted All Time</h2>
    {foreach from=$most_retweeted key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=tweets-mostretweeted&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}

{if $favorites|@count >1}
    <h2>Favorites</h2>
    {foreach from=$favorites key=tid item=t name=foo}
        {include file="_post.author_no_counts.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=ftweets-all&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}

{if $inquiries|@count >1}
    <h2>Inquiries</h2>
    {foreach from=$inquiries key=tid item=t name=foo}
        {include file="_post.counts_no_author.tpl" post=$t}
    {/foreach}
    <div align="right"><a href="index.php?v=tweets-questions&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}


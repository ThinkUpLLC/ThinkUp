{if $linksinfaves|@count >1}
<div class="section">
    <h2>Links in Favorites</h2>
    {foreach from=$linksinfaves key=tid item=l name=foo}
        {include file="_link.tpl" t=$f}
    {/foreach}
    <div class="view-all"><a href="?v=links-favorites&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $linksbyfriends|@count >1}
<div class="section">
    <h2>Links by Friends</h2>
    {foreach from=$linksbyfriends key=tid item=l name=foo}
        {include file="_link.tpl" t=$f}
    {/foreach}
    <div class="view-all"><a href="?v=links-friends&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $photosbyfriends|@count >1}

<div class="section">
    <h2>Photos by Friends</h2>
    {foreach from=$photosbyfriends key=tid item=l name=foo}
        {include file="_link.tpl" t=$f}
    {/foreach}
    <div class="view-all"><a href="?v=links-photos&u={$instance->network_username}&n=twitter">More...</a></div>
</div>
{/if}

{if $linksinfaves|@count < 1 && $linksbyfriends|@count < 1 && $photosbyfriends|@count < 1}
    <div class="alert urgent">No posts to display. {if $logged_in_user}Update your data and try again.{/if}</div>
{/if}
<h1>Domain Stats for {$instance->network_username}</h1>
{foreach from=$domain_widget_likes_by_day key="key" item="value"}
{$key} : {$value} <br>
{/foreach}
<br>
{foreach from=$domain_widget_likes_by_week key="key" item="value"}
{$key} : {$value} <br>
{/foreach}
<br>
{foreach from=$domain_widget_likes_by_month key="key" item="value"}
{$key} : {$value} <br>
{/foreach}

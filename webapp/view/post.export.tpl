<pre>
{foreach from=$tweets key=aid item=a}
{$a->adj_pub_date}	{$a->post_text|strip}
{/foreach}
</pre>

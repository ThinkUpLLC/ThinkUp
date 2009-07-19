<pre>
{foreach from=$tweets key=aid item=a}
{$a.adj_pub_date}	{$a.tweet_html|strip}
{/foreach}
</pre>
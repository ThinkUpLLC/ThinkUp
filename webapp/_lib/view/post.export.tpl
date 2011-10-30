{$info_msg}{$error_msg}<pre>
{if $posts}
{foreach from=$posts key=aid item=a}
{$a->adj_pub_date}    {$a->post_text|strip}
{/foreach}
{/if}
</pre>
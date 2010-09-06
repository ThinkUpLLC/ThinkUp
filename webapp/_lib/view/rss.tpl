<?xml version="1.0"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>ThinkUp Crawler for {$logged_in_user}</title>
    <link>http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI|@replace:'&':'&amp;'}</link>
    <atom:link href="http{if $smarty.server.HTTPS}s{/if}://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI|@replace:'&':'&amp;'}" rel="self" type="application/rss+xml" /> 
    <description>Calls to this feed will launch the ThinkUp crawler, if it hasn't run in the last {$rss_crawler_refresh_rate} minutes</description> 
    <pubDate>{$smarty.now|date_format:"%a, %d %b %Y %H:%M:%S %Z"}</pubDate>
    <lastBuildDate>{$smarty.now|date_format:"%a, %d %b %Y %H:%M:%S %Z"}</lastBuildDate>
    <generator>ThinkUp v{$thinkup_version}</generator>
    {foreach from=$items key=key item=item name=foo}
      <item>
        <title>{$item.title}</title>
        <link>{$item.link}</link>
        <description>{$item.description}</description>
        <pubDate>{$item.pubDate}</pubDate>
        <guid>{$item.guid}</guid>
      </item>
    {/foreach}
  </channel>
</rss>


<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="utf-8">
  <title>ThinkUp {$title} | Mark Parent</title>
  <link rel="shortcut icon" href="{$site_root_path}assets/img/favicon.ico">
</head>

<body>
  <h1>Mark Parent</h1>
  <p><a href="index.php">back to dashboard</a></p>
  <p>
    {if $reply->post_text}
      {$reply->post_text}
    {else}
      <span class="no-post-text">No post text</span>
    {/if}
  </p>
  <ul>
    {foreach from=$possible_parents key=tid item=t}
      <li><a href="mark-parent.php?t={$reply->post_id}&amp;p={$t->post_id}">use this one</a>
        {if $t->post_text}
          {$t->post_text}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
      </li>
    {/foreach}
  </ul>
</body>

</html>

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
  <p>{$reply->post_text}</p>
  <ul>
    {foreach from=$possible_parents key=tid item=t}
      <li><a href="mark-parent.php?t={$reply->post_id}&amp;p={$t->post_id}">use this one</a> {$t->post_text}</li>
    {/foreach}
  </ul>
</body>

</html>

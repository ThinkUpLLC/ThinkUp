<html>
<head>
<title></title>
</head>
<body>
<h1>Mark Parent</h1>

<a href="index.php">back to dashboard</a>
<p>{$reply->post_text}</p>

<ul>
{foreach from=$possible_parents key=tid item=t}
<li><a href="mark-parent.php?t={$reply->post_id}&amp;p={$t->post_id}">use this one</a> {$t->post_text}</li>
{/foreach}
</ul>


</body>
</html>

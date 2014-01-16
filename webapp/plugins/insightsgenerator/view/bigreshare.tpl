
{include file=$tpl_path|cat:"_users.tpl" users=$i->related_data.people }

{*
  We didn't store the posts in an array, so this is a hack until we regenerate.
  -- MBJ 2014-01-15
*}
{if isset($i->related_data.posts->id)}
{assign var="the_post" value=$i->related_data.posts}
{else}
{assign var="the_post" value=$i->related_data.posts[0]}
{/if}

{include file=$tpl_path|cat:"_post.tpl" post=$the_post hide_avatar=true}

{*
Render a link to a user on the source network.

Parameters:
$network (required) String, either 'facebook', 'twitter', or 'instagram'
$user_id (required) String that represents the user's id on the source network
$username (required) String that represents the user's username on the source network
*}

{if $network eq 'twitter'}https://twitter.com/intent/user?user_id={$user_id}{elseif $network eq 'facebook'}https://facebook.com/{$user_id}{elseif $network eq 'instagram'}https://instagram.com/{$username}{/if}
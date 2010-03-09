<?php

function twitter_configuration() {
	global $s;
	global $od;
	global $id;
	global $cfg;
	global $owner;

	$to = new TwitterOAuth($cfg->oauth_consumer_key, $cfg->oauth_consumer_secret);
	/* Request tokens from twitter */
	$tok = $to->getRequestToken();
	$token = $tok['oauth_token'];
	$_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

	/* Build the authorization URL */
	$oauthorize_link = $to->getAuthorizeURL($token);

	$owner_instances = $id->getByOwner($owner);

	$s->assign('owner_instances', $owner_instances);
	$s->assign('oauthorize_link', $oauthorize_link);
}


$webapp->addToConfigMenu('twitter', 'Twitter');
$webapp->registerCallback('twitter_configuration', 'configuration|twitter');


?>
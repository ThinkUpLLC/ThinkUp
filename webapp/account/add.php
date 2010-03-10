<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$tu = $_POST['twitter_username'];
$tp = $_POST['twitter_password'];

$od = new OwnerDAO($db);

$owner = $od->getByEmail($_SESSION['user']);


$api = new TwitterAPIAccessor($tu, $tp);

$twitter_id = $api->doesAuthenticate();
if ( $twitter_id > 0 ) {
	echo "Twitter authentication successful.<br />";

	$id = new InstanceDAO($db);
	$i = $id->getByUsername($tu);
	$oid = new OwnerInstanceDAO($db);

	if ( isset($i) ) {
		echo "Instance already exists.<br />";

		$id->updatePassword($tu, $tp);
		echo "Updated existing instance's password.<br />";

		$oi = $oid -> get($owner->id, $i->id);
		if ( $oi != null ) {
			echo "Owner already has this instance, no insert or update.<br />";
		} else {
			$oid->insert($owner->id, $i->id);
			echo "Added owner instance.<br />";
		}
			
	} else {
		echo "Instance does not exist.<br />";

		$id->insert($twitter_id, $tu, $tp);
		echo "Created instance with password.<br />";

		$i = $id->getByUsername($tu);
		$oid->insert($owner->id, $i->id);
		echo "Created an owner_instance.<br />";
	}

} else {
	echo 'Twitter authentication failed.';
}

# clean up
$db->closeConnection($conn);

echo '<br /> <a href="'.$THINKTANK_CFG['site_root_path'].'account/">Back to your account</a>.';

?>

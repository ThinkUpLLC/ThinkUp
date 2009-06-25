<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /u/login.php"); }

chdir("..");
$root_path 			= realpath('./../include')."/";
require_once($root_path . "init.php");

/*
if u/p authenticates on Twitter
	if u does not exist in instances table
		create instance and owner_instance
	else
		update existing instance with password and add owner_instance
else
	throw error
*/

/* check credentials here */

$tu = $_POST['twitter_username'];
$tp = $_POST['twitter_password'];

$db = new Database();
$conn = $db->getConnection();
$od = new OwnerDAO();

$owner = $od->getByEmail($_SESSION['user']);


$api = new TwitterAPIAccessor($tu, $tp);

$twitter_id = $api->doesAuthenticate();
if ( $twitter_id > 0 ) {
	echo "Twitter authentication successful.<br />";
	
	$id = new InstanceDAO();
	$i = $id->getByUsername($tu);
	$oid = new OwnerInstanceDAO();

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
	# clean up
	$db->closeConnection($conn);	

} else {
	echo 'Twitter authentication failed.';
}

echo '<br /> <a href="'.$TWITALYTIC_CFG['site_root_path'].'account/">Back to your account</a>.';


?>
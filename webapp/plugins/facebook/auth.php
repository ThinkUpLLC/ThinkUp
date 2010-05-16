<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /session/login.php");
}

chdir("..");
chdir("..");

require_once 'init.php';

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}


$fb_user = null;
$facebook = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);

try {
    $fb_user = $facebook->api_client->users_getLoggedInUser();
    echo "Facebook user is logged in and user ID set<br />";
    $fb_username = $facebook->api_client->users_getInfo($fb_user, 'name');
    $fb_username = $fb_username[0]['name'];

}
catch(Exception $e) {
    echo "EXCEPTION: ".$e->message;
}


if (isset($_GET['sessionKey']) && isset($fb_user) && $fb_user > 0) {

    $session_key = $_GET['sessionKey'];

    echo "DEBUG:";
    echo "Session Key: ".$session_key."<br />";

    $od = new OwnerDAO($db);
    $id = new InstanceDAO($db);
    $oid = new OwnerInstanceDAO($db);
    $ud = new UserDAO($db);


    $owner = $od->getByEmail($_SESSION['user']);

    $i = $id->getByUserId($fb_user);
    if (isset($i)) {
        echo "Instance exists<br />";
        $oi = $oid->get($owner->id, $i->id);
        if ($oi == null) { //Instance already exists, owner instance doesn't
            $oid->insert($owner->id, $i->id, $session_key); //Add owner instance with session key
            echo "Created owner instance.<br />";
        }
    } else { //Instance does not exist
        echo "Instance does not exist<br />";

        $id->insert($fb_user, $fb_username, 'facebook');
        echo "Created instance";

        $i = $id->getByUserId($fb_user);
        $oid->insert($owner->id, $i->id, $session_key);
        echo "Created owner instance.<br />";
    }

    if (!$ud->isUserInDB($fb_user)) {
        $r = array('user_id'=>$fb_user, 'user_name'=>$fb_username,'full_name'=>$fb_username, 'avatar'=>'', 'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0, 'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'', 'last_post_id'=>'', 'network'=>'facebook' );
        $u = new User($r, 'Owner info');
        $ud->updateUser($u);
    }
} else {
    echo "No session key or logged in Facebook user.";
}

# clean up

$db->closeConnection($conn);
echo '<br /> <a href="'.$THINKTANK_CFG['site_root_path'].'account/">Back to your account</a>.';

?>

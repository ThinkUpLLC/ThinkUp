<?php 
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /session/login.php");
}

// set up
chdir("..");
chdir("..");


require_once ("common/init.php");

$s = new SmartyThinkTank();

$cfg = new Config();

$request_token = $_GET['oauth_token'];
$request_token_secret = $_SESSION['oauth_request_token_secret'];
/*
 echo "DEBUG:"
 echo "URL Request Token: ".$request_token."<br />";
 echo "Session Request Token: ".$request_token_secret."<br />";
 */
$to = new TwitterOAuth($cfg->oauth_consumer_key, $cfg->oauth_consumer_secret, $request_token, $request_token_secret);
$tok = $to->getAccessToken();

if (isset($tok['oauth_token']) && isset($tok['oauth_token_secret'])) {
    $api = new TwitterAPIAccessorOAuth($tok['oauth_token'], $tok['oauth_token_secret'], $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret']);
    
    $u = $api->verifyCredentials();
    
    //	echo "User ID: ". $u['user_id'];
    //	echo "User name: ". $u['user_name'];
    $twitter_id = $u['user_id'];
    $tu = $u['user_name'];
    
    $od = new OwnerDAO($db);
    $owner = $od->getByEmail($_SESSION['user']);
    
    if ($twitter_id > 0) {
        $msg = "<h2 class=\"subhead\">Twitter authentication successful!</h2>";
        
        $id = new InstanceDAO($db);
        $i = $id->getByUsername($tu);
        $oid = new OwnerInstanceDAO($db);
        
        if (isset($i)) {
            $msg .= "Instance already exists.<br />";
            
            $oi = $oid->get($owner->id, $i->id);
            if ($oi != null) {
                $msg .= "Owner already has this instance, no insert  required.<br />";
                if ($oid->updateTokens($owner->id, $i->id, $tok['oauth_token'], $tok['oauth_token_secret'])) {
                    $msg .= "OAuth Tokens updated.";
                } else {
                    $msg .= "OAuth Tokens NOT updated.";
                }
            } else {
                if ($oid->insert($owner->id, $i->id, $tok['oauth_token'], $tok['oauth_token_secret'])) {
                    $msg .= "Added owner instance.<br />";
                } else {
                    $msg .= "PROBLEM Did not add owner instance.<br />";
                }
            }
            
        } else {
            $msg .= "Instance does not exist.<br />";
            
            $id->insert($twitter_id, $tu);
            $msg .= "Created instance.<br />";
            
            $i = $id->getByUsername($tu);
            if ($oid->insert($owner->id, $i->id, $tok['oauth_token'], $tok['oauth_token_secret'])) {
                $msg .= "Created an owner instance.<br />";
            } else {
                $msg .= "Did NOT create an owner instance.<br />";
            }
        }
        
        $cfg = new Config($i->network_username, $i->network_user_id);
        $s->assign('cfg', $cfg);
        
    }
    
    # clean up
    $db->closeConnection($conn);
    
}
$msg .= '<a href="'.$THINKTANK_CFG['site_root_path'].'account/" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Back to your account</a>';

$s->assign('msg', $msg);
$s->display('message.tpl');

?>

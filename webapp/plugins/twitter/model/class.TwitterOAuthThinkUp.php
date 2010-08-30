<?php
if (!class_exists('twitterOAuth')) {
    Utils::defineConstants();
    require_once THINKUP_WEBAPP_PATH.'_lib/extlib/twitteroauth/twitteroauth.php';
}

class TwitterOAuthThinkUp extends TwitterOAuth {

    //Adding a no OAuth required call to this class, for calls to the Search API
    function noAuthRequest($url) {
        return $this->http($url, 'GET');
    }
}

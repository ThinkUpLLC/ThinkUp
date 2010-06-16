<?php
if (!class_exists('twitterOAuth')) {
    $config = Config::getInstance();
    require_once $config->getValue('source_root_path').'extlib/twitteroauth/twitteroauth.php';
}

class TwitterOAuthThinkTank extends TwitterOAuth {

    //Adding a no OAuth required call to this class, for calls to the Search API
    function noAuthRequest($url) {
        return $this->http($url, 'GET');
    }
}

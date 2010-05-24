<?php 
class TwitterOAuthThinkTank extends TwitterOAuth {

    //Adding a no OAuth required call to this class, for calls to the Search API
    function noAuthRequest($url) {
        return $this->http($url, 'GET');
    }

    
}

<?php 
class LongUrlAPIAccessor {
    var $api_url = "http://api.longurl.org/v2/expand?";
    var $format = "php";
    var $user_agent;
    var $response_code = 1;
    var $title = 1;
    var $logger;
    
    function LongUrlAPIAccessor($app_title, $logger) {
        $this->user_agent = $app_title;
        $this->logger = $logger;
    }
    
    function expandUrl($u) {
        $params = array('title'=>$this->title, 'format'=>$this->format, 'user-agent'=>$this->user_agent, 'url'=>$u, 'response-code'=>$this->response_code, );
        
        $encoded_params = array();
        
        foreach ($params as $k=>$v) {
            $encoded_params[] = urlencode($k).'='.urlencode($v);
        }
        
        $api_call = $this->api_url.implode('&', $encoded_params);
        
        $this->logger->logStatus("API call: $api_call", get_class($this));
        
        $ctx = stream_context_create(array('http'=>array('timeout'=>1)));
        $resp = Utils::curl_get_file_contents($api_call, 0, $ctx);
        if ($resp == false)
            return null;
        else {
            //suppress unserialize notice with @ sign
            $result = @unserialize($resp);
            if ($result == false) {
                return null;
            } else {
                return $result;
            }
        }
        
    }
}
?>

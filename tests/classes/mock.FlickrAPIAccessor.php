<?php 
class FlickrAPIAccessor {
    var $api_url = "http://api.flickr.com/services/rest/?";
    var $format = "php_serial";
    var $method = "flickr.photos.getSizes";
    var $api_key;
    var $logger;
    
    function FlickrAPIAccessor($flickr_api_key, $logger) {
        $this->api_key = $flickr_api_key;
        $this->logger = $logger;
    }
    
    function getFlickrPhotoSource($u) {
        if ($this->api_key != '') {
            $this->logger->logStatus("Flickr API key set", get_class($this));
            $photo_short_id = substr($u, strlen('http://flic.kr/p/'));
            $photo_id = $this->base_decode($photo_short_id);
            $params = array('method'=>$this->method, 'photo_id'=>$photo_id, 'api_key'=>$this->api_key, 'format'=>$this->format, );
            
            $encoded_params = array();
            
            foreach ($params as $k=>$v) {
                $encoded_params[] = urlencode($k).'='.urlencode($v);
            }
            
            $api_call = $this->api_url.implode('&', $encoded_params);
            
            $this->logger->logStatus("Flickr API call: $api_call", get_class($this));
            
            //$resp = Utils::curl_get_file_contents($api_call);
            
            global $FAUX_DATA_PATH;
            $api_call = str_replace('http://', '', $api_call);
            $api_call = str_replace('/', '_', $api_call);
            $api_call = str_replace('?', '-', $api_call);
            $api_call = str_replace('&', '-', $api_call);
            //echo "READING LOCAL DATA FILE: ".$FAUX_DATA_PATH."flickr/".$api_call;
            $resp = file_get_contents($FAUX_DATA_PATH."flickr/".$api_call);
            
            if ($resp === "NONRESPONSE") {
                $resp = false;
            }
            
            if ($resp != false) {
                $fphoto = unserialize($resp);
                
                if ($fphoto['stat'] == 'ok') {
                    foreach ($fphoto['sizes']['size'] as $s) {
                        if ($s['label'] == 'Small')
                            $src = $s['source'];
                    }
                    return array("expanded_url"=>$src, "error"=>'');
                } else {
                    $this->logger->logStatus("ERROR: '".$fphoto['message']."'", get_class($this));
                    return array("expanded_url"=>'', "error"=>$fphoto['message']);
                }
                
            } else {
                $this->logger->logStatus("ERROR: No response from Flickr API", get_class($this));
                return array("expanded_url"=>'', "error"=>'No response from Flickr API');
            }
        } else {
            $this->logger->logStatus("ERROR: Flickr API key is not set", get_class($this));
            return array("expanded_url"=>'', "error"=>'');
        }
    }

    
    function base_decode($num, $alphabet = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ") {
        $decoded = 0;
        $multi = 1;
        while (strlen($num) > 0) {
            $digit = $num[strlen($num) - 1];
            $decoded += $multi * strpos($alphabet, $digit);
            $multi = $multi * strlen($alphabet);
            $num = substr($num, 0, -1);
        }
        
        return $decoded;
    }

    
}
?>

<?php 
class FlickrAPIAccessor {
    var $api_url = "http://api.flickr.com/services/rest/?";
    var $format = "php_serial";
    var $method = "flickr.photos.getSizes";
    var $api_key;
    
    function FlickrAPIAccessor($cfg) {
        $this->api_key = $cfg->flickr_api_key;
    }
    
    function getFlickrPhotoSource($u) {
        if ($this->api_key != '') {
            $photo_short_id = substr($u, strlen('http://flic.kr/p/'));
            $photo_id = $this->base_decode($photo_short_id);
            $params = array('method'=>$this->method, 'photo_id'=>$photo_id, 'api_key'=>$this->api_key, 'format'=>$this->format, );
            
            $encoded_params = array();
            
            foreach ($params as $k=>$v)
                $encoded_params[] = urlencode($k).'='.urlencode($v);
                
            $api_call = $this->api_url.implode('&', $encoded_params);
            
            $resp = Utils::curl_get_file_contents($api_call);
            if ($resp != false) {
                $fphoto = unserialize($resp);
                
                if ($fphoto['stat'] == 'ok') {
                    foreach ($fphoto['sizes']['size'] as $s) {
                        if ($s['label'] == 'Small')
                            $src = $s['source'];
                    }
                    return $src;
                } else
                    return '';
            } else {
                return '';
            }
        } else
            return '';
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

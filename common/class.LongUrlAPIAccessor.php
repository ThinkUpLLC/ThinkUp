<?php

class LongUrlAPIAccessor {
	var $api_url = "http://api.longurl.org/v2/expand?";
	var $format = "php";
	var $user_agent;
	var $response_code = 1;
	var $title = 1;

	function LongUrlAPIAccessor($cfg) {
		$this->user_agent = $cfg->app_title;
	}

	function expandUrl($u) {
		$params = array(
			'title'	=> $this->title,
			'format'	=> $this->format,
			'user-agent'	=> $this->user_agent,
			'url'	=> $u,
			'response-code' => $this->response_code,
		);

		$encoded_params = array();

		foreach ($params as $k => $v)
			$encoded_params[] = urlencode($k).'='.urlencode($v);

		$api_call = $this->api_url.implode('&', $encoded_params);
		
		$resp = Utils::curl_get_file_contents($api_call);
		if ( $resp==false )
			return null;
		else
			return unserialize($resp);

	}
}
?>
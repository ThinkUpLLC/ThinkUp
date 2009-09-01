<?php

class Utils {
	
	function GetDeltaTime($dtTime1, $dtTime2) {
		$nUXDate1 = strtotime($dtTime1->format("Y-m-d H:i:s"));
		$nUXDate2 = strtotime($dtTime2->format("Y-m-d H:i:s"));

		$nUXDelta = $nUXDate1 - $nUXDate2;
		$strDeltaTime = "" . $nUXDelta/60/60; // sec -> hour

		$nPos = strpos($strDeltaTime, ".");
		if ($nPos !== false)
		  $strDeltaTime = substr($strDeltaTime, 0, $nPos + 3);

		return $strDeltaTime;
	}	

	function getPercentage($num, $denom) {
		if ($num > 0) 
			return ($denom*100)/($num);
		else
			return 0;
	}
	
	public static function curl_get_file_contents($URL) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

		//echo $URL;
		//echo $contents;
        if ($contents) 
			return $contents;
        else 
			return null;
    }
}



?>
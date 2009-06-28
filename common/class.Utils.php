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
	

}



?>
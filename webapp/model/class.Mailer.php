<?php 
class Mailer {

    public static function mail($to, $subject, $message) {
        $mailheader = "From: \"Auto-Response\" <notifications@host>\r\n";
        $mailheader .= "X-Mailer: PHP/".phpversion();
        mail($to, $subject, $message, $mailheader);
    }
    
}


?>

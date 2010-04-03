<?php 
chdir("..");
require_once ("common/init.php");

session_start();
$session = new Session();

// TODO Take CLI parameters for Cron to run the crawler
if ($session->isLoggedIn()) {
    $crawler->crawl();
    
    if (isset($conn)) {
        $db->closeConnection($conn); // Clean up
    }
}

?>

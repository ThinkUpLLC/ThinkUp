<?php
session_start();
if (!isset($_SESSION['user']))
{
 die ("Access Denied");
}
?> 
<h2>My Account </h2>
<?php if (isset($_SESSION['user'])) { ?>
<p>Logged as <?php echo $_SESSION['user']; ?> | <a href="settings.php">Settings</a> 
  | <a href="logout.php">Logout</a> </p>
<?php } ?>  

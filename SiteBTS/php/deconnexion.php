
<?php 
session_destroy();
session_unset();
$_SESSION=array();
unset($_SESSION);
header('location:mission.php');
?>
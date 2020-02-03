<?php session_start();
function connexion(){ 
$dsn ="mysql:host=localhost;dbname=mails";
return $dbh = new PDO($dsn, 'root', '',array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));	 
}
 
 
function getEmail($email)
{
 
$dbh=connexion(); 
$req=$dbh->query('select count(*) as nb from mails where mail="'.$email.'"');
$result=$req->fetchAll();
 
if($result[0]['nb']==1){
	return true;
}else{
	return false;
}
 
}
 
 
if (getEmail($_GET['email'])){
 echo "valide";
}else{
	http_response_code(401);
    exit;
}
?>
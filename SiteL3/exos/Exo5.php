<!DOCTYPE PHP>
<?php	

if ($_GET['login']==="admin" && $_GET['pass']==="pwd"){
 echo "OK !";
}else{
	http_response_code(401);
    exit;
}
?>
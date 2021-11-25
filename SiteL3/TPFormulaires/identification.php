<?php
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=phppdo;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    	$json = array();
    	$rep='no';
		$reponse = $bdd->query('SELECT id FROM acces WHERE password ="'. $_GET['password'] .'" AND login="'.$_GET['username'].'"');
		while ($donnees = $reponse->fetch())
		{
			$rep='ok';
		}
    	$json = json_encode($json);
	}
	catch (Exception $e)
	{
	        die('Erreur : ' . $e->getMessage());
	}


	if($rep=='ok'){
		session_start();

		$reponse = $bdd->query('SELECT prenom FROM acces WHERE password ="'. $_GET['password'] .'" AND login="'.$_GET['username'].'"');
		while ($donnees = $reponse->fetch())
		{
			$rep=$donnees['prenom'];
		}

		$_SESSION['prenom']=$donnees;
		$_SESSION['CONNECTE']="YES";

		print("<h1>Vous êtes connecté ".$rep." !</h1>");
	}
	else{
		header('Location: login.php?message=Erreur');
  		exit();
	}
?>
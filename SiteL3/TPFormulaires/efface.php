<a href="deconnexion.php"><img src="images/deconnexion.png">Déconnexion</a>
<?php
session_start();
if(!isset($_SESSION['CONNECTE']) OR $_SESSION['CONNECTE'] !="YES"){

		header('Location: login.php');
}
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=phppdo;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
	catch (Exception $e)
	{
	        die('Erreur : ' . $e->getMessage());
	}

	// Si tout va bien, on peut continuer
try
	{
		if (isset($_GET['l'])) {
			$reponse = $bdd->query('DELETE FROM acces WHERE login ="'.$_GET['l'].'" AND prenom ="'.$_GET['p'].'" AND statut="'.$_GET['s'].'" AND age='.$_GET['a']);
			while ($donnees = $reponse->fetch())
			{
			}
			header('Location: liste.php?message=Succes');
		}
		else{
			header('Location: liste.php');
		}
}
catch (Exception $e)
{
		header('Location: liste.php?message=Error');
}
?>
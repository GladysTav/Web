<a href="deconnexion.php"><img src="images/deconnexion.png">Déconnexion</a>
<?php
	session_start();
	if(!isset($_SESSION['CONNECTE']) OR $_SESSION['CONNECTE'] !="YES"){

			header('Location: login.php');
	}
	else{
		echo($_SESSION['prenom']);
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
	function id($p,$l,$s,$a)
	{
		try
		{
			$bd = new PDO('mysql:host=localhost;dbname=phppdo;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}
		catch (Exception $e)
		{
		        die('Erreur : ' . $e->getMessage());
		}
		$reponse = $bd->query('SELECT id FROM acces WHERE login ="'.$l.'" AND prenom ="'.$p.'" AND statut="'.$s.'" AND age='.$a);
		while ($donnees = $reponse->fetch())
		{
			return $donnees['id'];
		}
	}
	// On récupère tout le contenu de la table
	$reponse = $bdd->query('SELECT login, prenom, statut, age FROM acces');
	echo('<table border=1><tr><td colspan="5">Liste des acces</td><td><a href="ajoute.php"><img src="images/ajoute.png"></a></td></tr>');
	// On affiche chaque entrée une à une
	while ($donnees = $reponse->fetch())
	{
		echo('<tr>');
	     echo('<td>'.$donnees['prenom'].'</td><td>'.$donnees['login'].'</td><td>'.$donnees['statut'].'</td><td>'.$donnees['age'].'</td>'); 
	     echo('<td><a href="efface.php?p='.$donnees['prenom'].'&l='.$donnees['login'].'&s='.$donnees['statut'].'&a='.$donnees['age'].'"><img src="images/croix.png"></a></td>');
	     echo('<td><a href="modif.php?id='.id($donnees['prenom'],$donnees['login'],$donnees['statut'],$donnees['age']).'"><img src="images/modif.png"></a></td>');
	     echo('</tr>');
	}
	echo('</table>');

	$reponse->closeCursor(); // Termine le traitement de la requête

	if(isset($_GET['message'])){
		if($_GET['message']=='Succes'){
			echo('<br><br><h1>Suppression réussie !</h1>');
		}
		elseif ($_GET['message']=='Error') {
			echo('<br><br><h1>Suppression échouée...</h1>');
		}
	}
?>
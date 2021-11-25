<a href="deconnexion.php"><img src="images/deconnexion.png">Déconnexion</a>
<?php
session_start();
if(!isset($_SESSION['CONNECTE']) OR $_SESSION['CONNECTE'] !="YES"){

		header('Location: login.php');
}
?>

<form action="#" name="formu">
	<fieldset><legend>Ajouter un utilisateur</legend>
		<table>
			<tr>
				<td>Prénom :</td>
				<td><input type="text" name="prenom"></td>
			</tr>
			<tr>
				<td>Login :</td>
				<td><input type="text" name="login"></td>
			</tr>
			<tr>
				<td>Mot de passe :</td>
				<td><input type="password" name="pass"></td>
			</tr>
			<tr>
				<td>Âge :</td>
				<td><input type="text" name="age"></td>
			</tr>
		</table>
		<input type="submit" name="Go" onclick="verif()">
	</fieldset>
</form><br>
<FORM ACTION="liste.php">
    <INPUT TYPE="SUBMIT" VALUE="Retourner à la liste">
  </FORM>

<script type="text/javascript">
	function verif() {
		if(document.formu.prenom.value==""){
			window.alert("Merci d'entrer un prénom !");
		}
	}
</script>

<?php
	if (isset($_GET['prenom'])) {
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
		$reponse = $bdd->query('INSERT INTO acces(login, prenom, statut, age, password) VALUES ("'.$_GET['login'].'","'.$_GET['prenom'].'", "Etudiant", "'.$_GET['age'].'", "'.$_GET['pass'].'")');
		echo "OK !";
		//header('Location: liste.php?message=SuccesAjout');
	}
	catch (Exception $e)
	{
		echo "Erreur !";
			//header('Location: liste.php?message=ErrorAjout');
	}
	}
?>
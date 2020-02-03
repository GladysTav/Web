
<?php session_start();?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="wawa">
  <meta name="author" content="wawa">
  <title>Mission PHP</title>
  <!-- Exercice 4 -->
  <script> 
  function showValue(newValue){ document.getElementById("range").innerHTML=newValue ;} 
  </script>
</head>


<!-- PHP --><br><br>

	<!-- Etape 5 -->
<header>
	<?php 
	if(isset($_SESSION['login']))
		echo "Utilisateur connecté : ", $_SESSION['login']; ?>
</header>

	<!-- Etape 1 -->
	<body>
		<?php echo "Bonjour" ; ?>
	</body>


	<!-- Etape 3 -->
	<?php
	if(!empty($_GET))
	{
		$_GET ['login']; 
		$_GET ['mdp'];
		$_GET ['infos'];
		$_GET ['email'];
		$_GET ['url'];
		$_GET ['news'];
		$_GET ['nom'];
		$_GET ['prenom'];
	}
	if(!empty($_POST))
		echo $_POST['login'];
	?>
	<br><br>

	<form method="get" action="index.php">
		<p>
		<fieldset><legend>Formulaire :</legend>
		<input type="charset" name="login" placeholder="Login ?"><br>
		<input type="charset" name="mdp" placeholder="Password ?"><br><br>
		<input type="charset" name="nom" required placeholder="Nom ?">
		<input type="charset" name="prenom" required placeholder="Prenom ?">
		<input type="email" name="email" placeholder="Email ?" autofocus="email">
		<input type="url" name="url" placeholder="URL du site ?"> <br><br>
		<fieldset><legend>Information personnelle <br></legend><input type="charset" name="infos" size="100"></fieldset><br>
		<ul>
			<li>
				<input type="radio" name="news" value="news" checked>S'abonner à la newsletter
			</li>
			<li>
				<input type="radio" name="news" value="news2">Recevoir la newsletter par mail
			</li>
		</ul>
		<input type="submit" name="submit" value="Envoyer"></fieldset>
		</p>
	</form>
	<br>
	<?php
		if(isset($_POST["login"]))
			echo $_POST["login"];
	?>

	<!-- Etape 4 -->

	<form name="x" action="connexion.php" method="post">
		<input type="submit" value="Se connecter">
	</form>

	<form name="x" action="deconnexion.php" method="post">
		<input type="submit" value="Deconnexion">
	</form>








	<!-- Etape 2 --> <br><br>
	<footer>Footer</footer>
		<aside>Pub<br>Pub</aside>

	<br><br><br><br>

<?php session_start();?>

<header>
	<?php 
	if(isset($_SESSION['login']))
		echo "Utilisateur connecté : ", $_SESSION['login']; ?>
</header>

<br> <br>
<!-- Etape 4 du TP php -->
<h1>Connexion :</h1>

<br><br>
<?php 
$tableau = [
"Moi", "mdp",
"Chat" , "Miaou",
"Chien" , "Ouaf"
]?>


<form method="get" action="connexion.php">
	<p>
		<input type="charset" name="login" placeholder="Login ?"><br>
		<input type="Password" name="mdp" placeholder="Password ?"><br>
		<input type="submit" value="Envoyer">
	</p>
</form>


<?php
		$trouve = false;
		if (isset($_GET['login']) && isset($_GET['mdp'])) 
	{
		for ($i=0; $i < 6; $i=$i+2) 
		{ 
			if (($_GET['login'] == $tableau[$i]) && ($_GET['mdp'] == $tableau[$i+1])) 
			{
				$trouve = true;	
				echo "Correct !";
				$_SESSION['login']=$_GET['login'];
			}
			
		}

		if($trouve == true)
		{
			header('location:mission.php');
			echo "Connecté !";
		}
		else
		{
			echo 'Login disponibles :';
			for ($i=0; $i < 6; $i=$i+2)
				echo $tableau[$i], ", ";
		}
	}
?>

<br>
<a href="mission.php">retour</a>

<br><br>

<footer>Footer</footer>
		<aside>Pub<br>Pub</aside>

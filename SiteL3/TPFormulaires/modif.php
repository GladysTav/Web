<a href="deconnexion.php"><img src="images/deconnexion.png">Déconnexion</a>
<?php
session_start(); ?>

<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6 lt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7 lt8"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8 lt8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="UTF-8" />
        <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">  -->
        <title>Login </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="description" content="Login and Registration Form with HTML5 and CSS3" />
        <meta name="keywords" content="html5, css3, form, switch, animation, :target, pseudo-class" />
        <meta name="author" content="Codrops" />
        <link rel="shortcut icon" href="../favicon.ico"> 
        <link rel="stylesheet" type="text/css" href="css/demo.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/animate-custom.css" />
		<?php 
			try
			{
				$bdd = new PDO('mysql:host=localhost;dbname=phppdo;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		    	$json = array();
				$reponse = $bdd->query('SELECT nom FROM statut');
				while ($donnees = $reponse->fetch())
				{
            		$json[$donnees["nom"]]=(utf8_encode($donnees["nom"]));
				}
		    	$json = json_encode($json);
			}
			catch (Exception $e)
			{
			        die('Erreur : ' . $e->getMessage());
			}
		?>
		<script>
			function check() {
				if(document.formu.champ.value=="statut"){
					$maString='<SELECT name="valeur" class="form-control">';
					$tab=<?php echo $json; ?>;

					for (var element in $tab) {
					   $maString=$maString.concat('<option value="'+ element +'">'+ element +'</option>')
					}
					$maString=$maString.concat('</SELECT>');
					document.getElementById("monId" ).innerHTML = $maString;
				}
				else{
					document.getElementById("monId" ).innerHTML = '<input type="text" name="valeur">';
				}
			}
		</script>
    </head>
    <body>
           <form action="#" name="formu">
				<fieldset><legend>Modifier un utilisateur</legend>
					<table>
						<tr>
							<td>Champ :</td>
							<td>
								<SELECT name='champ' class='form-control' onChange="check()"><br>
								<OPTION value="age">Âge
								<OPTION value="prenom">Prénom
								<OPTION value="login">Login
								<OPTION value="password">Mot de passe
								<OPTION value="statut">Statut
								</SELECT>
							</td>
						</tr>
						<tr>
							<td>Valeur :</td>
							<td name="ChampValue" id="monId"><input type="text" name="valeur"></td>
							<input type="hidden" name="id" visibility="hidden">
						</tr>
					</table>
					<input type="submit" name="Go" onclick="verif()">
				</fieldset>
			</form><br>
			<FORM ACTION="liste.php">
			    <INPUT TYPE="SUBMIT" VALUE="Retourner à la liste">
			  </FORM>

			<?php
			if(isset($_GET['id'])){
				echo('<script>document.formu.id.value='.$_GET['id'].';</script>');
			}
				try
				{
					$bdd = new PDO('mysql:host=localhost;dbname=phppdo;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
				}
				catch (Exception $e)
				{
				        die('Erreur : ' . $e->getMessage());
				}

				if (isset($_GET['valeur'])) {

					// Si tout va bien, on peut continuer
					try
					{	
						if($_GET['champ']=="age"){
							$reponse = $bdd->query('UPDATE acces SET age='.$_GET['valeur'].' WHERE id='.$_GET['id']);
						}
						else{
							$reponse = $bdd->query('UPDATE acces SET '.$_GET['champ'].'="'.$_GET['valeur'].'" WHERE id='.$_GET['id']);
						}
						echo "OK !";
						//header('Location: liste.php?message=SuccesAjout');
					}
					catch (Exception $e)
					{
						echo 'Erreur : ' . $e->getMessage();
							//header('Location: liste.php?message=ErrorAjout');
					}
				}
			?>
    </body>
</html>





















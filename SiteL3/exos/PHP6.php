<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
	/*
	Complétez le fichiertp3form.php en écrivant les scripts suivants.
	Une liste de loisirs est enregistrée dans une variable de type tableau.
	Ecrivez le script qui :
	- génère la variable tableau.
	- affiche la liste de boutons radio ci-joint, à partir du tableau.
	Complétez tp3affiche.php en écrivant le script qui affiche le loisir favori choisi, par exemple :
	Votre loisir favori est : Musique.
	*/
	$loisirs=array('Foot','Equitation','Badminton', 'Jeux', 'Voyages', 'Musique');

		if(!isset($_GET['loisir']))
		{
			print("<h1>Formulaire</h1><br>");
			print('<form action="#">');
			foreach ($loisirs as $key => $value) {
				print('<input type="radio" name="loisir" value="'.$key.'">'.$value.'<br>');
			}
			print("<button class='btn btn-success btn-lg'>GO !</button></form>");
			print("</form>");
		}
		else
		{
			if(isset($_GET['loisir']) AND $_GET['loisir']!=""){
				print("Ton loisir favori est ".$loisirs[$_GET['loisir']]);
			}
			else
			{
				print("<h1>Formulaire</h1><br>");
				print('<form action="#">');
				foreach ($loisirs as $key => $value) {
					print('<input type="radio" name="loisir" value="'.$key.'">'.$value.'<br>');
				}
				print("<button class='btn btn-success btn-lg'>GO !</button></form>");
				print("</form>");
			} 
		} 
	?>
</body>
</html>
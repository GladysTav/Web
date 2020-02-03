<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
	/*
	Complétez le fichier tp3form.php en écrivant le script qui affiche la liste des loisirs pratiqués
	sous forme de cases à cocher. Pensez à réutiliser le tableau créé précédemment. Plusieurs
	réponses sont possibles. Chacune est transmise comme un élément de tableau. Complétez
	tp3affiche.php en écrivant le script qui affiche le ou les loisirs pratiqués choisis (pensez que
	c'est un tableau qui est transmis), par exemple :
	Vous pratiquez aussi comme loisirs : Jeux - Sports -
	Voyages. La liste sera triée par ordre alphabétique.
	Remarques :
	implode($car,$tableau) : renvoie une chaîne de caractère contenant les éléments du tableau séparés par $car.
	On l'utilise souvent avec ", " ou " - " ou " " ou " | ".
	explode($car,$chaine) : fonction inverse de implode(), elle renvoie un tableau formé de sous-chaînes issues
	d'une chaîne tronçonnées en utilisant comme séparateur $car.
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
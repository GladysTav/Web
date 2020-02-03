<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
	/*
	Ecrivez un formulaire qui demande un nombre de lignes L et une taille de bordure B. A la soumission, il génère
	une page comportant un tableau vide de L lignes (une seule colonne) avec une bordure de B pixels. Modifiez le
	script pour demander le nombre de colonnes C et générer un tableau de L lignes et C colonnes.
	*/
		if(!isset($_GET['L']) AND !isset($_GET['B']) AND !isset($_GET['C']))
		{
			print("<h1>Format du tableau</h1><br>");
			print("<form action='#'>Nombre de lignes : <input type='text' name='L' class='form-control'>");
			print("<form action='#'>Nombre de colonnes : <input type='text' name='C' class='form-control'>");
			print("<form action='#'>Taille de la bordure : <input type='text' name='B' class='form-control'>");
			print("<button class='btn btn-success btn-lg'>GO !</button></form>");
		}
		else
		{
			if(isset($_GET['L']) AND isset($_GET['B']) AND isset($_GET['C']) AND $_GET['L']!="" AND $_GET['B']!="" AND $_GET['C']!=""){
				print("<h1>Résultat </h1>");
				print('<table border ="'.$_GET['B'].'" width=60%>');
				for ($i=0; $i < $_GET['L'] ; $i++) { 
					print('<tr>');
					for ($a=0; $a < $_GET['C'] ; $a++) { 
						print('<td> - </td>');
					}
					print('</tr>');
				}
			}
			else
			{
				print("<h1>Format du tableau</h1><br>");
				print("<form action='#'>Nombre de lignes : <input type='text' name='L' class='form-control'>");
				print("<form action='#'>Nombre de colonnes : <input type='text' name='C' class='form-control'>");
				print("<form action='#'>Taille de la bordure : <input type='text' name='B' class='form-control'>");
				print("<button class='btn btn-success btn-lg'>GO !</button></form>");
				?>
				
				<script >window.alert("Merci d'entrer toutes les valeurs");</script>
			<?php } 
		} ?>
	
</body>
</html>
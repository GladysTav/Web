<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
	/*
	Créez une pagetp3form.php sur laquelle vous taperez les scripts suivants.
	- Tapez les balises html habituelles. La page comporte un formulaire de méthode get, dont l'action est l'accès
	au fichier tp3affiche.php.
	En utilisant des boucles :
	- Ecrivez en une liste déroulante de formulaire qui permet de choisir un jour (de 1 à 31).
	- Ecrivez en une liste déroulante de formulaire qui permet de choisir une année (de 1980 à 2005).
	- Ecrivez une liste déroulante de formulaire qui permet de choisir un mois. La valeur transmise correspond
	au nom du mois, tel qu'il est affiché dans la liste(exemple : Février).
	- Modifiez le script pour que la valeur transmise soit le nombre du mois (exemple : 2).
	- Ecrivez la page tp3affiche.php qui affiche (en s'adaptant au choix fait):
	La date choisie est le 3/5/1993. 

	<SELECT name="nom" size="1">
	<OPTION>lundi
	<OPTION>mardi
	<OPTION>mercredi
	<OPTION>jeudi
	<OPTION>vendredi
	</SELECT>
	*/
		if(!isset($_GET['an']) AND !isset($_GET['jour']) AND !isset($_GET['mois']))
		{
			print("<h1>Formulaire</h1><br>");
			print("<form action='#'>");
			
			print("<SELECT name='jour' class='form-control'><br>");
			for ($i=1; $i <= 31 ; $i++) { 
				print('<OPTION>'.$i);
			}
			print('</SELECT><br>');
			
			print("<SELECT name='an' class='form-control'><br>");
			for ($i=1980; $i <= 2005 ; $i++) { 
				print('<OPTION>'.$i);
			}
			print('</SELECT><br>');
			
			print("<SELECT name='mois' class='form-control'><br>");
			print('<OPTION value="1">Janvier');
			print('<OPTION value="2">Février');
			print('<OPTION value="3">Mars');
			print('<OPTION value="4">Avril');
			print('<OPTION value="5">Mai');
			print('<OPTION value="6">Juin');
			print('<OPTION value="7">Juillet');
			print('<OPTION value="8">Août');
			print('<OPTION value="9">Septembre');
			print('<OPTION value="10">Octobre');
			print('<OPTION value="11">Novembre');
			print('<OPTION value="12">Décembre');
			print('</SELECT><br>');

			print("<button class='btn btn-success btn-lg'>GO !</button></form>");
		}
		else
		{
			if(isset($_GET['an']) AND isset($_GET['jour']) AND isset($_GET['mois']) AND $_GET['an']!="" AND $_GET['jour']!="" AND $_GET['mois']!=""){
				print("La date chosie est le ".$_GET['jour']."/".$_GET['mois']."/".$_GET['an']);
			}
			else
			{
				print("<h1>Formulaire</h1><br>");
				print("<form action='#'>");
				
				print("<SELECT name='jour' class='form-control'><br>");
				for ($i=1; $i <= 31 ; $i++) { 
					print('<OPTION>'.$i);
				}
				print('</SELECT><br>');
				
				print("<SELECT name='an' class='form-control'><br>");
				for ($i=1980; $i <= 2005 ; $i++) { 
					print('<OPTION>'.$i);
				}
				print('</SELECT><br>');
				
				print("<SELECT name='mois' class='form-control'><br>");
				print('<OPTION value="1">Janvier');
				print('<OPTION value="2">Février');
				print('<OPTION value="3">Mars');
				print('<OPTION value="4">Avril');
				print('<OPTION value="5">Mai');
				print('<OPTION value="6">Juin');
				print('<OPTION value="7">Juillet');
				print('<OPTION value="8">Août');
				print('<OPTION value="9">Septembre');
				print('<OPTION value="10">Octobre');
				print('<OPTION value="11">Novembre');
				print('<OPTION value="12">Décembre');
				print('</SELECT><br>');

				print("<button class='btn btn-success btn-lg'>GO !</button></form>");

				?>
				
				<script >window.alert("Merci de choisir une valeur par champ");</script>
			<?php }  
		} ?>
	
</body>
</html>
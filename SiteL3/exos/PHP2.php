<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
		if(!isset($_GET['nb']) AND !isset($_GET['sup']) AND !isset($_GET['inf']))//nb inf et sup
		{
			print("<h1>Entrez les nombres</h1><br>");
			print("<form action='#'><input type='text' name='nb' class='form-control'>");
			print(" est-il compris entre "."<input type='text' name='inf' class='form-control'>"." et "."<input type='text' name='sup' class='form-control'>". " ?");
			print("<button class='btn btn-success btn-lg'>Envoyer</button></form>");
		}
		else
		{
			if(isset($_GET['nb']) AND isset($_GET['sup']) AND isset($_GET['inf']) AND $_GET['nb']!="" AND $_GET['inf']!="" AND $_GET['sup']!=""){
				print("<h1>Résultats du test</h1>");
				if($_GET['nb'] < $_GET['sup'] AND $_GET['nb'] > $_GET['inf']){
					print("Oui, ".$_GET['nb']." est compris entre ".$_GET['inf'].' et '.$_GET['sup']);
				}
				else
				{
					print("Non, ".$_GET['nb']." n'est pas compris entre ".$_GET['inf'].' et '.$_GET['sup']);
				}
			}
			else
			{
				print("<h1>Entrez les nombres</h1><br>");
				print("<form action='#'><input type='text' name='nb' class='form-control'>");
				print(" est-il compris entre "."<input type='text' name='inf' class='form-control'>"." et "."<input type='text' name='sup' class='form-control'>". " ?");
				print("<button class='btn btn-success btn-lg'>Envoyer</button></form>");
				?>
				
				<script >window.alert("Merci d'entrer toutes les valeurs");</script>
			<?php } 
		} ?>
	
</body>
</html>

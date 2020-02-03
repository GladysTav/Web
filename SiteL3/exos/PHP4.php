<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<?php 
	/*
	Ecrivez un script affichant un formulaire demandant un nom et un mot de passe (qui n'apparaît pas à l'écran).
	A la soumission, il s'appelle lui-même et vérifie l’identité du visiteur par rapport à son identité écrite "en
	dur". La casse ne devra être prise en compte (le visiteur peut taper indifféremment en majuscules ou en
	minuscules). Les espaces tapés en début ou en fin de mot de passe ou de nom seront éliminés avec une
	fonction trim().
	Si le visiteur n'est pas reconnu, seul le formulaire s'affiche. Si
	le visiteur est reconnu, seul un message d'accueil s'affiche.
	*/
		if(!isset($_GET['L']) AND !isset($_GET['B']))
		{
			print("<h1>Formulaire</h1><br>");
			print("<form action='#'>Nom : <input type='text' name='L' class='form-control'><br>");
			print("<form action='#'>MDP : <input type='password' name='B' class='form-control'><br>");
			print("<button class='btn btn-success btn-lg'>GO !</button></form>");
		}
		else
		{
			if(isset($_GET['L']) AND isset($_GET['B']) AND $_GET['L']!="" AND $_GET['B']!=""){
				$log=strtolower(trim($_GET['L']));
				$mdp=strtolower(trim($_GET['B']));

				if($log=="moi" AND $mdp=="mdp")
				{
					print("<h1>Bienvenue</h1>");
				}
				else{
					print("<h1>Formulaire</h1><br>");
					print("<form action='#'>Nom : <input type='text' name='L' class='form-control'><br>");
					print("<form action='#'>MDP : <input type='text' name='B' class='form-control'><br>");
					print("<button class='btn btn-success btn-lg'>GO !</button></form>");
					?>
					
					<script >window.alert("Et non, la réponse est : moi - mdp");</script>
				<?php } 
			}
			else
			{
				print("<h1>Formulaire</h1><br>");
				print("<form action='#'>Nom : <input type='text' name='L' class='form-control'><br>");
				print("<form action='#'>MDP : <input type='text' name='B' class='form-control'><br>");
				print("<button class='btn btn-success btn-lg'>GO !</button></form>");
				?>
				
				<script >window.alert("Merci d'entrer toutes les valeurs");</script>
			<?php } 
		} ?>
	
</body>
</html>
<!DOCTYPE html>
<html>
<head>
	<title>Site</title>
</head>

<body style="width: 100%">
	<!-- <h1>Exo : Moteur de recherche</h1><br> -->
<br>
<table style="margin-left: 20px; background-color: white;">
	<form action="#">
		<tr>
			<td>
				Pr&eacute;nom : 
			</td>
			<td>
				<input type="text" name="first" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				Nom : 
			</td>
			<td>
				<input type="text" name="last" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				Adresse : 
			</td>
			<td>
				<input type="text" name="adr" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				Ville : 
			</td>
			<td>
				<input type="text" name="ville" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				Code postal : 
			</td>
			<td>
				<input type="text" name="cp" class="form-control">
			</td>
		</tr>
		<tr>
			<td align="center">
				<button class="btn btn-success btn-lg">Envoyer</button>
			</td>
			<td align="center">
				<button class="btn btn-success btn-lg">Réinitialiser</button>
			</td>
		</tr>
	</form>
</table>

<br><br><br><br>
<?php
if(!isset($_GET['f']))
{
	if(isset($_GET['first'])) {
		print('Bienvenue '.$_GET['first'].' <b>'.$_GET['last'].'</b><br>');
	}
	if(isset($_GET['adr'])) {
		print('Nous avons bien not&eacute; que vous habitez '.'<br>');
		print($_GET['adr'].' à <b>'.$_GET['ville'].'</b> ('.$_GET['cp'].')');
	}
	if(isset($_GET['first']))
	{
		echo('<br>Consultation de : <br>');
		print('<ul><li><a href="PHP1.php?f=1&first='.$_GET['first'].'&last='.$_GET['last'].'"><u>votre etat civil</u></a></li>');
		print('<li><a href="PHP1.php?f=2&adr='.$_GET["adr"]."&cp=".$_GET["cp"]."&ville=".$_GET["ville"].'"><u>votre adresse</u></a></li></ul><br>');
	}
}
if(isset($_GET['f']) AND $_GET['f']==1){
	print("<table><tr><td>Pr&eacute;nom : </td><td>".$_GET['first']."</td></tr><tr><td>Nom :</td><td>".$_GET['last']."</td></tr></table>");
}
if(isset($_GET['f']) AND $_GET['f']==2){
	print("Vous habitez : <br>".$_GET['adr']."<br>".$_GET['cp']." ".$_GET['ville']);
}

?>

</body>
</html>
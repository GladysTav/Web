<!DOCTYPE php>
<h1>Mission Game of Thrones 1</h1>
<?php
 include "maison.php";
 //include "hero.php";
 include "noble.php";
 //include "characters.php";

$IDF = "Île de France";
$Stark = new Maison("Stark","Winter is coming","Un Loup Géant gris sur champ de neige","Il y a longtemps");
$Lannister = new Maison("Lannister","Butez le","Armoireries des Lannister","Longtemps");
$Celte = "Celte";
$Viking = "Viking"; 



array(
1 => $TyrionLannister = new Hero("Tyrion Lannister", 273, "", "Peter Dinklage"),
2 => $JohnSnow = new Hero("John Snow", 283, "", "Kit Harington"),
3 => $DaenerysTargaryen = new Hero("Daenerys Targaryen", 284, "", "Emilia Clarke"),
4 => $AriaStark = new Hero("Aria Stark", 287, "", "Maisie Williams"),
5 => $SansaStark = new Hero("Sansa Stark", 285, "", "Sophie Turner"),
6 => $JoffreyBaratheon = new Noble("Joffrey Baratheon", 282, 301, "Baratheon", "Sansa Stark", "Robert Baratheon", "Cersei Lannister")
);

$AriaStark.$Maison = $Stark;
$SansaStark->$Maison = $Stark;

$Nombre = $JoffreyBaratheon->$nombre;
?>

<form name="nb" action="nbPerso.php" method ="post">
	<input type="submit" value=$Nombre>
</form>
<?php session_start();?>
<!DOCTYPE php>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="wawa">
  <meta name="author" content="wawa">
  <title>Mission PHP - SGBDR</title>
  <script> 
  function showValue(newValue){ document.getElementById("range").innerHTML=newValue ;} 
  </script>
</head>



<?php 
$dsn = 'mysql:dbname=SGBDR;host=localhost'; 
$user = 'pdo_user'; 
$password = 'mdp'; 
 
try { 
    $dbh = new PDO($dsn, $user, $password); 
} catch (PDOException $e) { 
    echo 'Connexion échouée : ' . $e->getMessage(); 
} 
?>

<h1>Mission SGBDR</h1> <br>

<textarea row="500" cols="200" >
<h2>Etape 1</h2>
Create DATABASE "sgbdr"} charset=utf8;

CREATE TABLE IF NOT EXISTS `produit` (
  `NumColl` int(11),
  `RefProd` varchar(50),
  `Design` varchar(50),
  `Couleur` varchar(50),
  `Dim` varchar(50),
  `PrixHT` int(11),
  PRIMARY KEY (`RefProd`),
  KEY `NumColl` (`NumColl`)
);

INSERT INTO `produit` (`NumColl`, `RefProd`, `Design`, `Couleur`, `Dim`, `PrixHT`) VALUES
(1, 'A12', 'Chaise longue', 'Marine', '120x60', 90),
(2, 'B32', 'Parasol', 'Paille', 'diam 90', 45),
(2, 'A14', 'Drap de bain', 'Orangé', '130x80', 65),
(1, 'B25', 'Parasol', 'Ciel', 'diam 110', 60),
(2, 'A15', 'Coussin', 'Paille', '10x30', 12);


CREATE TABLE IF NOT EXISTS `collection` (
  `NumColl` int(11),
  `DateLanc` varchar(15),
  `NomColl` varchar(30),
  `Harmonie` varchar(30),
  PRIMARY KEY (`NumColl`)
);

INSERT INTO `collection` (`NumColl`, `DateLanc`, `NomColl`, `Harmonie`) VALUES
(1, '01/04/2005', 'Marée', 'Bleu'),
(2, '15/04/2005', 'Soleil', 'Jaune');


<h2>Etape 2</h2>
Create User "pdo_user";
GRANT * TO 'pdo_user';



</textarea>
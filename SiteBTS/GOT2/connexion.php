<?php
$log = $_POST["login"];
$password = $_POST["password"];

try {
                $bdd = new PDO('mysql:host=localhost;dbname=got1;charset=utf8', 'root', '',
                   array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $requete = $bdd->prepare('SELECT count(*) FROM utilisateur WHERE login = :username AND Mdp = :password');
$requete->execute(array('username' => $_REQUEST['login'], 'password' => $_REQUEST['password']));
$donnees = $requete->fetch();


if ($donnees[0] == 1)
	 echo "OK";
else
	echo "KO";
            }
catch (Exception $e) {
die('Erreur : ' . $e->getMessage());
echo "error";
  }



?>
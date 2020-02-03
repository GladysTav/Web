<!DOCTYPE php>
<?php
 include "characters.php";
class Hero extends Cara
{
	public $nomActeur;
	public $Maison;
	function __construct($nom, $dateN, $dateM, $nomActeur) {
		print "Nom, date de naissance, date de mort, nom d'acteur";
	}

}

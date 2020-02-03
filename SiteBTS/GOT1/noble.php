<!DOCTYPE php>
<?php
 include "hero.php";
class Noble extends Cara
{
	public $maison;
	public $epou;
	public $pere;
	public $mere;
	function __construct($nom, $dateN, $dateM, $Maison, $epou, $pere, $mere) {
		print "Nom, date de naissance, date de mort, maison, époux/épouse, père, mère";
	}
}
?>
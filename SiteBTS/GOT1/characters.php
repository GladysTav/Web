<!DOCTYPE php>

<?php

class Cara
{
	public $ID;
	public $Nom;
	public $DateN;
	public $DateM;
	static $nombre = 0;

	function __construct($nom, $dateN, $dateM)
	{
		$this.$nombre= $this.$nombre + 1;
		$this.$ID = $this.$nombre;
		$this.$DateN = $dateN;
		$this.$DateM = $dateM;
		$this.$Nom = $nom;
	}
	function nombre()
	{
		return $nombre;
	}

	public function setID($ID) {
  		$this->ID = $ID;
	}

	public function getID() {
  		return $this->ID;
	}

	public function setNom($Nom) {
  		$this->Nom = $Nom;
	}

	public function getNom() {
  		return $this->Nom;
	}

	public function setDateN($DateN) {
  		$this->DateN = $DateN;
	}

	public function getDateN() {
  		return $this->DateN;
	}

	public function setDateM($DateM) {
  		$this->DateM = $DateM;
	}

	public function getDateM() {
  		return $this->DateM;
	}

	public function __toString()
	{
		$string = "ID : ".($this.$ID)."\nNom : ".($this.$Nom)."\nDate de naissance : ".($this.$DateN)."\nDate de mort : ".($this.$DateM);
		return $string;
	}
}

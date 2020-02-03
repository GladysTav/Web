<?php

namespace App;

require 'vendor/autoload.php';

use App\SQLiteConnection;
class PHP4Paie {
  /**
   * PDO object
   * @var \PDO
   */
  private $pdo;
  public function __construct($pdo) {
    $this->pdo = $pdo;
  }


  /*
    <p>  Pseudo de l'agent</p>
    <p>  Prénom et nom de l'agent</p>
    <p>  Nombre de dimanches travaillés</p>
    <p>  Nombre de jours fériés travaillés</p>
    <p>  Date des jours fériés travaillés</p>
  <p>  Jours de repos hebdomadaire sur la/les semaines incluant un jour férié travaillé</p>
  <p>  Nombre d'heures travaillées de nuit hors dimanche, jour férié et heures supplémentaires</p>
    <p>  Nombres d'heures supplémentaires travaillées</p>
    <p>  Justification des heures supplémentaires</p>
    <p>  Personne ayant demandé les heures supplémentaires</p>
    <p>  Personne ayant autorisé les heures supplémentaires</p>
    <p>  Date des jours de congés pris</p>
    <p>  Nombre de jours de congés pris</p>
    <p>  Nature des congés pris</p>
    <p>  Justification en cas de congé autre qu'un congé payé</p>
  <p>  Jours de repos hebdomadaire sur la/les semaines incluant un jour de congé</p>
    <p>  Date des jours de maladie</p>
    <p>  Nombre de jours de maladie</p>
    <p>  Date du dernier jour travaillé pour chaque arrêt maladie</p>
  <p>  Jours de repos hebdomadaire sur la/les semaines incluant des jours de maladie</p>
    <p>  Arrêt de travail fourni sous 48 heures ? Yes/No.</p>
    <p>  Date des jours d'absence hors congé payé, congé sans solde et maladie</p>
    <p>  Nombre de jours d'absence hors congé payé, congé sans solde et maladie</p>
    <p>  Justification des jours d'absence hors congé payé, congé sans solde et maladie</p>
  <p>  Jours de repos hebdomadaire sur la/les semaines incluant des jours d'absence</p>
  <p>  Nombre d'heures de retard</p>
  <p>  Autre (rectification, cas non prévu, etc.) Merci de fournir le plus de détails possible.</p>
    */

// /!\ Calcul des congés et maladie, Je prends toute la période déclarée, peu importe si des jours débordent sur un autre mois
// Il faut compter 5 jours congés + 2 jours repos /semaine

// /!\ calcul des maladies et absence, n'affiche rien si les dates chevauchent un mois


function GetFullName($id){
  $stmt = $this->pdo->query('SELECT fullname FROM employee where id =\''.$id.'\';');
  $count=0;
  $employees = [];
  while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $employees[] = [
          'fullname' => $row['fullname']
      ];
      $count = $count+1;
  }
  if($count==0)
  {
    return -1;
  }
  elseif ($count>1) {
    return -2;
  }
  elseif($count==1) {

    foreach ($employees as $employee) :
        return $employee['id'];
    endforeach;
  }
  else {
    return -2;
  }
}

function GetNbDimanche($id, $mois){
  $sql = $this->pdo->query('SELECT count(*) as rep FROM employee, jour_travaille where employee.id ='.$id
          .' and employee.username = jour_travaille.agent and jour_travaille.dimanche ="True"'
          ." and jour_travaille.date like '".date('Y-m', $mois)."%';");
$shifts = [];
$rep=-1;

    foreach ($sql as $row) {
           $shifts[] = [
                $rep =  $row['rep']
           ];
       }

     return $rep;
}


function GetJF($id, $mois){
  $sql = $this->pdo->query('SELECT jour_travaille.date FROM employee, jour_travaille where employee.id ='.$id
          .' and employee.username = jour_travaille.agent and jour_travaille.ferie ="True"'
          ." and jour_travaille.date like '%".date('Y-m', $mois)."%';");
$shifts = [];


while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
       $shifts[] = [
            'date' => $row['date']
       ];
}

     return $shifts;
}

function GetNbJF($id, $mois){
  $sql = $this->pdo->query('SELECT count(*) as rep FROM employee, jour_travaille where employee.id ='.$id
          .' and employee.username = jour_travaille.agent and jour_travaille.ferie ="True"'
          ." and jour_travaille.date like '".date('Y-m', $mois)."%';");
$shifts = [];
$rep=-1;

    foreach ($sql as $row) {
           $shifts[] = [
                $rep =  $row['rep']
           ];
       }

     return $rep;
}


function GetNbJR_JF($id, $mois){//Jours de repos hebdomadaire sur la/les semaines incluant un jour férié travaillé
  $JF=GetJF($id,$mois);
  $sql = $this->pdo->query('SELECT count(*) as rep FROM employee, jour_travaille where employee.id ='.$id
          .' and employee.username = jour_travaille.agent and jour_travaille.ferie ="True"'
          ." and jour_travaille.date like '".date('Y-m', $mois)."%';");
$shifts = [];
$rep=-1;

    foreach ($sql as $row) {
           $shifts[] = [
                $rep =  $row['rep']
           ];
       }

     return $rep;
}


function GetHS($id, $mois){
  $JF=$this->GetJF($id,$mois);
  $sql = $this->pdo->query("SELECT date, nb, demande, justification, autorise
                            from heures_supp
                            inner join employee on employee.username=heures_supp.agent
                            where employee.id = $id
                            and heures_supp.date like '".date('Y-m', $mois)."%'");

  $shifts = [];
  while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
         $shifts[] = [
              'date' => $row['date'],
              'nb' => $row['nb'],
              'demande' => $row['demande'],
              'justif' => $row['justification'],
              'autorise' => $row['autorise']
         ];
  }

       return $shifts;
}

function GetCP($id, $mois){
  $sql = $this->pdo->query("SELECT date_deb, date_fin
                            from conges_payes
                            inner join employee on employee.username=conges_payes.agent
                            where employee.id = $id
                            and (conges_payes.date_deb like '".date('Y-m', $mois)."%'
                            or conges_payes.date_fin like '".date('Y-m', $mois)."%')");

  $shifts = [];
  while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
      $dates=$this->GetDatesDansMois($row['date_deb'], $row['date_fin'], $mois);
         $shifts[] = [
              'deb' => $dates[0]['date'],
              'fin' => $dates[sizeof($dates)-1]['date'],
              'nb' => sizeof($dates)
         ];
  }

       return $shifts;
}

function GetCNP($id, $mois){
  $sql = $this->pdo->query("SELECT date_deb, date_fin, justification
                            from conges_non_payes
                            inner join employee on employee.username=conges_non_payes.agent
                            where employee.id = $id
                            and (conges_non_payes.date_deb like '".date('Y-m', $mois)."%'
                            or conges_non_payes.date_fin like '".date('Y-m', $mois)."%')");

  $shifts = [];
  while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
      $dates=$this->GetDatesDansMois($row['date_deb'], $row['date_fin'], $mois);

         $shifts[] = [
              'deb' => $dates[0]['date'],
              'fin' => $dates[sizeof($dates)-1]['date'],
              'nb' => sizeof($dates),
              'justif' => $row['justification']
         ];
  }

       return $shifts;
}

function GetMaladie($id, $mois){
  $sql = $this->pdo->query("SELECT date_debut, date_fin, dernier_jour_travaille, fourni_sous_48h
                            from maladie
                            inner join employee on employee.username=maladie.agent
                            where employee.id = $id
                            and (maladie.date_debut like '".date('Y-m', $mois)."%'
                            or maladie.date_fin like '".date('Y-m', $mois)."%')");

  $shifts = [];
  while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
      $dates=$this->GetDatesDansMois($row['date_debut'], $row['date_fin'], $mois);

         $shifts[] = [
              'deb' => $dates[0]['date'],
              'fin' => $dates[sizeof($dates)-1]['date'],
              'nb' => sizeof($dates),
              'dernier_jour' => $row['dernier_jour_travaille'],
              '48h' => $row['fourni_sous_48h']
         ];
  }

       return $shifts;
}

function GetAbsence($id, $mois){
  $sql = $this->pdo->query("SELECT date_debut, date_fin, justification as justif
                            from absence
                            inner join employee on employee.username=absence.agent
                            where employee.id = $id
                            and (absence.date_debut like '".date('Y-m', $mois)."%'
                            or absence.date_fin like '".date('Y-m', $mois)."%')");

  $shifts = [];
  while ($row = $sql->fetch(\PDO::FETCH_ASSOC)) {
      $dates=$this->GetDatesDansMois($row['date_debut'], $row['date_fin'], $mois);

         $shifts[] = [
              'deb' => $dates[0]['date'],
              'fin' => $dates[sizeof($dates)-1]['date'],
              'nb' => sizeof($dates),
              'justif' => $row['justif']
         ];
  }

       return $shifts;
}

function JourSuivant($Jour){
    return date("Y-m-d",strtotime("+1 day", strtotime($Jour)));
}
function GetDatesDansMois($Deb, $Fin, $mois){
  //$DateLundiConvert = str_replace('/', '-', $Lundi);
  //$DateLundiConvert = date("Y-m-d", strtotime($DateLundiConvert));
  $Mois=date("Y-m", $mois);
  $LesDates=[];
  for ($Date=$Deb; $Date <= $Fin; $Date=$this->JourSuivant($Date)) {
    if(date("Y-m", strtotime($Date))==$Mois){
      $LesDates[] =[
        'date' => $Date
      ];
    }
  }
  return $LesDates;
}

}
?>

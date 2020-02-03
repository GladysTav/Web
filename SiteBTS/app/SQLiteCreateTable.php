<?php

namespace App;

  include 'app/crypt.php';
/**
 * SQLite Create Table Demo
 */
class SQLiteCreateTable {

    /**
     * PDO object
     * @var \PDO
     */
    private $pdo;

    /**
     * connect to the SQLite database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }




 public function getByDate() {
     $sql = $this->pdo->query('Select * From article order by Date;');
     $shifts = [];

  foreach (($sql) as $row) {
         $shifts[] = [
              'Date' => $row['Date'],
             'Lien' => $row['Lien'],
             'Resume' => $row['Resume'],
             'Avis' => $row['Avis'],
             'Note' => $row['Note']
         ];
     }
     return $shifts;
 }
    public function getByDateDec() {
     $sql = ('Select * From article order by Date desc;');
     $shifts = [];

  foreach ($this->pdo->query($sql) as $row) {
         $shifts[] = [
              'Date' => $row['Date'],
             'Lien' => $row['Lien'],
             'Resume' => $row['Resume'],
             'Avis' => $row['Avis'],
             'Note' => $row['Note']
         ];
     }
     return $shifts;
 }
    public function getByNote() {
     $sql = ('Select * From article order by Note;');
     $shifts = [];

  foreach ($this->pdo->query($sql) as $row) {
         $shifts[] = [
              'Date' => $row['Date'],
             'Lien' => $row['Lien'],
             'Resume' => $row['Resume'],
             'Avis' => $row['Avis'],
             'Note' => $row['Note']
         ];
     }
     return $shifts;
 }
    public function getByNoteDec() {
     $sql = ('Select * From article order by Note desc;');
     $shifts = [];

  foreach ($this->pdo->query($sql) as $row) {
         $shifts[] = [
              'Date' => $row['Date'],
             'Lien' => $row['Lien'],
             'Resume' => $row['Resume'],
             'Avis' => $row['Avis'],
             'Note' => $row['Note']
         ];
     }
     return $shifts;
 }




}
?>

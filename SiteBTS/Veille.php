<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Site de Gladys TAVENAUX</title>


<link rel="stylesheet" href="css/reset.css" type="text/css" />
<link rel="stylesheet" href="css/styles.css" type="text/css" />
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">


<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/slider.js"></script>
<script type="text/javascript" src="js/superfish.js"></script>

<script type="text/javascript" src="js/custom.js"></script>

<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />

<!--
deiform, a free CSS web template by ZyPOP (zypopwebtemplates.com/)

Download: http://zypopwebtemplates.com/

License: Creative Commons Attribution
//-->
</head>

<style type="text/css">
  input{
   background:radial-gradient(#F1BCF1,#E87FE6);
   color:#000000;
   border-color:#6C6B6C;
   font:bold 13px Arial;
   opacity:0.7;
   border-radius: 10px;
}

input:hover{
  background:radial-gradient(#BB74D1,#EFB2F6);
  box-shadow: inset 2px 2px 2px 2px #F7B3FE;
}
</style>


<body>
<div id="container" class="width">

           <header> 
  <div class="width">
    <nav>
          <ul class="sf-menu dropdown">
              <li><a href="index.html">Accueil</a></li>
              <li><a href="Entreprise.php">Entreprise</a></li>
              <li><a>Projets</a><ul>
                <li><a>Entreprise</a><nav><ul class="sf-menu dropdown" style="margin-top: 0">
                  <li><a href="PointeuseC.php">Pointeuse en C</a></li>
                  <li><a href="Support.php">Le support</a></li>
                  <li><a href="LaRepro.php">Le laboratoire de reproduction de bug</a></li>
                  <li><a href="PowerShell.php">PowerShell</a></li>
                  <li><a href="Badgeuse.php">Badgeuse</a></li></ul></nav></li>
                <li><a>Ecole</a><nav><ul class="sf-menu dropdown" style="margin-top: 0">
                  <li><a href="GOT1/index.php">Mission Game of Thrones 1</a></li>
                  <li><a href="GOT2/index.html">Mission Game of Thrones 2</a></li>
                  <li><a href="GOT3/villes.html">Mission Game of Thrones 3</a></li>
                  <li><a href="GOT4/client/index.php">Mission Game of Thrones 4</a></li>
                  <li><a href="PPE1/index.html">Le closed-loop marketing</a></li>
                  <li><a href="PPE2/index.html">Application de notation repas&hôtel</a></li>
                  <li><a href="PPE3/index.html">Application de gestion de tickets incidents</a></li>
                  <li><a href="PPE4/index.php">Application de gestion des demandes de mutation</a></li></ul></nav></li>
                </ul></li>
              <li class="selected"><a href="Veille.php">Veille</a></li>
              <li><a href="Analy_cri.php">Analyse critique</a></li>
              <li><a href="Contact.html">Mentions légales</a></li>

            </ul> <div class="clear"></div>
        </nav>
        </div>
  <div class="clear"></div>
    </header>

    
   <div id="body" class="width">



    <section id="content" >

      <article>
  <div style="margin-left: 1em; margin-top: 2em;">
	<h1>Présentation de la veille technologique</h1>
	<h2>Le Cloud-Gaming</h2><br>
	<p style="text-indent:1em; color: #BBBBBB;">L'entreprise dans laquelle je suis, Blade, a créé Shadow.
Cet ordinateur distant super puissant est en réalité une machine virtuelle délocalisée, ce que l'on peut appeller Cloud Computing.
En prenant en compte l'aspect "gaming" du Shadow, nous pouvons donc le qualifier de "Cloud Gaming".
</p><p style="color:#BBBBBB;">
C'est ainsi que j'ai trouvé les mots-clefs à intégrer dans mes alertes Google, lorsque mon maître d'apprentisage Mr. Moreau m'a demandé de mener une veille technologique pour surveiller nos concurrents. Cette veille me sert donc pour le BTS, puisque ces mêmes alertes sont celles que je surveille pour la veille demandée.
Mon sujet de veille porte donc sur le Cloud Gaming.
</p><p style="color:#BBBBBB;">
Certes, ce sujet est utile à mon entreprise, et j'avais déjà commencé la veille avant que cela ne me soit demandé pour l'école. Mais égalemment, quoi de plus magique qu'un ordinateur dans internet ?
En plus que le commun du Cloud Computing, Shadow permet de jouer à des jeux PC nécessitant beaucoup de ressources sur un PC bas de gamme, ne pouvant seulement décoder une vidéo. Shadow peut égalemment être utilisé sur tablette, smartphone ou grâce au petit boîtier remplaçant une tour PC !
Le Cloud Gaming est voué à remplacer les PC gamer actuels, et Shadow sera le leader du milieu.
</p><br><br><br>

<h2>Evolution chronologique de la veille technologique</h2>

<table class="BorderLess" style="align-items: center; align-self: center; text-align: center; width: 20em;"><tr><td>
    <form method="POST" action="Veille.php">
      <input type="hidden" name="tri" value="Date">
      <input type="submit" value="Trier par date croissante">
    </form></td><td>
    <form method="POST" action="Veille.php">
      <input type="hidden" name="tri" value="DateDesc">
      <input type="submit" value="Trier par date décroissante">
    </form></td><td>
    <form method="POST" action="Veille.php">
      <input type="hidden" name="tri" value="Note">
      <input type="submit" value="Trier par note croissante">
    </form></td><td>
    <form method="POST" action="Veille.php">
      <input type="hidden" name="tri" value="NoteDesc">
      <input type="submit" value="Trier par note décroissante">
    </form></td></tr></table>

<?php 
require 'vendor/autoload.php';
use App\SQLiteConnection as SQLiteConnection;
use App\SQLiteCreateTable as SQLiteCreateTable;

$bdd=new SQLiteCreateTable((new SQLiteConnection())->connect());


?>
<table id='letableau' border="1">
	<thead>
   <tr>
      <th style="width: 5em;">Date</th>
      <th style="width: 3em;">Lien</th>
      <th style="width: 25em;">Résumé</th>
      <th style="width: 20em;">Avis</th>
      <th style="width: 3em;">Note</th>
   </tr>
	</thead>
    
      <?php

      if(isset($_POST['tri'])){
        $choix=$_POST['tri'];
        if ($choix=="Date"){
          foreach ($bdd->getByDate() as $row) {
              ?><tr>
                <tbody>
                  <td> <?php print $row['Date'] . "\t"; ?></td>
                  <td><a href=<?php print $row['Lien'] . "\t";?>>Lien</a></td>
                  <td> <?php print $row['Resume'] . "\t";?></td>
                  <td><?php print $row['Avis'] . "\t";?></td>
                  <td><?php print $row['Note'] . "\n";?></td>
                 </tbody></tr>
                <?php
            }
            
        }
        elseif($choix=="Note"){
          foreach ($bdd->getByNote() as $row) {
              ?><tr>
                <tbody>
                  <td> <?php print $row['Date'] . "\t"; ?></td>
                  <td><a href=<?php print $row['Lien'] . "\t";?>>Lien</a></td>
                  <td> <?php print $row['Resume'] . "\t";?></td>
                  <td><?php print $row['Avis'] . "\t";?></td>
                  <td><?php print $row['Note'] . "\n";?></td>
                 </tbody></tr>
                <?php
            }
          }
        elseif($choix=="NoteDesc"){
          foreach ($bdd->getByNoteDec() as $row) {
              ?><tr>
                <tbody>
                  <td> <?php print $row['Date'] . "\t"; ?></td>
                  <td><a href=<?php print $row['Lien'] . "\t";?>>Lien</a></td>
                  <td> <?php print $row['Resume'] . "\t";?></td>
                  <td><?php print $row['Avis'] . "\t";?></td>
                  <td><?php print $row['Note'] . "\n";?></td>
                 </tbody></tr>
                <?php
            }
          }
        elseif($choix=="DateDesc"){
          foreach ($bdd->getByDateDec() as $row) {
              ?><tr>
                <tbody>
                  <td> <?php print $row['Date'] . "\t"; ?></td>
                  <td><a href=<?php print $row['Lien'] . "\t";?>>Lien</a></td>
                  <td> <?php print $row['Resume'] . "\t";?></td>
                  <td><?php print $row['Avis'] . "\t";?></td>
                  <td><?php print $row['Note'] . "\n";?></td>
                 </tbody></tr>
                <?php
            }
            
        }
      }
      else{

          foreach ($bdd->getByDate() as $row) {
              ?><tr>
                <tbody>
                  <td> <?php print $row['Date'] . "\t"; ?></td>
                  <td><a href=<?php print $row['Lien'] . "\t";?>>Lien</a></td>
                  <td> <?php print $row['Resume'] . "\t";?></td>
                  <td><?php print $row['Avis'] . "\t";?></td>
                  <td><?php print $row['Note'] . "\n";?></td>
                 </tbody></tr>
                <?php
            } 
    }?>



</table>




</div></article></section></div>
    <footer>
        <div class="footer-bottom">
            <p>&copy; Gladys TAVENAUX</p>
         </div>
    </footer>
    
</body>
</html>


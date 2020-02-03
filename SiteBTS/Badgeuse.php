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
</head>
<body>
<div id="container" class="width">

            <header> 
  <div class="width">
    <nav>
          <ul class="sf-menu dropdown">
              <li><a href="index.html">Accueil</a></li>
              <li><a href="Entreprise.php">Entreprise</a></li>
              <li class="selected"><a>Projets</a><ul>
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
              <li><a href="Veille.php">Veille</a></li>
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
        <div style="margin-left:1em; margin-top:2em;"><br>


          <h1>Présentation</h1>
  <p style="margin-left: 2em">Le support  est constitué du Head of Support, des chefs d'équipe, et des agents.</p>
  <p>Les agents ont un emploi du temps oragnisé en shift. C'est à dire qu'ils savent, une semaine à l'avance, quels jours ils travaillent, et à quelles horaires (9h-17h ou 12h-00h).</p>
  <p>Le retard d'un agent pénalisera donc son collègue, qui devra supproter la charge de travail seul.</p>
<p><br>Afin d'éviter cela, le Head of Support souhaite contrôler les horaires d'arrivée, départ, et les pauses des agents avec un logiciel simple sur les PC du bureau.</p>
<p>Cependant, le nouveau règlement RGPD impose que le contrôle des horaires au bureau soit fait par un système de badge.</p>
<p>Ainsi, mon collègue a programmé une badgeuse UFC en python sur une raspberry. Il m'a montré comment faire, et nous avons discuté du code pour l'améliorer et contourner quelques problématiques.</p>
<p>De mon côté, j'ai codé le site permettant d'accèder à ces données. J'y ai également ajouté plusieurs outils utiles au support.</p>
<br>
<h1>Description</h1>
<p style="margin-left: 2em">Mon collègue a installé les drivers pour la badgeuse sur une Raspberry Pie. Il m'a montré comment il faisait, et on a pu échanger des idées concernant le code en python, puisque la détection et le traitement se font dans deux versions de python différentes.</p>
<p>De mon côté, j'ai codé un site web avec Xampp et apache afin de pouvoir consulter les données de la badgeuse. On m'a égalemment présenté des idées à ajouter au site tel qu'un système de récompenses pour la ponctualité.</p>
<div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/1"></div><br>
<p>A l'arrivée sur le site, vous tombez sur cette page. Il faudra donc renseigner un pseudo et un mot de passe correspondants à la base de données.</p>
<p>Si vous arrivez sur une autre page du site sans être connecté, le PHP vous redirigera sur cette même page.</p><br><br>
<div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/2"></div><br>
<p>Voici la page d'accueil et centrale du site. On peut y consulter nos données de la badgeuse, et naviguer sur le site.</p>
<p>Deux boutons permettent de télécharger ces données, soit en CSV, soit en PDF.</p>
<p>Selon votre statut (Agent, Chef ou Admin), vous avez accès à plus ou moins de pages. Ces restrictions se font via php, qui affiche ou non les boutons et redirigent les utilisateurs qui sont tombés sur une page à laquelle ils n'ont pas accès.</p><br><br>
<div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/3"></div><br>
<p>La page MonCompte permet de modifier son mot de passe.</p>
<p>Un mot de passe doit contenir au moins 8 caractères dont au moins 3 parmis : majusculee, minuscule, chiffre, caractère spécial.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/4"></div><br>
<p>Cette page présente les récompenses attribuées pour la ponstualité.</p>
<p>Le premier tableau affiche toutes les récompenses disponibles, et le second affiche celles acquises par l'utilisateur.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/5"></div><br>
<p>La page de rendez-vous affiche ceux que l'utilisateur a créé, ceux auxquels il est invité, et ceux qui sont passés il y a moins d'une semaine.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/6"></div><br>
<p>Touss les utlisateurs peuvent créer et répondre à un rendez-vous. On ne peut supprimer que ceux que l'on a nous-même créé.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/7"></div><br>
<p>La page des shifts affiche les shifts de la semaine suivante, ou bien ceux de la semaine choisie dans la liste déroulante.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/8"></div><br>
<p>Après avoir consulté les choix de ses coéquipiers, un utilisateur renseigne ses préférences en cliquant sur des CheckBox associées à des emojis.</p>
<p>Ces choix sont enregistrés dans la base de données avec une couleur associée (vert pour un shift voulu, rouge sinon) et seront écrasés par la couleur des shifts définitifs (bleus).</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/9" style=" height: 50em;"></div><br>
<p>Sur cette page, les chefs d'équipe et les administrateurs peuvent ajouter un utilisateur ou modifier ses données.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/10"></div><br>
<p>Ils peuvent également gèrer les équipes en ajoutant ou supprimant des agents ou chefs à celles-ci.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/11"></div><br>
<p>Les chefs et admin peuvent modifier les données de la pointeuse, au cas où un agent aurait oublié de badger par exemple.</p>
<p>Toutes les modifications de la base de données via le site sont enregistrées dans une table Modification.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/12"></div><br>
<p>Cette page n'est accessible que par les administarteurs.</p>
<p>Elle permet de consulter toutes les données de la badgeuse, les tables de la base de données, toutes les modifications, les utilisateurs, et les récompenses.</p>
<p>Il suffit de cliquer sur le bouton pour faire apparaître la div correspondante en dessous.</p>
<p>C'est également ici que l'on peut ajouter des récompenses à la base de données.</p>
<br><br><div class="AccueilContainer" style="width: 90%; padding: 8px;"><img src="screen/13"></div><br>
<p>La page Shift & paies permet de faciliter l'attribution de shifts définitifs.</p>
<p>Elle affiche par défaut le planning de la semaine courante et celui de la semaine suivante, mais on peut choisir la semaine dans le menu déroulant.</p>
<p>On peut ainsi attribuer un shift (matin ou soir) en cliquant sur le S correspondant, ou attribuer le jour de repos en cliquant sur R.</p>
<br><br>
<p>Maintenant, un collègue m'a fourni un template en VueJs et m'a demandé de migrer le sie dessus. Je commence donc à comprendre son fonctionnement et y ai ajouté mes pages.</p>

        </div></article></section></div>

    <footer>
        <div class="footer-bottom">
            <p>&copy; Gladys TAVENAUX</p>
         </div>
    </footer>
</body>
</html>

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
                  <li><a href="../../PPE4/index.php">Application de gestion des demandes de mutation</a></li></ul></nav></li>
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
    <p style="margin-left: 2em;">Le laboratoire de Reproduction (LaRepro) a pour but de recréer les bugs rencontrés par les utilisateurs, pour en comprendre la source et transmettre ces informations aux développeurs.</p>
    <p>LaRepro est dirigée par Emmanuel S. Matthieu et moi sommes là à temps partiel pour l'aider (lui 2 jours sur 3, moi une semaine sur deux).</p>
    <br>
    <h1>Missions</h1>
    <p>Ayant peu de connaissances techniques comparées à mes collègues, ma mission principale était de chercher des informations sur Zendesk et discord, pour trouver des bugs à reroduire, et des indices sur comment les reproduire.</p>
    <p>Une feuille de tech Offenders que l'on remplit au fil de l'avancement de résolution :</p>
    <img src="logo/techOffenders"><br>
    <br>
    <p>Une fois le bug reproduit, on renseigne les conditions, l'environnement, le déroulé, et le plus d'informations possibles sur ce bug dans un ticket JIRA.</p>
    <img src="logo/jira"><br><br>
    <p>On reçoit aussi souvent des missions par les devs. Sur le document ci-dessous, je devais tester les effets d'une commande :</p>
    <img src="logo/commandeSG"><br><br>
    <p>Comme nous sommes ammenés à souvent 'casser' nos VM, nous les remettons souvent à zéro (RAZ). Pour éviter la perte de temps liée à la réinstallation de tous les logiciels, j'ai donc créé un script en PowerShell pour automatiser les installations des logiciels que nous utiisons le plus. J'en parle plus en profondeur <a href="PowerShell.php">ici</a>.</p>

        </div></article></section></div>

    <footer>
        <div class="footer-bottom">
            <p>&copy; Gladys TAVENAUX</p>
         </div>
    </footer>
</body>
</html>

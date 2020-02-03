<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Site de Gladys TAVENAUX</title>
<link rel="stylesheet" href="../../../css/reset.css" type="text/css" />
<link rel="stylesheet" href="../../../css/styles.css" type="text/css" />
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="../../../js/jquery.js"></script>
<script type="text/javascript" src="../../../js/slider.js"></script>
<script type="text/javascript" src="../../../js/superfish.js"></script>
<script type="text/javascript" src="../../../js/custom.js"></script>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
</head>
<div id="container" class="width">

            <header> 
  <div class="width">
    <nav>
          <ul class="sf-menu dropdown">
              <li><a href="../../index.html">Accueil</a></li>
              <li><a href="../../Entreprise.php">Entreprise</a></li>
              <li class="selected"><a>Projets</a><ul>
                <li><a>Entreprise</a><nav><ul class="sf-menu dropdown" style="margin-top: 0">
                  <li><a href="../../PointeuseC.php">Pointeuse en C</a></li>
                  <li><a href="../../Support.php">Le support</a></li>
                  <li><a href="../../LaRepro.php">Le laboratoire de reproduction de bug</a></li>
                  <li><a href="../../PowerShell.php">PowerShell</a></li>
                  <li><a href="../../Badgeuse.php">Badgeuse</a></li></ul></nav></li>
                <li><a>Ecole</a><nav><ul class="sf-menu dropdown" style="margin-top: 0">
                  <li><a href="../../GOT1/index.php">Mission Game of Thrones 1</a></li>
                  <li><a href="../../GOT2/index.html">Mission Game of Thrones 2</a></li>
                  <li><a href="../../GOT3/villes.html">Mission Game of Thrones 3</a></li>
                  <li><a href="../../GOT4/client/index.php">Mission Game of Thrones 4</a></li>
                  <li><a href="../../PPE1/index.html">Le closed-loop marketing</a></li>
                  <li><a href="../../PPE2/index.html">Application de notation repas&hôtel</a></li>
                  <li><a href="../../PPE3/index.html">Application de gestion de tickets incidents</a></li>
                  <li><a href="../../PPE4/index.php">Application de gestion des demandes de mutation</a></li></ul></nav></li>
                </ul></li>
              <li><a href="../../Veille.php">Veille</a></li>
              <li><a href="../../Analy_cri.php">Analyse critique</a></li>
              <li><a href="../../Contact.html">Mentions légales</a></li>

            </ul> <div class="clear"></div>
        </nav>
        </div>
  <div class="clear"></div>
    </header>

<body>
<div id="body" class="width">
    <section id="content" class="two-column with-right-sidebar">
      <article>
        <div style="margin-left:1em; margin-top:2em;">
        	
<form method="post" action="index.php" style="border: 3px solid black; background: grey; border-radius: 10px;">
	<input type="password" placeholder="Mot de passe" name="pass" id="pass" style="margin: 3px; border-radius: 5px; border: 1px solid black; margin-left: 10%;"> <input type="submit" name="dl" id='dl' value="Télécharger le zip" style="float: right; margin: 3px; border-radius: 5px; border: 1px solid black; background: #9B53EE; margin-right: 10%;">
</form>
<script>
	$('#dl').click(function () {
    if ($('#pass').val()=="YouShallNotPass"){
        //window.alert('<a href="GOT4.zip" OnClick="load()">Télécharger<a/>');
        window.open('PPE4.zip',"_blank", null);
    }
    else{window.alert('Mauvais mot de passe');}
});
</script>
<br>
<h2>Application de gestion des demandes de mutation</h2><br>
<h3>Sujet :</h3>
<h4>1. Objectif</h4><p>
Le DSI de l’entreprise GSB souhaite fournir aux visiteurs médicaux, une application connectée à une
base de données pour formuler leurs demandes de mutations.</p><p>
Le DSI désire mettre en place une application permettant d’automatiser l’affectation annuelle des
visiteurs :<br>
- vœu d’affectation composé d’un choix de 3 régions maximum,<br>
- calcul automatique de l’affectation des visiteurs en fin d’année,<br>
- visualisation des affectations.</p><p>
Rappel : les visiteurs médicaux sont répartis par région et secteur géographique.</p><p>
Les administrateurs et/ou les gestionnaires de l’application auront en charge la gestion de leurs demandes
de mutations des visiteurs.</p><p>
Les acteurs du S.I. sont :<br>
- les 480 visiteurs médicaux en France métropolitaine et les 60 dans les départements et
territoires d’outre-mer, répartis en 7 secteurs géographiques,<br>
- le gestionnaire du service des affectations de région (RH : Ressources Humaines).</p>

<h4>2. Utilisateurs concernés</h4><p>
- Le visiteur médical<br>
- Le gestionnaire RH</p>

<h4>3. Proposition d’une application développée en C#</h4><p>
L’application intitulée Gestion des demandes de mutation sera développée en Visual C#, la base de
données sera gérée avec MySQL ou SQL Server.</p>
<h5>a. Codage</h5><p>
Le codage de l'application doit respecter les normes de codage du langage C#.</p>
<h5>b. Environnement</h5><p>
Le développement sera effectué à l'aide de Visual Studio C#, avec une base de données
MySQL ou SQL Server.</p>
<h5>c. Graphisme et ergonomie</h5><p>
L'allure générale de l’application se caractérisera par :<br>
- Un respect de la charte graphique de GSB (logo, typographie).</p>

<h4>4. Design</h4><p>
Le design devra permettre une bonne ergonomie. Le maximum d’informations sera affiché pour
permettre une certaine clarté des informations. Des raccourcis devront être mis en place pour assurer
une rapidité d’exécution.</p>
<br><br>
</div></article></section></div></body>

<aside class="sidebar big-sidebar right-sidebar">
  
  
            <ul>  
               <li>
                  <ul class="blocklist">
                    <li><a class="selected" href="index.html">Sujet</a></li>
                    <li><a href="mcd.php">MCD</a></li>
                    <li><a href="mld.php">MLD</a></li>
                    <li><a href="user.php">Documentation utilisateur</a></li>
                    <li><a href="tech.php">Documentation technique</a></li>
                   </ul>
                </li>
            </ul>
            </aside>
      <div class="clear"></div>
      </div>

    <footer>
        <div class="footer-bottom">
            <p>&copy; Gladys TAVENAUX</p>
         </div>
    </footer>
</body>
</html>

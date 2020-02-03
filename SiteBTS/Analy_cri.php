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




<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">





</head>

<style type="text/css">
#container2 {
	min-width: 320px;
	max-width: 800px;
	margin: 0 auto;
}
		</style>
<body>
	<div id="mapage">
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
              <li><a href="Veille.php">Veille</a></li>
              <li class="selected"><a href="Analy_cri.php">Analyse critique</a></li>
              <li><a href="Contact.html">Mentions légales</a></li>

            </ul> <div class="clear"></div>
        </nav>
        </div>
  <div class="clear"></div>
    </header>
       
<div id="body" class="width">



    <section id="content" style="width: 98%">

      <article>
	<br> <br>
	<h1>Analyse critique</h1><br>
	<!--<p style="text-indent:1em">
A la fin des deux ans, bilan des ces deux années, acquis / en cours d'acquisition, ce que j'aurais aimé voir
En terme de jeu (what ?) pour montrer ce qu'on sait faire<br>
conclusion des deux années pro<br>
PAS DE REDACTION<br>
FORMAT WEB DEV, sous la forme qu'on veut<br>
Mots, concepts, adjectifs, axes d'amélioration<br>
Analyse de moi par moi<br>
Voir ce qui s'est passé durant ces deux ans du point de vu pro<br>
Acquis / en cours d'acquisition<br>
<br><br><br>

Animation pour l'oral de BTS, qui appuie la communication, montre ce qu'on sait faire en tant que dev web<br>

Illustre la fin de l'oral
</p>-->
</div>

</article></section></div>

<div id="body" class="width">
    <section id="content" style="width: 98%">

      <article>

<div id="graphique" class="width" style="align-items: center;">

<script src="../../code/highcharts.js"></script>
<script src="../../code/modules/networkgraph.js"></script>

<div id="container2" class="width" style="width:200em;"></div>



		<script type="text/javascript">
// Add the nodes option through an event call. We want to start with the parent
// item and apply separate colors to each child element, then the same color to
// grandchildren.
Highcharts.addEvent(
    Highcharts.seriesTypes.networkgraph,
    'afterSetOptions',
    function (e) {
        var colors = Highcharts.getOptions().colors,
            i = 0,
            nodes = {};
        e.options.data.forEach(function (link) {

            if (link[0] === 'Moi') {
                nodes['Moi'] = {
                    id: 'Moi',
                    marker: {
                        radius: 40
                    }
                };
                nodes[link[1]] = {
                    id: link[1],
                    marker: {
                        radius: 30
                    },
                    color: colors[i=i+2]
                };
            } else if (nodes[link[0]] && nodes[link[0]].color) {
                nodes[link[1]] = {
                    id: link[1],
                    color: nodes[link[0]].color
                };
            }
        });

        e.options.nodes = Object.keys(nodes).map(function (id) {
            return nodes[id];
        });
    }
);

Highcharts.chart('container2', {
    chart: {
        type: 'networkgraph',
        height: '100%'
    },
    title: {
        text: 'Compétences acquises durant le BTS SIO SLAM'
    },
    subtitle: {
        text: '2017-2019'
    },
    plotOptions: {
        networkgraph: {
            keys: ['from', 'to'],
            layoutAlgorithm: {
                enableSimulation: true
            }
        }
    },
    series: [{
        dataLabels: {
            enabled: true
        },
        data: [
        	['Moi', 'ITESCIA'],
        	['Moi', 'Blade'],
        	['ITESCIA','Code'],
        	['ITESCIA','Web'],
        	['ITESCIA','Droit'],
        	['ITESCIA','Logiciels'],
        	['Blade','Support'],
        	['Blade','LaRepro'],
        	['Blade','Pointeuse'],
        	['Code','C#'],
        	['Code','vb.net'],
        	['Code','SQL'],
        	['Web','HTML'],
        	['Web','CSS'],
        	['Web','PHP'],
        	['Web','JavaScript'],
        	['Droit','RGPD'],
        	['Logiciels','Visual Studio'],
        	['Logiciels','MSProject'],
        	['Logiciels','Wamp'],
        	['Wamp','Apache'],
        	['Wamp','MySQL'],
        	['Wamp','Maria DB'],
        	['Wamp','PHP my admin'],
        	['Support','Communication'],
        	['Support','Expression écrite'],
        	['Support','Assistance'],
        	['LaRepro','PowerShell'],
        	['PowerShell','Ascii Art'],
        	['PowerShell','Installation automatique'],
        	['PowerShell','Auto-formation'],
        	['PowerShell','Autonomie'],
        	['LaRepro','Recherche de bug'],
        	['LaRepro','Rapport de bug'],
        	['LaRepro','Recherche d\'information'],
        	['LaRepro','Organisation du travail'],
        	['LaRepro','Communication interne'],
        	['Pointeuse','C'],
        	['Pointeuse',' C#'],
        	['C',' Auto-formation'],
        	['C',' Autonomie'],
        	[' C#','  Auto-formation'],
        	[' C#','  Autonomie'],
        	['Pointeuse','Site Web'],
        	['Site Web',' HTML'],
        	['Site Web',' CSS'],
        	['Site Web',' PHP'],
        	['Site Web',' JavaScript'],
        	['Site Web',' Apache'],
            ['Site Web','SQLite'],
            ['Site Web','VueJs'],
            ['Site Web','   Auto-formation'],
            ['Site Web','   Autonomie'],
        	['Site Web','Xampp']

        ]
    }]
});


		</script>


</div>

<br><br>
<a class="lienPages" href="tableauSyntheseComplet.pdf" onclick="window.open(this.href); return false;">Consulter le tableau de synthèse</a>

</article>
</section>
</div>



    	<div class="clear" style="clear:both">
    <footer>
        <div class="footer-bottom">
            <p>&copy; Gladys TAVENAUX</p>
         </div>
    </footer>
     </div>
</body>
</html>
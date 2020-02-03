
var navigbar_toggle = true;
function navigbar_heure(id)
{
        date = new Date;
        h = date.getHours();
        if(h<10)
        {
                h = "0"+h;
        }
        m = date.getMinutes();
        if(m<10)
        {
                m = "0"+m;
        }
        s = date.getSeconds();
        if(s<10)
        {
                s = "0"+s;
        }
        resultat = h+':'+m+':'+s;
        document.getElementById(id).innerHTML = resultat;
        setTimeout('navigbar_heure("'+id+'");','1000');
        return true;
}
function navigbar_toggle_oc()
{
	if(navigbar_toggle)
    {
    	document.getElementById('navigbar_4529_nav').style.display="none";
    	document.getElementById('navigbar_4529_openbtn').style.display="block";
	    navigbar_toggle = false;
    }
    else
    {
    	document.getElementById('navigbar_4529_nav').style.display="block";
    	document.getElementById('navigbar_4529_openbtn').style.display="none";
	    navigbar_toggle = true;
    }
	return true;
}

var navig_bar_html = "<div class=\"navigbar_4529\" id=\"navigbar_4529_nav\"><div class=\"navigbar_4529_c1\"><div class=\"navigbar_4529_c2\"><div class=\"navigbar_4529_ctn\">"
+"    <div class=\"navigbar_4529_left\">"
+"        <div id=\"navigbar_4529_heure\" class=\"navigbar_4529_heure\"></div><div class=\"navigbar_4529_separator\"></div>"
+"        <script type=\"text/javascript\">navigbar_heure('navigbar_4529_heure');</script>"
+"    </div>"
+"    <div class=\"navigbar_4529_center\">"
+"        <a href=\"index.html\">Accueil</a>"
+"        <a href=\"Missions.php\">Missions</a>"
+"        <a href=\"Entreprise.php\">Présentation de l'entreprise</a>"
+"        <a href=\"Analy_cri.php\">Analyse critique</a>"
+"        <a href=\"Veille.php\">Veille technologique</a>"
+"        <a href=\"Situ_pro.php\">Situation professionnelle</a>"
+"        <a href=\"mentionslegales.html\">Mentions légales</a>"
+"	  </div><div class=\"navigbar_4529_separator\"></div>"
+"	</div>"
+"</div></div></div></div>"
+"<div class=\"navigbar_4529_openbtn\" id=\"navigbar_4529_openbtn\"><div class=\"navigbar_4529_c1\"><div class=\"navigbar_4529_c2\"><div class=\"navigbar_4529_ctn\">"
+"	<div class=\"navigbar_4529_open\" id=\"navigbar_4529_open\" onclick=\"javascript:navigbar_toggle_oc();\"></div>"
+"</div></div></div><div style=\"clear:both;\"></div></div>"
+"<style type=\"text/css\">"
+".navigbar_4529, .navigbar_4529_openbtn"
+"{"
+"	direction: ltr;"
+"	height:30px;"
+"	position:fixed;"
+"	bottom:0px;"
+"	margin:0px;"
+"	padding:0px;"
+"	width:100%;"
+"	z-index:1004;"
+"}"
+".navigbar_4529 *, .navigbar_4529_openbtn *"
+"{"
+"	border:0px;"
+"}"
+".navigbar_4529 div"
+"{"
+"	vertical-align:top;"
+"}"
+".navigbar_4529_openbtn"
+"{"
+"	display:none;"
+"}"
+".navigbar_4529_openbtn .navigbar_4529_ctn"
+"{"
+"	width:30px;"
+"}"
+".navigbar_4529_c1"
+"{"
+"	background:url('http://services.supportduweb.com/navigbar/styles/s1_1.png') no-repeat left top;"
+"	height:30px;"
+"	width:90%;"
+"	margin:auto;"
+"}"
+".navigbar_4529_c2"
+"{"
+"	height:30px;"
+"	margin-left:10px;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/s1_3.png') no-repeat right top;"
+"}"
+".navigbar_4529_ctn"
+"{"
+"	height:30px;"
+"	margin-right:10px;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/s1_2.png') repeat-x;"
+"}"
+".navigbar_4529_left"
+"{"
+"	float:left;"
+"}"
+".navigbar_4529_center"
+"{"
+"	float:left;"
+"}"
+".navigbar_4529_bookmarker"
+"{"
+"	vertical-align:top;"
+"	padding-top:5px;"
+"	display:inline-block;"
+"}"
+".navigbar_4529_separator"
+"{"
+"	display:inline-block;"
+"	height:30px;"
+"	width:2px;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/btns.png') no-repeat;"
+"	background-position:-48px 0px;"
+"	margin-left:6px;"
+"	margin-right:6px;"
+"}"
+".navigbar_4529_heure"
+"{"
+"	padding-top:5px;"
+"	font-weight:bold;"
+"	display:inline-block;"
+"	}"
+".navigbar_4529_ltop"
+"{"
+"	cursor:pointer;"
+"	width:24px;"
+"	height:21px;"
+"	margin-top:4px;"
+"	margin-right:4px;"
+"	display:inline-block;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/btns.png') no-repeat;"
+"	background-position:0px 0px;"
+"}"
+".navigbar_4529_ltop:hover"
+"{"
+"	background-position:-24px 0px;"
+"}"
+".navigbar_4529_lbottom"
+"{"
+"	cursor:pointer;"
+"	width:24px;"
+"	height:21px;"
+"	margin-top:4px;"
+"	display:inline-block;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/btns.png') no-repeat;"
+"	background-position:0px -21px;"
+"}"
+".navigbar_4529_lbottom:hover"
+"{"
+"	background-position:-24px -21px;"
+"}"
+".navigbar_4529_close"
+"{"
+"	cursor:pointer;"
+"	width:30px;"
+"	height:28px;"
+"	margin-top:1px;"
+"	display:inline-block;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/btns.png') no-repeat;"
+"	background-position:0px -42px;"
+"}"
+".navigbar_4529_close:hover"
+"{"
+"	background-position:-30px -42px;"
+"}"
+".navigbar_4529_open"
+"{"
+"	cursor:pointer;"
+"	width:30px;"
+"	height:28px;"
+"	margin-top:1px;"
+"	display:inline-block;"
+"	background:url('http://services.supportduweb.com/navigbar/styles/btns.png') no-repeat;"
+"	background-position:0px -70px;"
+"}"
+".navigbar_4529_open:hover"
+"{"
+"	background-position:-30px -70px;"
+"}"
+"</style>"
+"";
if(typeof jqueryInnerHTML != 'undefined' && jqueryInnerHTML)
{
	$('#navigbar_4529').html(navig_bar_html);
}
else
{
	document.write(navig_bar_html);
}
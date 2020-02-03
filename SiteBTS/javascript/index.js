<!DOCTYPE javascript>
<html>
<meta charset="utf-8">
<head>
  <title>Calculatrice</title>
</head>


<body>

 <script type="text/javascript">

alert(parsInt(a)+parseInt(b));
var val = parseInt(prompt("Val"));
if(val>20)
  alert("Supérieur");
else(val <20);
  alert("Inférieur ");

  var a = parseInt(prompt("a"));
  var b = parseInt(prompt("b"));
  var choix = parseInt(prompt("1 Addition, 2 soustraction, 3division, 4 multiplication"));
  switch (choix) {
    case 1:
      alert(a+b);
      break;
      case 2:
        alert(a-b);
        break;
        case 3:
          alert(a/b);
          break;
        case 4:
            alert(a*b);
            break;
    default:
alert("Aucun Choix");
  }
  var cum = 0;
  for(var i = 0;i<5;i++){
    cum += parseInt(prompt("a"));
  }
alert(cum);

var cum = 1;
do{
  cumul *=parseInt(prompt("a"));
  s=parseInt(prompt("1 pour sortir"));
}
  while (s!=1);
  alert(cum);

  function monnom(nom){
    alert("bonjour "+nom);
  }
  monnom(prompt("YouYouYou"));
  var tb = [];
  for(var i = 0;i<10;i++)
  {
    tb.push(parseInt(prompt("val"+i)));
  }

  tb.sort(function(a,b){
    return a - b;
  } );
  console.log(tb);



  <script src="Js.js"></script>
</form>

<script type="text/javascript">
alert("Bonjour");
var nom = prompt("nom");
var prenom = prompt("prenom");
alert (nom+prenom);
var a = prompt("a");
var b = prompt("b");

alert(parseInt(a)+parseInt(b));
var val = parseInt(prompt("Val"));
if(val>20)
  alert("Supérieur");
else(val <20);
  alert("Inférieur ");

  var a = parseInt(prompt("a"));
  var b = parseInt(prompt("b"));
  var choix = parseInt(prompt("1 Addition, 2 soustraction, 3division, 4 multiplication"));
  switch (choix) {
    case 1:
      alert(a+b);
      break;
      case 2:
        alert(a-b);
        break;
        case 3:
          alert(a/b);
          break;
        case 4:
            alert(a*b);
            break;
    default:
alert("Aucun Choix");
  }
  var cum = 0;
  for(var i = 0;i<5;i++){
    cum += parseInt(prompt("a"));
  }
alert(cum);

var cum = 1;
do{
  cumul *=parseInt(prompt("a"));
  s=parseInt(prompt("1 pour sortir"));
}
  while (s!=1);
  alert(cum);

  function monnom(nom){
    alert("bonjour "+nom);
  }
  monnom(prompt("YouYouYou"));
  var tb = [];
  for(var i = 0;i<10;i++)
  {
    tb.push(parseInt(prompt("val"+i)));
  }

  tb.sort(function(a,b){
    return a - b;
  } );
  console.log(tb);



  </script>
</form>

</body>
</html>


</body>
</html>
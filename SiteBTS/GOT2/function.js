
function connect(){
 $.ajax("connexion.php", 
{ type: "POST",
 success: function(result){
           if (result == "OK"){
                     alert("OK");
                     location = "mesPersonnages.php";
           }else{
                     alert("identifiant ou mot de passe incorrect");
           }
          }
});
}
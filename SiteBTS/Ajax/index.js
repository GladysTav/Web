
$( document ).ready(function() {
 
//click sur l'id btn
$('#btn').click(function(){ 
 
 $.ajax({       
      type: "GET",
//appel de index.php sur le serveur web
      url: "http://localhost/Ajax/index.php",
      success: function(data){ 
 
       	$("#result").html(data);
      }
    });
  });
});
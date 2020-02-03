$(document).ready(function() {

    function toggle(anId)
        {
          node = document.getElementById(anId);
          if (node.style.display=="none")
          {
            // Contenu caché, le montrer
            node.style.display = "inline";
            node.style.height = "0";            // Optionnel rétablir la hauteur
          }
          else
          {
            // Contenu visible, le cacher
            node.style.display = "none";
            node.style.height = "0";            // Optionnel libérer l'espace
          }
            /*var img = new Image();
            img.src = "ressources/img6b.png";
            document.getElementById('body').backgroundImage = img.src;*/
        }

        function CheckClick(id){
        if(document.getElementById(id).checked){
        document.getElementById(id+" span").style.color='#1732E1';
        document.getElementById(id+" span").style.fontWeight='bold';
        }
        else {
          document.getElementById(id+" span").style.color='#D6DEE6';
          document.getElementById(id+" span").style.fontWeight='normal';
        }
        }


 
 $('#btn-valide-bjr').click(function(){ 
 $.ajax({ 
       type: "GET",
       url: "../serveur/index.php/bonjour",
       success: function(resultat){
          window.alert(resultat);
       }
  });
 });




$('#btn-new-liste').click(function(){ 
    let idx=$('#idx').val();
    $('#table-perso').bootstrapTable({
     url: "../serveur/index.php/personnage/"+idx,
     columns: [{
     field: 'name',
     title: 'Nom'
     }, {
     field: 'id',
     title: 'id'
     }, {
     field: 'house',
     title: 'Maison'
     }, {
     field: 'culture',
     title: 'Culture'
     }, {
     field: 'actor',
     title: 'Acteur'
     }, {
     field: 'date_of_birth',
     title: 'Date de naissance'
     }, {
     field: 'date_of_death',
     title: 'Date de mort'
     }, {
     field: 'titles',
     title: 'Titres'
     }]
    });

});


$('#btn-valide').click(function(){ 
  let id=$('#id').val();
  let nom=$('#nom').val();
  $.ajax({ 
    type: "GET",
    contentType: 'application/json; charset=utf-8',
    url: "../serveur/index.php/user?id="+id+"&nom="+nom,
    success: function(data){
      alert(data);
    }
  });
});


$('#btn-valide-persall').click(function () {
   $('#table-persos').bootstrapTable({
     url: '../serveur/index.php/SeeAllPerso',
     columns: [{
     field: 'name',
     title: 'Nom'
     }, {
     field: 'id',
     title: 'id'
     }, {
     field: 'house',
     title: 'Maison'
     }, {
     field: 'culture',
     title: 'Culture'
     }, {
     field: 'actor',
     title: 'Acteur'
     }, {
     field: 'date_of_birth',
     title: 'Date de naissance'
     }, {
     field: 'date_of_death',
     title: 'Date de mort'
     }, {
     field: 'titles',
     title: 'Titres'
     }]
  });

});

$('#btn-valide-persall100').click(function () {
   $('#table-persos100').bootstrapTable({
     url: '../serveur/index.php/SeeAllPerso100',
     columns: [{
     field: 'name',
     title: 'Nom'
     },  {
     field: 'titles',
     title: 'Titres'
     }, {
     field: 'culture',
     title: 'Culture'
     }]
  });

});

$('#btn-affiche-utilisateurs').click(function () {
let letoken = sessionStorage.getItem('token');
   $('#users').bootstrapTable({
     url: '../serveur/index.php/Utilisateurs?token='+letoken,
     columns: [{
        field: 'id',
        title: 'id'}, {
     field: 'Pseudo',
     title: 'Pseudo'
     },{
        field: 'email',
        title: 'Email'
     }]
  });

});

$('#cacher-persall').click(function () {
    toggle("table-persos");
});
$('#cacher-persall100').click(function () {
    toggle("table-persos100");
});
$('#cacher-new-liste').click(function () {
    toggle("table-perso");
});
$('#cacher-get-villes').click(function () {
    toggle("table-villes");
});
$('#cacher-get-maisons').click(function () {
    toggle("table-maisons");
});


$('#btn-get-ville').click(function traitement() {
    $.ajax("https://api.got.show/api/cities/"+$('#_name').val(),
    {
        type: "GET",
        data: $('#_name').val(),
        success: function (resultat) {
                    
        $("#id").html(resultat.data["_id"]);
        $("#name").html(resultat.data["name"]);
        $("#type").html(resultat.data["type"]);
        $("#X").html(resultat.data["coordX"]);
        $("#Y").html(resultat.data["coordY"]);
    }
    });

});




$('#btn-get-perso').click(function traitement() {
    $.ajax("https://api.got.show/api/characters/"+$('#_name').val(),
    {
    type: "GET",
    data: $('#_name').val(),
    success: function (resultat) {
                
    $("#_id").html(resultat.data["_id"]);
    $("#male").html(resultat.data["male"]);
    $("#house").html(resultat.data["house"]);
    $("#slug").html(resultat.data["slug"]);
    $("#name").html(resultat.data["name"]);
    $("#__v").html(resultat.data["__v"]);
    $("#c").html(resultat.data["c"]);
    $("#pageRank").html(resultat.data["pageRank"]);
    $("#books").html(resultat.data["books"]);
    $("#updatedAt").html(resultat.data["updatedAt"]);
    $("#createdAt").html(resultat.data["createdAt"]);
    $("#titles").html(resultat.data["titles"]);
    }
    });
});


                        

$('#btn-get-villes').click(function () {
    $('#table-villes').bootstrapTable({
        url: 'https://api.got.show/api/cities',
        columns: [{
            field: 'name',
            title: 'Item Name'
        }, {
            field: 'type',
            title: 'type'
        }]
    });
});

$('#btn-get-maisons').click(function () {
    $('#table-maisons').bootstrapTable({
        url: 'https://api.got.show/api/houses',
        columns: [{
                    
            field: 'name',
            title: 'Nom'
        }]
    });
});




$('#btn-connect').click(function(){ 
    let pseudo=$('#pseudo').val();
    let mdp=$('#mdp').val();
    $.ajax({ 
        type: "GET",
        contentType: 'application/json; charset=utf-8',
        url: "../serveur/index.php/user?pseudo="+pseudo+"&mdp="+mdp,
        success: function(data){
        window.alert(data);
        }
    });
});


$('#btn-inscription').click(function(){ 
let pseudo=$('#pseudo').val();
let mdp=$('#mdp').val();
let conf=$('#conf').val();
let email=$('#email').val();
$.ajax({ 
      type: "POST",
      contentType: 'application/json; charset=utf-8',
      url: "../serveur/index.php/inscrire?pseudo="+pseudo+"&mdp="+mdp+"&conf="+conf+"&email="+email,
     success: function(data){
        window.alert(data);
      }
 });
});


 $('#btn-suppr').click(function(){ 
let id=$('#pseudoASuppr').val();
let token = sessionStorage.getItem('token');
 $.ajax({ 
       type: "POST",
       url: "../serveur/index.php/DELETE/user/"+id+"?token="+token,
     success: function(data){
        window.alert(data);
      }
  });
 });


 $('#btn-modif-mail').click(function(){ 
let id=$('#pseudo').val();
let mail=$('#mail').val();
let token = sessionStorage.getItem('token');
 $.ajax({ 
       type: "POST",
       url: "../serveur/index.php/PUT/user?pseudo="+id+"&mail="+mail+"&token="+token,
     success: function(data){
        window.alert(data);
      }
  });
 });





$( "#btn-token").click(function(event) {    

                pass = $("#password").val(),
                login = $("#login").val(),

               $.ajax({ 
                type: "GET",
                url: "../serveur/index.php/obtentionToken?pseudo="+login+"&pass="+pass,
                success: function(data){
                  sessionStorage.setItem('token', data); 
                  window.alert("C'est OK !");                              
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {  
                    window.alert('Erreur. Vérifiez les identifiants.'); 
                    $(".form-group-password").addClass("has-danger");
                    $("#password").addClass("form-control-danger");
                }             
                });
});




$('#dl').click(function () {
    if ($('#pass').val()=="YouShallNotPass"){
        //window.alert('<a href="GOT4.zip" OnClick="load()">Télécharger<a/>');
        window.open('GOT4.zip',"_blank", null);
    }
    else{window.alert('Mauvais mot de passe');}
});




var backgroundColor=[
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ];
var borderColor=[
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ];

/*
var ctx4 = document.getElementById("nbvillepartype").getContext('2d');
var myDoughnutChart = new Chart(ctx4, {
    type: 'doughnut',
    data : {
    datasets: [{
        data: [10, 20, 30],// les chiffres
        backgroundColor: backgroundColor,
            borderColor: borderColor,
            borderWidth: 1
    }],   
    labels: [//les villes
        'Red',
        'Yellow',
        'Blue'
    ],    
}    
});*/


    $.ajax({
     type: "GET",
         url: "../serveur/index.php/getpersoparmaison",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx = document.getElementById("nbpersoparmaison").getContext('2d');
          var myChart = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: name, //tableau des noms
                  datasets: [{
                      label: 'nombre de personnages',
                      data: num, // tableau des data
                      backgroundColor: backgroundColor,
                      borderColor: borderColor,
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true
                          }
                      }]
                  }
              }
           });
         }
     });


$.ajax({
     type: "GET",
         url: "../serveur/index.php/getpersoparmaison",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx4 = document.getElementById("nbpersoparmaisonb").getContext('2d');
            var myDoughnutChart = new Chart(ctx4, {
                type: 'doughnut',
                data : {
                datasets: [{
                    data: num,
                    backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 1
                }],
                labels: name,    
            }    
            });
         }
     });


    $.ajax({
     type: "GET",
         url: "../serveur/index.php/getvillepartype",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx = document.getElementById("nbvillepartype").getContext('2d');
          var myChart = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: name, //tableau des noms
                  datasets: [{
                      label: 'nombre de villes',
                      data: num, // tableau des data
                      backgroundColor: backgroundColor,
                      borderColor: borderColor,
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true
                          }
                      }]
                  }
              }
           });
         }
     });


$.ajax({
     type: "GET",
         url: "../serveur/index.php/getvillepartype",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx4 = document.getElementById("nbvillepartypeb").getContext('2d');
            var myDoughnutChart = new Chart(ctx4, {
                type: 'doughnut',
                data : {
                datasets: [{
                    data: num,
                    backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 1
                }],
                labels: name,    
            }    
            });
         }
     });


    $.ajax({
     type: "GET",
         url: "../serveur/index.php/getpersopar500",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx = document.getElementById("nbpersopar500").getContext('2d');
          var myChart = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: name, //tableau des noms
                  datasets: [{
                      label: 'nombre de personnages',
                      data: num, // tableau des data
                      backgroundColor: backgroundColor,
                      borderColor: borderColor,
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true
                          }
                      }]
                  }
              }
           });
         }
     });


$.ajax({
     type: "GET",
         url: "../serveur/index.php/getpersopar500",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx4 = document.getElementById("nbpersopar500b").getContext('2d');
            var myDoughnutChart = new Chart(ctx4, {
                type: 'doughnut',
                data : {
                datasets: [{
                    data: num,
                    backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        borderWidth: 1
                }],
                labels: name,    
            }    
            });
         }
     });

});




$('#btn-culture').click(function () {
   $('#table-culture').bootstrapTable({
     url: '../serveur/index.php/cultures',
     columns: [{
     field: 'name',
     title: 'Culture'
     }]
  });

});

$('#btn-creer-maison').click(function () {
let letoken = sessionStorage.getItem('token');
   let name=$('#name').val();
	let region=$('#region').val();
	let date=$('#date').val();
$.ajax({ 
      type: "POST",
      contentType: 'application/json; charset=utf-8',
      url: "../serveur/index.php/maison?name="+name+"&region="+region+"&date="+date+'&token='+letoken,
     success: function(data){
        window.alert(data);
      }
 });

});


var backgroundColor=[
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ];
var borderColor=[
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
               'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ];

$.ajax({
     type: "GET",
         url: "../serveur/index.php/maison/cultures",
         success: function(resultat){
          var objet = JSON.parse(resultat);
          var name = [];
          var num = [];
          
           objet.forEach(function(i){
            name.push('"'+i.name+'"');
            num.push(i.num);
           });
  
          var ctx = document.getElementById("nbcultureparmaison").getContext('2d');
          var myChart = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: name, //tableau des noms
                  datasets: [{
                      label: 'nombre de cultures',
                      data: num, // tableau des data
                      backgroundColor: backgroundColor,
                      borderColor: borderColor,
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true
                          }
                      }]
                  }
              }
           });
         }
     });



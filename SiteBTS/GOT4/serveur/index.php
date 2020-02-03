<?php

require 'vendor/autoload.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
$app = new \Slim\App;
//error_reporting(E_ALL);
//ini_set('display_errors', 1);


/* REQUESTS BY CLIENT*/

$app->get('/bonjour', function(Request $request, Response $response){  
  return "Bonjour !! ^_^";
});


$app->post('/DELETE/user/{id}', function(Request $request, Response $response){  
  $tb = $request->getQueryParams();
  $token = $tb['token'];
  if(validJWT($token)){
    $id=$request->getAttribute('id');
    return DelUser($id);
  }else{
    //non autorisé
    return $response->withStatus(401);
  }  
});

$app->get('/SeeAllPerso', function(Request $request, Response $response){  
return getPersonnages();
});
$app->get('/SeeAllPerso100', function(Request $request, Response $response){  
return getPersonnages100();
});
$app->get('/getpersopar500', function(Request $request, Response $response){  
return getpersopar500();
});
$app->get('/getpersoparmaison', function(Request $request, Response $response){  
return getpersoparmaison();
});
$app->get('/getvillepartype', function(Request $request, Response $response){  
return getvillepartype();
});


$app->get('/Utilisateurs', function(Request $request, Response $response){ 
  $tb = $request->getQueryParams();
  $token=$tb['token'];
  if(validJWT($token)){
    return Utilisateurs();
  }else{
    //non autorisé
    return $response->withStatus(401);
  }   
return Utilisateurs();
});

$app->get('/personnage/{id}', function(Request $request, Response $response){
$id = $request->getAttribute('id');
return getPersonnage($id);
});


/*$app->post('/user', function(Request $request, Response $response){
$tb = $request->getQueryParams();
$id = $tb["id"];
$nom = $tb["nom"];
//fonction d'insertion
return checkUser($id, $nom);
});*/

$app->get("/user", function(Request $request, Response $response){
  $tb = $request->getQueryParams();
    $pseudo = $tb["pseudo"];
    $mdp = $tb["mdp"];
   $allowed= SeConnecter($pseudo, $mdp);

    $obj = json_decode($allowed, true);

  return $obj[0]['email'];
  });

$app->get("/dl", function(Request $request, Response $response){
  $tb=$request->getQueryParams();
  return dl($tb['pass']);
});

$app->post("/inscrire", function(Request $request, Response $response){
  $tb = $request->getQueryParams();
    $pseudo = $tb["pseudo"];
    $mdp = $tb["mdp"];
    $conf = $tb["conf"];
    $email = $tb["email"];

    if($pseudo == "" || $email==""){return "Merci de renseigner tous les champs";}
    elseif(strlen($pseudo)<5){return "Le pseudo doit faire au moins 5 caractères";}
    elseif(strlen($mdp)<6){ return "Le mot de passe doit faire au moins 6 caractères.";}

    elseif($mdp==$conf){
      return AddUser($pseudo, $mdp, $email);}
    else{return ("La confirmation est différente du mot de passe.");}
});

$app->post("/PUT/user", function(Request $request, Response $response){
  $tb = $request->getQueryParams();
    $pseudo = $tb["pseudo"];
    $mail = $tb["mail"];

  $token = $tb['token'];

  if(validJWT($token)){

    if($mail==""){return "Merci de renseigner un mail";}

    else{
      return ChangeMail($pseudo, $mail);
    }

  }else{
    //non autorisé
    return $response->withStatus(401);
  }  
});



//TOKEN

$app->get('/obtentionToken', function(Request $request, Response $response){  
  //vérification de l'utilisateur
  $tb = $request->getQueryParams();
  $login = $tb['pseudo'];
  $pass = $tb['pass'];
  $allowed= ckeckUser($login,$pass);

    $obj = json_decode($allowed, true);

  if($obj[0]["boul"]){
    $token=getTokenJWT();
    return $response->withJson($token,200);
  }else{
    return $response->withStatus(401);
  }
});

/*
$app->post('/monURI', function(Request $request, Response $response){
  $token = $request->getQueryParam('token');
  if(validJWT($token)){
    //J'execute la fonction
  }else{
    //non autorisé
    return $response->withStatus(401);
  }  
});
*/

 function getTokenJWT() {
   // Make an array for the JWT Payload
  $payload = array(
    //30 min
    "exp" => time() + (60 * 30)
  );
   // encode the payload using our secretkey and return the token
  return JWT::encode($payload, "secret");
}

  function validJWT($token) {
    $res = false;
    try {
        $decoded = JWT::decode($token, "secret", array('HS256'));       
    } catch (Exception $e) {
      return $res;
    }
    $res = true;
    return $res;  
  }


  // FIN TOKEN

/* FUNCTIONS */

function ckeckUser($login,$pass){
  $sql = "SELECT COUNT(*) as boul FROM user WHERE Pseudo = '".$login."' and mdp = '".$pass."'";
    try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_CLASS);
        return json_encode($result, JSON_PRETTY_PRINT);
    }catch(PDOException $e){
        return '{"error":'.$e->getMessage().'}';
    }
}

function connexion(){

if ($_SERVER['SERVER_NAME'] == 'localhost') {

$user = "root";
$mdp = "";
$host = "localhost";
$dbname = "GOT4";

}
else{
    $host = 'db777713586.hosting-data.io';
    $dbname = 'db777713586';
    $user = 'dbo777713586';
    $mdp = 'motdepasse';
}

      try {
        return $dbh = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $user, $mdp,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_AUTOCOMMIT=>FALSE));
      }
      catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
        return false;
      }

}


function executeca($sql){
  try{
    $dbh=connexion();
    $statement = $dbh->prepare($sql);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_CLASS); 
    return json_encode($result, JSON_PRETTY_PRINT);
  } 
  catch(PDOException $e){
    return '{"error":'.$e->getMessage().'}';
  }
}



function getPersonnages()
{
  $sql = "SELECT characters.id, characters.name as 'name', houses.name as 'house', cultures.name as 'culture', characters.actor, characters.date_of_death, "
      ."characters.date_of_birth, characters.titles FROM characters, cultures, houses "
      ."Where characters.culture = cultures.id and characters.house = houses.id group by characters.id, cultures.id, houses.id";
  return executeca($sql);
}


function Utilisateurs()
{
  $sql = "SELECT * from user";
  return executeca($sql);
}


function getPersonnages100()
{
  $sql = "select characters.name as 'name', cultures.name as 'culture', characters.titles as 'titles' from characters Left outer join cultures on characters.culture = cultures.id group by characters.id, cultures.id Limit 100";
  return executeca($sql);
}



function getPersonnage($id)
{
  $sql = "SELECT characters.id, characters.name as 'name', houses.name as 'house', cultures.name as 'culture', characters.actor, characters.date_of_death, "
      ."characters.date_of_birth, characters.titles FROM characters, cultures, houses "
      ."Where characters.name like '".$id."' and characters.culture = cultures.id and characters.house = houses.id group by characters.id, cultures.id, houses.id";
  return executeca($sql);
}

function SeConnecter($pseudo, $mdp){
  $sql = "SELECT email FROM user WHERE Pseudo = '".$pseudo."' and mdp = '".$mdp."'";
 try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_CLASS);
        return json_encode($result, JSON_PRETTY_PRINT);
    }catch(PDOException $e){
        return '{"error":'.$e->getMessage().'}';
    }
}


function AddUser($id, $mdp, $email){
      $sql = "Insert INTO user(Pseudo, mdp, email) values ('".$id."', '".$mdp."', '".$email."')";
    try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        return "Insertion effectuée !";
    }catch(PDOException $e){
        return '{"Erreur":'.$e->getMessage().'}';
    }
}

function DelUser($id){
    $sql = "delete from user where pseudo like '".$id."'";
    try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        return "Suppression effectuée !";
    }catch(PDOException $e){
        return '{"Error":'.$e->getMessage().'}';
    }
}


function ChangeMail($pseudo, $mail){
    $sql = "update user set email ='".$mail."' where pseudo='".$pseudo."'";
    try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        return "Modification effectuée !";
    }catch(PDOException $e){
        return '{"Error":'.$e->getMessage().'}';
    }
  }


function getpersopar500(){
  $sql = "CALL `perso500`();";
  return executeca($sql);
}
function getpersoparmaison(){
  $sql = "SELECT count(characters.name) as num, houses.name as name from characters, houses where characters.house = houses.id group by houses.name having count(characters.name)>9 order by count(characters.name) desc";
  return executeca($sql);
}
function getvillepartype(){
  $sql = "SELECT count(cities.name) as num, cities_type.name as name from cities, cities_type where cities.type = cities_type.id group by cities.type order by count(cities.name) desc";
  return executeca($sql);
}
/*
function dl($pass){
    if($pass=='YouShallNotPass'){
      try{

        $uploaddir = '/var/www/uploads/';
        $uploadfile = $uploaddir . basename($_FILES['userfile']['GOT4.zip']);

        echo '<pre>';
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            echo "Le fichier est valide, et a été téléchargé
                   avec succès. Voici plus d'informations :\n";
        } else {
            echo "Attaque potentielle par téléchargement de fichiers.
                  Voici plus d'informations :\n";
        }

        echo 'Voici quelques informations de débogage :';
        print_r($_FILES);

        echo '</pre>';

      $full_path = 'GOT4.zip'; // chemin système (local) vers le fichier
      $file_name = basename($full_path);
       
      ini_set('zlib.output_compression', 0);
      $date = gmdate(DATE_RFC1123);
       
      header('Pragma: public');
      header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
       
      header('Content-Tranfer-Encoding: none');
      header('Content-Length: '.filesize($full_path));
      header('Content-MD5: '.base64_encode(md5_file($full_path)));
      header('Content-Type: application/octetstream; name="'.$file_name.'"');
      header('Content-Disposition: attachment; filename="'.$file_name.'"');
       
      header('Date: '.$date);
      header('Expires: '.gmdate(DATE_RFC1123, time()+1));
      header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime($full_path)));
       
      readfile($full_path);
      return ("Ok !");}
      catch(Exception $e){ return $e;}
    }
    else{return('Mauvais mot de passe.');}
  }
*/





  $app->get('/cultures', function(Request $request, Response $response){
return getCultures();
});

function getCultures(){
  $sql = "SELECT name from Cultures";
  return executeca($sql);
}





$app->post("/maison", function(Request $request, Response $response){
  $tb = $request->getQueryParams();
    $region = $tb["region"];
    $date = $tb["date"];
    $name = $tb["name"];
    $token = $tb['token'];


  if(validJWT($token)){
    if($region == "" || $date=="" || $name==""){return "Merci de renseigner tous les champs";}
    else {return AddHouse($name, $date, $region);}
  }else{
    //non autorisé
    return $response->withStatus(401);
  }  

});

function AddHouse($name, $date, $region){
  $idregion = getidregion($region);
      $sql = "Insert INTO houses(name, region, founded, id) values ('".$name."', '56fadc530b11d60868c274a0', '".$date."', 111111111111111111111111)";
    try{
        $dbh = connexion();
        $statement = $dbh->prepare($sql);
        $statement->execute();
        return $sql;
        return "Insertion effectuée !";
    }catch(PDOException $e){
        return '{"Erreur":'.$e->getMessage().'}'.$idregion;
    }
}

function getidregion($region){
  $sql=executeca("SELECT id FROM regions where name = '".$region."'");
  $obj = json_decode($sql);
  return $obj->{'id'}; // 12345
}





  $app->get('/maison/cultures', function(Request $request, Response $response){
return getCulturesParMaison();
});

function getCulturesParMaison(){
  return executeca("SELECT count(*) as num, houses.name from houses, characters, cultures where characters.culture=cultures.id and characters.house = houses.id group by characters.house order by num desc");
}


$app->run();
<?php
header("Content-Type: application/json");
require('db_connection.php');

// the requre lines include the lib and api source files
//require("lib.php");
//require("commands.php");

// this line starts the server session - that means the server will "remember" the user
// between different API calls - ie. once the user is authorized, he will stay logged in for a while
session_start();

//json error
function errorJson($msg){
  print json_encode(array('error'=>$msg));
  exit();
}


print_r($_POST);

//No command
if(!$_POST['command']){
  print json_encode(array('error'=>'no command bozo'));
}

//INSERT
$email = "test";
$user = mysql_query("INSERT INTO userinfo (emailaddress) VALUES ('$email')");

//The  app sends over what "command" of the API it wants executed
switch ($_POST['command']) {

    case "newuser`":
      login($_POST['username'], $_POST['password']);
          break;

    case "logout":
      logout();
      break;

}

exit();
?>
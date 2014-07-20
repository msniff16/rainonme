<?php
header("Content-Type: application/json");
require('db_connection.php');

// the requre lines include the lib and api source files
//require("lib.php");
require("commands.php");

// this line starts the server session - that means the server will "remember" the user
// between different API calls - ie. once the user is authorized, he will stay logged in for a while
session_start();

//json error
function errorJson($msg){
  print json_encode(array('error'=>$msg));
  exit();
}

//Parse Posted JSON
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

//No command
if(!$_POST['command']){
  print json_encode(array('error'=>'no command bozo'));
}

//The  app sends over what "command" of the API it wants executed
switch ($_POST['command']) {

    case "register":
      register($_POST['phonenumber'], $_POST['password'], $_POST['latitude'], $_POST['longitude']);
      break;

    case "login":
      login($_POST['phonenumber'], $_POST['password']);
      break;

    case "getUserInfo":
      getUserInfo($_POST['phonenumber']);
      break;

    case "dailyReport":
      dailyReport($_POST['phonenumber'], $_POST['value']);
      break;

    case "weeklyReport":
      weeklyReport($_POST['phonenumber'], $_POST['value']);
      break;

    case "minuteAlerts":
      minuteAlerts($_POST['phonenumber'], $_POST['value']);
      break;

    case "hourAlerts":
      hourAlerts($_POST['phonenumber'], $_POST['value']);
      break;

    case "findRain":
      findRain($_POST['latitude'], $_POST['longitude']);
      break;

}

exit();
?>
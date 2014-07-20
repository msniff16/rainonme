<?php

/*----This script set up with crontab -e to run every 30 minutes ----*/

require('db_connection.php');
require('api.php');

//get all users from userinfo
$getUsers = mysql_query("SELECT * FROM userinfo ORDER BY id DESC");
$allUsers = mysql_num_rows($getUsers);

for($iii=0; $iii<$allUsers; $iii++) {

   //get this user's data
    $emailaddress = mysql_result($getUsers,$iii,'emailaddress');
    $location = mysql_result($getUsers,$iii,'location');
    $phonenumber = mysql_result($getUsers,$iii,'phonenumber');
    $latitude = mysql_result($getUsers,$iii,'latitude');
    $longitude = mysql_result($getUsers,$iii,'longitude');
    $minutealerts = mysql_result($getUsers,$iii,'minuteAlerts');
    $houralerts = mysql_result($getUsers,$iii,'hourAlerts');
    $dailysummary = mysql_result($getUsers,$iii,'dailysummary');
    $hourofreport = mysql_result($getUsers,$iii,'hourofreport');
    $weeklysummary = mysql_result($getUsers,$iii,'weeklysummary');
    $maxalerts = mysql_result($getUsers,$iii,'maxalerts');

    //request report for this user
    requestReport($phonenumber, $latitude, $longitude, $minutelyreport, $hourlyreport, $dailysummary, $hourofreport, $weeklysummary, $maxalerts);

}
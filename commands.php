<?php
header("Content-Type: application/json");
require('lib/forecast.io.php');
require('db_connection.php');

/*

//User sign up function
function register($emailaddress, $location, $phonenumber, $latitude, $longitude, $dailysummary, $hourofreport, $weeklysummary, $maxalerts) {

    $createUser = mysql_query("INSERT INTO userinfo (emailaddress, location, phonenumber, latitude, longitude, dailysummary, hourofreport, weeklysummary, maxalerts)
      VALUES ('$emailaddress', '$location', '$phonenumber', '$latitude', '$longitude', '$dailysummary', '$hourofreport', '$weeklysummary', '$maxalerts')");

}

//User welcome text message
function welcomeMessage() {

  $message = "You've signed up for your personalized rain tracker with rain on me! Add this number to your contacts as 'rainonme'."

}
*/

//find how long rain is from your current lat/lon
function findRain($latitude, $longitude) {
  $time = findRainTime($latitude, $longitude);
  print json_encode(array('time'=>$time));
}

//get number minutes before it rains
function findRainTime($latitude, $longitude) {

  //API Key
  $api_key = 'eccf1a4ed86ba49e6fdeeea0885ce363';

  //Set timezone based on lat/long
  $currentTimeZone = getClosestTimezone($latitude, $longitude, 'US');
  date_default_timezone_set($currentTimeZone);

  //New forecast class
  $forecast = new ForecastIO($api_key, $currentTimeZone);

  //Times
  $currentday = substr(date('d:H:i:s',time()),0,2);
  $currentTime = date('H:i:s',time());
  $currentHour = substr($currentTime,0,2);
  $currentMinute = substr($currentTime,3,2);
  $dayofweek = jddayofweek ( cal_to_jd(CAL_GREGORIAN, date("m"),date("d"), date("Y")) , 1 );

  //New forecast class

  $conditions_today = $forecast->getForecastTodayMinute($latitude, $longitude);

    foreach($conditions_today as $cond) {

      //Assume it is going to rain (>=70%)
      if($cond->getPrecipProbability() >= .70) {

          //Minutes until rainfall
          $rainMinute = substr($cond->getTime('H:i:s'),3,2);
          $timeDiff = $currentMinute - $rainMinute;

          //It is raining, send a message
          if($timeDiff == 0 && ($cond->getPrecipProbability() == 1)) {

            return "It is currently raining here";

            //end loop
            break;

          }

          //It will rain in XX minutes
          else {

            return $timeDiff ." minutes until rainfall";

            //end loop
            break;

          }

      }

      return "No rain near your location";

  }

}

//Find nearest timezone from latitude and longitude (inaccurate internationally)
function getClosestTimezone($lat, $lng) {

  $diffs = array();
  foreach(DateTimeZone::listIdentifiers() as $timezoneID) {
    $timezone = new DateTimeZone($timezoneID);
    $location = $timezone->getLocation();
    $tLat = $location['latitude'];
    $tLng = $location['longitude'];
    $diffLat = abs($lat - $tLat);
    $diffLng = abs($lng - $tLng);
    $diff = $diffLat + $diffLng;
    $diffs[$timezoneID] = $diff;

  }

  //asort($diffs);
  $timezone = array_keys($diffs, min($diffs));
  return $timezone[0];

}

//register the user, check to see not already registered
function register($phonenumber, $password, $latitude, $longitude) {
  $ip =  $_SERVER['REMOTE_ADDR'];
  $time = time();
  $location = '';
  $login = mysql_query("SELECT * FROM userinfo WHERE phonenumber = '$phonenumber' LIMIT 1");
  if(mysql_num_rows($login) > 0) {
    print json_encode(array('error'=>'A user with this phone number already exists'));
  }
  else {
    $insertUser = mysql_query("INSERT INTO userinfo (phonenumber, password, location, latitude, longitude, minuteAlerts, hourAlerts, dailysummary, hourofreport, weeklysummary, maxalerts, ip, time) VALUES ('$phonenumber','$password','$location','$latitude','$longitude','1','1','1','8','1','10','$ip','$time')");
    $getUserInfo = mysql_query("SELECT * FROM userinfo WHERE phonenumber = '$phonenumber' LIMIT 1");
    $userData = [
        "name" => mysql_result($getUserInfo,0,'name'),
        "phonenumber" => mysql_result($getUserInfo,0,'phonenumber'),
        "location" => mysql_result($getUserInfo,0,'location'),
        "latitude" => mysql_result($getUserInfo,0,'latitude'),
        "longitude" => mysql_result($getUserInfo,0,'longitude'),
        "minuteAlerts" => mysql_result($getUserInfo,0,'minuteAlerts'),
        "hourAlerts" => mysql_result($getUserInfo,0,'hourAlerts'),
        "dailysummary" => mysql_result($getUserInfo,0,'dailysummary'),
        "weeklysummary" => mysql_result($getUserInfo,0,'weeklysummary'),
        "hourofreport" => mysql_result($getUserInfo,0,'hourofreport'),
        "maxalerts" => mysql_result($getUserInfo,0,'maxalerts'),
      ];

      print json_encode($userData);
  }
}

//login -> if successful, return array of all userinfo
function login($phonenumber, $password) {
  $login = mysql_query("SELECT * FROM userinfo WHERE phonenumber = '$phonenumber' AND password = '$password' LIMIT 1");
  if(mysql_num_rows($login) < 1) {
    print json_encode(array('error'=>'Invalid phone number or password'));
  }
  else {
      $getUserInfo = mysql_query("SELECT * FROM userinfo WHERE phonenumber = '$phonenumber' LIMIT 1");
      $userData = [
        "name" => mysql_result($getUserInfo,0,'name'),
        "phonenumber" => mysql_result($getUserInfo,0,'phonenumber'),
        "location" => mysql_result($getUserInfo,0,'location'),
        "latitude" => mysql_result($getUserInfo,0,'latitude'),
        "longitude" => mysql_result($getUserInfo,0,'longitude'),
        "minuteAlerts" => mysql_result($getUserInfo,0,'minuteAlerts'),
        "hourAlerts" => mysql_result($getUserInfo,0,'hourAlerts'),
        "dailysummary" => mysql_result($getUserInfo,0,'dailysummary'),
        "weeklysummary" => mysql_result($getUserInfo,0,'weeklysummary'),
        "hourofreport" => mysql_result($getUserInfo,0,'hourofreport'),
        "maxalerts" => mysql_result($getUserInfo,0,'maxalerts'),
      ];

      print json_encode($userData);
  }

}


//get information for a user
function getUserInfo($phonenumber) {
  $getUserInfo = mysql_query("SELECT * FROM userinfo WHERE phonenumber = '$phonenumber' LIMIT 1");
  $userData = [
    "name" => mysql_result($getUserInfo,0,'name'),
    "phonenumber" => mysql_result($getUserInfo,0,'phonenumber'),
    "location" => mysql_result($getUserInfo,0,'location'),
    "latitude" => mysql_result($getUserInfo,0,'latitude'),
    "longitude" => mysql_result($getUserInfo,0,'longitude'),
    "minuteAlerts" => mysql_result($getUserInfo,0,'minuteAlerts'),
    "hourAlerts" => mysql_result($getUserInfo,0,'hourAlerts'),
    "dailysummary" => mysql_result($getUserInfo,0,'dailysummary'),
    "weeklysummary" => mysql_result($getUserInfo,0,'weeklysummary'),
    "hourofreport" => mysql_result($getUserInfo,0,'hourofreport'),
    "maxalerts" => mysql_result($getUserInfo,0,'maxalerts'),
  ];

  print json_encode($userData);

}

//update daily report
function dailyReport($phonenumber, $value) {
  $updateDailyReport = mysql_query("UPDATE userinfo SET dailysummary = '$value' WHERE phonenumber = '$phonenumber' LIMIT 1");
}

//update weekly report
function weeklyReport($phonenumber, $value) {
  $updateWeeklyReport = mysql_query("UPDATE userinfo SET weeklysummary = '$value' WHERE phonenumber = '$phonenumber' LIMIT 1");
}

//update minute reports
function minuteAlerts($phonenumber, $value) {
  $updateMinuteAlerts = mysql_query("UPDATE userinfo SET minuteAlerts = '$value' WHERE phonenumber = '$phonenumber' LIMIT 1");
}

//update hour reports
function hourAlerts($phonenumber, $value) {
  $updateHourAlerts = mysql_query("UPDATE userinfo SET hourAlerts = '$value' WHERE phonenumber = '$phonenumber' LIMIT 1");
}



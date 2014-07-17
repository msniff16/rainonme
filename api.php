<?php

include('lib/forecast.io.php');
include('twilio/sms-request.php');
require('db_connection.php');

//TO DO: get their time zone to send messages on their time!!!

//Request full weather report based on user data
function requestReport($emailaddress, $location, $phonenumber, $latitude, $longitude, $dailysummary, $hourofreport, $weeklysummary, $maxalerts) {

  //API Key
  $api_key = 'eccf1a4ed86ba49e6fdeeea0885ce363';

  //New forecast class
  $forecast = new ForecastIO($api_key);

  //send to this number
  $to = $phonenumber;

  //Set timezone based on lat/long
  $currentTimeZone = getClosestTimezone($latitude, $longitude, 'US');
  date_default_timezone_set($currentTimeZone);

  //Times
  $currentday = substr(date('d:H:i:s',time()),0,2);
  $currentTime = date('H:i:s',time());
  $currentHour = substr($currentTime,0,2);
  $currentMinute = substr($currentTime,3,2);
  $dayofweek = jddayofweek ( cal_to_jd(CAL_GREGORIAN, date("m"),date("d"), date("Y")) , 1 );

  /* GET MINUTELY CHANCES OF RAIN */
  $conditions_today = $forecast->getForecastTodayMinute($latitude, $longitude);

  foreach($conditions_today as $cond) {

    // 0.002 in./hr. corresponds to very light precipitation,
    // 0.017 in./hr. corresponds to light precipitation,
    // 0.1 in./hr. corresponds to moderate precipitation,
    // and 0.4 in./hr. corresponds to heavy precipitation.
    $intensity = $cond->getPrecipIntensity();
    if($intensity < 0.002) {
      $rate = "drizzling";
    }
    if($intensity >= 0.002) {
      $rate = "raining very lightly";
    }
    if($intensity >= 0.017) {
      $rate = "light raining";
    }
    if($intensity >= 0.1) {
      $rate = "moderately raining";
    }
    if($intensity >= 0.4) {
      $rate = "heavy raining";
    }

    //Assume it is going to rain (>=70%)
    if($cond->getPrecipProbability() >= .70) {

        //Minutes until rainfall
        $rainMinute = substr($cond->getTime('H:i:s'),3,2);
        $timeDiff = $currentMinute - $rainMinute;

        //It is raining, send a message
        if($timeDiff == 0 && ($cond->getPrecipProbability() == 1)) {

          //log report that it is raining
          $message = "It is currently " . $rate;
          logMessage($emailaddress, $message, "raining", time());

          //don't exceed max alerts
          getRainfallMessagesSentToday($emailaddress, $maxalerts);

          //last raining report within 2 hours
          $lastReport = getLastRainingReport($emailaddress);

          //hasn't rained in 2 hours
          if($lastReport == false) {

            //send message
            sendMessage($to, $message);

          }

          //end loop
          break;

        }

        //It will rain in XX minutes
        else {

          //log impending rainfall report
          $message = "It will be " . $rate . " in " . $timeDiff . " minutes";
          logMessage($emailaddress, $message, "impendingrainfall", time());

          //don't exceed max alerts
          getRainfallMessagesSentToday($emailaddress, $maxalerts);

          //last impendingrainfallreport within 2 hours
          $lastReport = getLastImpendingRainfallReport($emailaddress);

          //hasn't rained in 2 hours
          if($lastReport == false) {

            //send message
            sendMessage($to, $message);

          }

          //end loop
          break;

        }

    }

  }

  /* GET HOURLY CHANCES */

  $conditions_today = $forecast->getForecastTodayHourly($latitude, $longitude);

  foreach($conditions_today as $cond) {

    //Good chance of Rain (>=60%)
    if($cond->getPrecipProbability() >= .60) {

        //minutes until raining
        $rainHour = substr($cond->getTime('H:i:s'),0,2);
        $timeDiff = $currentHour - $rainHour;

        // 0.002 in./hr. corresponds to very light precipitation,
        // 0.017 in./hr. corresponds to light precipitation,
        // 0.1 in./hr. corresponds to moderate precipitation,
        // and 0.4 in./hr. corresponds to heavy precipitation.

        $intensity = $cond->getPrecipIntensity();

        if($intensity < 0.002) {
          $rate = "drizzling";
        }
        if($intensity >= 0.002) {
          $rate = "raining very lightly";
        }
        if($intensity >= 0.017) {
          $rate = "light raining";
        }
        if($intensity >= 0.1) {
          $rate = "moderately raining";
        }
        if($intensity >= 0.4) {
          $rate = "heavy raining";
        }

        //time difference > 1 hour (would be covered by minutely report already)
        if($timeDiff > 1) {

          //log impending rainfall report
          $message = $cond->getPrecipProbability() * 100 . "% chance of rain in " . $timeDiff . " hours";
          logMessage($emailaddress, $message, "rainhoursaway", time());

          //don't exceed max alerts
          getRainfallMessagesSentToday($emailaddress, $maxalerts);

          //last impendingrainfallreport within 2 hours
          $lastReport = getLastRainHoursAwayReport($emailaddress);

          //hasn't rained in 2 hours
          if($lastReport == false) {

            //send message
            sendMessage($to, $message);

          }

          //end loop
          break;

        }

    }

  }

  /*  Daily and Weekly Weather Report Summaries  */

  //Daily Reports (sent at hour specified by user)
  if($hourofreport == $currentHour) {

      //see if daily report already sent. If so, continue;
      $reportSent = getLastDailyReport($emailaddress, $currentday);

      //report not sent yet
      if($reportSent == false) {
        //daily report not sent -> get summary and send
        $summary = $forecast->getForecastSummaryToday($latitude, $longitude);
        $message = "Daily forecast: " . $summary;
        sendMessage($to, $message);

        //log daily report
        logMessage($emailaddress, $message, "dailyreport", time());

      }

  }

  //Weekly Reports (sent once a week on sunday mornings)
  if(($hourofreport == $currentHour) && $dayofweek == "Sunday") {

      //see if daily report already sent. If so, continue;
      $reportSent = getLastWeeklyReport($emailaddress, $currentday);

      //report not sent yet
      if($reportSent == false) {

        //daily report not sent -> get summary and send
        $summary = $forecast->getForecastSummaryWeekly($latitude, $longitude);
        $message = "Forecast this week: " . $summary;
        sendMessage($to, $message);

        //log weekly report
        logMessage($emailaddress, $message, "weeklyreport", time());

      }

  }

} //end of request report function (main function)


//Log messages
function logMessage($emailaddress, $message, $report, $time) {

  $logMessage = mysql_query("INSERT INTO messages (emailaddress, message, report, time) VALUES ('$emailaddress', '$message', '$report', '$time')");

}

//Number of rainfall messages sent today
function getRainfallMessagesSentToday($emailaddress, $maxalerts) {

    //last midnight
    $midnight = strtotime('today midnight');

    //get number of messages sent (impendingrainfall or rainfall types and time greater than last midnight)
    $getMessagesSent = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND (report = 'raining' || report = 'impendingrainfall') AND time > '$midnight' ORDER BY id DESC LIMIT 1");
    $numSent = mysql_num_rows($getMessagesSent);

    //check if > maxalerts
    if($numSent > $maxalerts) {
      continue;
    }

}

//Last "raining" report sent today
function getLastRainingReport($emailaddress) {

    //get last daily report sent
    $getLastReport = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND report = 'raining' ORDER BY id DESC LIMIT 1");
    $lastReport = mysql_result($getLastReport,0,'time');

    if(!$lastReport) {
      return false;
    }

    //check if has been raining within the past 2 hours (7600 seconds)
    elseif((time() - $lastReport) < 7200) {
      return true;
    }

    else {
      return false;
    }

}

//Last "impendingrainfall" report sent today
function getLastImpendingRainfallReport() {

    //get last daily report sent
    $getLastReport = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND report = 'impendingrainfall' ORDER BY id DESC LIMIT 1");
    $lastReport = mysql_result($getLastReport,0,'time');

    if(!$lastReport) {
      return false;
    }

    elseif((time() - $lastReport) < 7200) {
      return true;
    }

    else {
      return false;
    }

}

//Last "impendingrainfall" report sent today
function getLastRainHoursAwayReport($emailaddress) {

    //get last daily report sent
    $getLastReport = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND report = 'rainhoursaway' ORDER BY id DESC LIMIT 1");
    $lastReport = mysql_result($getLastReport,0,'time');

    if(!$lastReport) {
      return false;
    }

    //check if has been about to rain within the past 3 hours (11200 seconds)
    elseif((time() - $lastReport) < 11200) {
      return true;
    }

    else {
      return false;
    }

}

//See if report sent today
function getLastDailyReport($emailaddress, $currentday) {

    //get last daily report sent
    $getLastReport = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND report = 'dailyreport' ORDER BY id DESC LIMIT 1");
    $lastReport = mysql_result($getLastReport,0,'time');

    if(!$lastReport) {
      return false;
    }

    //check if days of month are equal
    elseif(substr(date('d:H:i:s',$lastReport),0,2) == $currentday) {
      return true;
    }

    else {
      return false;
    }

}

//See if report sent today
function getLastWeeklyReport($emailaddress, $currentday) {

    //get last daily report sent
    $getLastReport = mysql_query("SELECT time FROM messages WHERE emailaddress = '$emailaddress' AND report = 'weeklyreport' ORDER BY id DESC LIMIT 1");
    $lastReport = mysql_result($getLastReport,0,'time');

    if(!$lastReport) {
      return false;
    }

    //check if today < 7 (one week)
    elseif(($currentday - substr(date('d:H:i:s',$lastReport),0,2)) < 7) {
      return true;
    }

    else {
      return false;
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


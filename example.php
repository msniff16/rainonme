<?php
include('lib/forecast.io.php');
include('twilio/sms-request.php');

$api_key = 'eccf1a4ed86ba49e6fdeeea0885ce363';

$latitude = '33.31';
$longitude = '-79.32';

$forecast = new ForecastIO($api_key);
$to = "3309267945";

//Get Location Name
$latlonurl = "http://geocoder.ca/?latt=".$latitude."&longt=".$longitude."&reverse=1&allna=1&geoit=xml&corner=1&jsonp=1&callback=getPlace";
$content = file_get_contents($latlonurl);

?>

<script type="text/javascript">

  function getPlace() {
      document.write('<p>test</p>;');
  }

</script>

<?php

echo'<h1>Current Conditions:</h1>';

/*
 * GET CURRENT CONDITIONS
 */
$condition = $forecast->getCurrentConditions($latitude, $longitude);

echo $condition->getTemperature() .' Degrees<br />';

echo'<h1>Minutely Chances:</h1>';

/*
 * GET MINUTELY CHANCES
 */
$conditions_today = $forecast->getForecastTodayMinute($latitude, $longitude);

$currentTime = date('H:i:s',time());
$currentMinute = substr($currentTime,3,2);

foreach($conditions_today as $cond) {

  //Good chance of Rain (>=60%)
  if($cond->getPrecipProbability() >= .60) {

      //minutes until raining
      $rainMinute = substr($cond->getTime('H:i:s'),3,2);
      $timeDiff = $currentMinute - $rainMinute;

      // 0.002 in./hr. corresponds to very light precipitation,
      // 0.017 in./hr. corresponds to light precipitation,
      // 0.1 in./hr. corresponds to moderate precipitation,
      // and 0.4 in./hr. corresponds to heavy precipitation.
      $intensity = $cond->getPrecipIntensity();
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
      else {
        $rate = "drizzling";
      }

      //time difference
      if($timeDiff == 0) {
        $message = "Weatherjojo: It's currently " . $rate;
        echo $message;
        //sendMessage($to, $message);
        break;
      }

      else {
        $mesage = "Raining in " . $timeDiff . " minutes, rate: " . $rate;
        //sendMessage($to, $message);
        break;
      }

  }

}

echo'<h1>Hourly Chances:</h1>';

/*
 * GET HOURLY CHANCES
 */
$conditions_today = $forecast->getForecastTodayHourly($latitude, $longitude);

$currentTime = date('H:i:s',time());
$currentHour = substr($currentTime,0,2);

foreach($conditions_today as $cond) {

  //Good chance of Rain (>=60%)
  if($cond->getPrecipProbability() >= .60) {

      //minutes until raining
      $rainHour = substr($cond->getTime('H:i:s'),0,2);
      $timeDiff = $currentHour- $rainHour;

      // 0.002 in./hr. corresponds to very light precipitation,
      // 0.017 in./hr. corresponds to light precipitation,
      // 0.1 in./hr. corresponds to moderate precipitation,
      // and 0.4 in./hr. corresponds to heavy precipitation.
      $intensity = $cond->getPrecipIntensity();
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
      else {
        $rate = "drizzling";
      }

      //time difference
      if($timeDiff == 0) {
        $message = "Weatherjojo: It's currently " . $rate;
        echo $message;
        //sendMessage($to, $message);
        break;
      }

      else {
        $mesage = "Raining in " . $timeDiff . " hours, rate: " . $rate;
        //sendMessage($to, $message);
        break;
      }

  }

}

//Summaries

echo'<h1>Hourly Summary:</h1>';

echo $forecast->getForecastSummaryHourly($latitude, $longitude) . '<br />';

echo'<h1>Daily Summary:</h1>';

echo $forecast->getForecastSummaryToday($latitude, $longitude) . '<br />';

echo'<h1>Weekly Summary:</h1>';

echo $forecast->getForecastSummaryWeekly($latitude, $longitude) . '<br />';


<?php

 function sendMessage($to, $message) {

	// Include the Twilio PHP library
	require 'lib/Services/Twilio.php';

	// Twilio REST API version
	$version = "2010-04-01";

	// Set our Account SID and AuthToken
	$sid = 'ACf8a0a193958a8a46f2dbb700d73efa13';
	$token = '0e5008a58d3d5d02cb188c41706e95a0';

	// A phone number you have previously validated with Twilio
	$phonenumber = '12345423138';

	//to number
 	$tonum = "+1" . $to;

	// Instantiate a new Twilio Rest Client
	$client = new Services_Twilio($sid, $token, $version);

	try {
		// Initiate a new outbound call
		$call = $client->account->messages->sendMessage(
			$phonenumber, // The number of the phone initiating the call
			$tonum, // The number of the phone receiving call
			$message
		);
		echo 'Sent message: ';


	} catch (Exception $e) {
		echo 'Error: ' . $e->getMessage();
	}

} //end
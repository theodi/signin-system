<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('functions.php');

function sendPushNotification($deviceToken,$title,$body,$button) {
	include('push-config.php');
	$payload['aps']['alert'] = array(
			"title" => $title,
			"body" => $body,
			"action" => $button
			);
	$payload['aps']['url-args'] = array(
			"clicked"
			);
	$payload = json_encode($payload);
	$apnsHost = 'gateway.push.apple.com';
	$apnsPort = 2195;
	$streamContext = stream_context_create();
	stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
	$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
	$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
	fwrite($apns, $apnsMessage);
	fclose($apns);
}

function sendNotification($email,$title,$body,$button) {
	global $mysqli;
	$query = 'select device_id from device_id where email="'.$email.'";';
	$res = $mysqli->query($query);
	
	while ($row = $res->fetch_assoc()) {
		$device_id = $row["device_id"];
		sendPushNotification($device_id,$title,$body,$button);
	}	
}

//	Example Usage
//	$title = "Arrival Notification";
//	$body = "Someone is here to see you!";
//	$button = "View";
//	$deviceToken = "....";
//	sendPushNotification($deviceToken,$title,$body,$button);

if ($argv[1]) {
	$button = $argv[2];
	$title = $argv[3];
	$body = $argv[4];
	if (!$button || !$title || !$body) {
		echo "Error: No Content";
		exit(1);
	}
	sendNotification($argv[1],$title,$body,$button);

}

?>

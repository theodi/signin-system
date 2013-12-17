<?php

error_reporting(E_ALL ^ E_NOTICE);

require('functions.php');

$keycard_id = $_POST['keycard_id'];

if ($keycard_id == "" || strpos($statusCode," ") > 0 || strpos($statusCode,"'") > 0 || strpos($statusCode,'"') > 0 || strpos($statusCode,";")>0 || strpos($statusCode,",") > 0) {
	$statusCode = 400;
} else {
	$statusCode = register_keycard($keycard_id);
}

$status_codes = array (
		200 => 'OK',
		// Signed In
		201 => 'Created',
		// New Reocrd
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		// Signed Out
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		400 => 'Bad Request',
		404 => 'Not Found',
		// Something went wrong
		500 => 'Internal Server Error'
		);

if ($statusCode == null) {
	$statusCode = 500;
}

$status_string = $statusCode . ' ' . $status_codes[$statusCode];
header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);

?>

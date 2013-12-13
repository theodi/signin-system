<?php

	require('functions.php');

	$person = $_POST;	

	if ($person["id"] == "") {
		header("HTTP/1.0 400 Bad Request");
		echo "Bad Request";
		exit(1);
	}

	$res = sign_out($person);

	if (!$res) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "FAILED to sign in";
		exit(1);
	} else {
		header("HTTP/1.0 200 OK");
		echo "Signed Out";
		exit(0);
	}
	
?>

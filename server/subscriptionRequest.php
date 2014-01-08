<?php

	error_reporting(E_ALL ^ E_NOTICE);
	include('functions.php');

	$email = $_POST["email"];
	$type = $_POST["type"];
	$urlkey = $_POST["urlkey"];
	$option = $_POST["option"];
	if (!$email) { $email = $_GET["email"];	}
	if (!$type) { $type = $_GET["type"];	}
	if (!$urlkey) { $urlkey = $_GET["urlkey"];	}
	if (!$option) { $option = $_GET["option"];	}

	if (!$email || !$type || !$urlkey) {
		header("HTTP/1.0 400 Bad Request");
		echo "missing params";
		exit(1);
	}

	if ($type != "midata" && $type != "alerts") {
		header("HTTP/1.0 400 Bad Request");
		echo "wrong type";
		exit(1);
	}

	$query = 'update '.$type.' set authenticated=1 where email="'.$email.'" and urlkey="'.$urlkey.'";';
        $res = $mysqli->query($query);
        if (!$res) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "FAILED for $query";
		exit(1);
	}

	if ($type == "midata" || $type == "alerts") {
		if ($option == "unsubscribe") {
			$query = 'update '.$type.' set subscribed=0 where email="'.$email.'" and urlkey="'.$urlkey.'";';
		} else {
			$query = 'update '.$type.' set subscribed=1 where email="'.$email.'" and urlkey="'.$urlkey.'";';
		}
	        $res = $mysqli->query($query);
        	if (!$res) {
			header("HTTP/1.0 500 Internal Server Error");
			echo "FAILED for $query";
			exit(1);
		} else {
			header("HTTP/1.0 200 OK");
			exit(1);
		}
	}

?>

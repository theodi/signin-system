<?php

	include('functions.php');

	$email = $_POST["email"];
	if (!$email) {
		$email = $_GET["email"];
	}
	if (!$email) {
		header("HTTP/1.0 400 Bad Request");
		echo "FAILED";
		exit(1);
	}

	$query = 'select id, firstname, lastname, email, sector from people where email="'.$email.'";';

	$res = $mysqli->query($query);

	if ($res->num_rows > 0) {	
		$row = $res->fetch_assoc();
		$row["id"] = get_person_id($row);
		if (signed_in($row)) {
			$row["signedIn"] = "true";
		}
		header('Content-type: application/json');
		echo (json_encode($row));
	} else {
		header("HTTP/1.0 404 Not Found");
		echo "FAILED $query";
		exit(1);
	}

?>

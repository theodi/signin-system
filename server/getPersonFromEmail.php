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

	$query = 'select id, firstname, lastname, email, sector from people where people.email="'.$email.'";';

	$res = $mysqli->query($query);

	if ($res->num_rows > 0) {	
		$row = $res->fetch_assoc();
		$row["id"] = get_person_id($row);
		if (signed_in($row)) {
			$row["signedIn"] = "true";
		}
		$query = 'select role from people_roles inner join people on people_roles.person_id=people.id where people.email="'.$email.'" order by people_roles.valid_from desc limit 1;';
		$res2 = $mysqli->query($query);
		$row2 = $res2->fetch_assoc();
		if ($row2["role"] != "") {
			$row["role"] = $row2["role"];
		}
		header('Content-type: application/json');
		echo (json_encode($row));
	} else {
		header("HTTP/1.0 404 Not Found");
		echo "FAILED $query";
		exit(1);
	}

?>

<?php

	error_reporting(E_ALL ^ E_NOTICE);
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

	$array["midata"] = "0";
	$array["alerts"] = "0";
	$array["alerts_available"] = "0";

	$query = 'select * from midata where email="'.$email.'";';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {	
		$row = $res->fetch_assoc();
		$array["midata"] = "1";
		$array["midata-subscribed"] = $row["subscribed"];
	}
	$query = 'select * from alerts where email="'.$email.'";';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {	
		$row = $res->fetch_assoc();
		$array["alerts"] = "1";
		$array["alerts_subscribed"] = $row["subscribed"];
	}
	
	$query = 'select * from device_id where email="'.$email.'";';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {	
		$array["push_alerts"] = 1;
	}	

	$query = 'select role from people inner join people_roles on people_roles.person_id=people.id where email="'.$email.'" order by people_roles.valid_from desc limit 1;';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {	
		$row = $res->fetch_assoc();
		$role = $row["role"];
		if ($role=="staff" || $role=="startup") {
			$array["alerts_available"] = "1";
		}
	}	
		
	header('Content-type: application/json');
	echo (json_encode($array));

?>

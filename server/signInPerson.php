<?php

	error_reporting(E_ALL ^ E_NOTICE);

	require('functions.php');

	$person = $_POST;	

	if ($person["email"] == "") {
		header("HTTP/1.0 400 Bad Request");
		echo "Bad Request";
		exit(1);
	}

	$columns = getColumnsInTable("people");
	$query_part = "";
	foreach($person as $key => $value) {
		if ($columns[$key]) {
			$query_part .= $key . '="' . $value .'",';
		}
	}
	$query_part = substr($query_part,0,-1);

	$query = 'select * from people where email="'.$person["email"].'";';
	
	$res = $mysqli->query($query);

	if ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
		$person["id"] = $row["id"];
		$query = 'update people set ' . $query_part . ' where id="' . $person["id"] . '";';
	} else {
		$person["id"] = get_person_id($person);
		$query = 'insert into people set id="' . $person["id"] . '",' . $query_part . ';';
	}

	$res = $mysqli->query($query);
	
	update_role($person);

	if (!$res) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "FAILED $query";
		exit(1);
	}
	
	$res = sign_in($person);

	if (!$res) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "FAILED to sign in";
		exit(1);
	} else {
		header("HTTP/1.0 200 OK");
		echo "Signed In";
		exit(1);
	}
	
?>

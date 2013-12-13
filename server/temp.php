<?php

	include('functions.php');
	$query = "select * from people limit 1";
	$res = $mysqli->query($query);
	$row = $res->fetch_assoc();
	foreach($row as $title => $value) {
		echo $title . "<br/>";
	}
?>

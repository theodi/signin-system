<?php

	include('functions.php');

	$query = 'select firstname, lastname, email, photo from people inner join people_roles on people_roles.person_id=people.id where people_roles.role="Startup";';

	$res = $mysqli->query($query);

	$count = 0;
	if ($res->num_rows > 0) {	
		while ($row = $res->fetch_assoc()) {
			$array["results"][$count]["title"] = $row["firstname"] . ' ' . $row["lastname"];
			$array["results"][$count]["slug"] = strtolower($row["firstname"]) . '-' . strtolower($row["lastname"]);
			$array["results"][$count]["details"]["square"] = "img/person-generic.jpg";
		}		
		$count++;
	}
	echo (json_encode($array));

?>

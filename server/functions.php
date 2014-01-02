<?php

include('database-connector.inc.php');

date_default_timezone_set('UTC');

$yesterday = date("Y-m-d",time() - 86400);
$yesterday = $yesterday . "T23:59:59Z";
	
$query = "update in_out set checkout='$yesterday' where checkin<'$yesterday' and checkout='';";
$mysqli->query($query);

function get_person_id($person) {
	$key_string = $person["firstname"] . $person["lastname"] . $person["email"];
	return (md5($key_string));
}

function signed_in($person) {
	global $mysqli;
	$query = "select * from in_out where id='".$person["id"]."' and checkout='';";
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {
		return true;
	} 
	return false;
}

function sign_in($person) {
	global $mysqli;

	$query = "select * from in_out where id='".$person["id"]."' and checkout='';";
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {
		return false;
	} 

	$columns = getColumnsInTable("in_out");
	$query_part = "";
	foreach($person as $key => $value) {
		if ($columns[$key] > 0) {
			$query_part .= $key . '="' . $value .'",';
		}
	}
	$query_part = substr($query_part,0,-1);

	$date = date("Y-m-d",time());
	$time = date("H:i:s",time());
	$date_string = $date . 'T' . $time . 'Z';
	
	$query = "insert into in_out set ".$query_part.", checkin='$date_string', checkout='';";
	
	$res = $mysqli->query($query);
	
	if ($person["toSee"] != "") {
		handleNotifications($person);
	}

	return $res;
}

function handleNotifications($person) {
	$toSee = $person["toSee"];
//Some ODI specific hacks
	if (strpos($toSee,"@") === false) {
		if ($toSee == "gavin-starks") {
			$toSee = "gavin@theodi.org";
		} 
		if ($toSee == "stuart-colemant") {
			$toSee = "stuart@theodi.org";
		} 
		if ($toSee == "jeni-tennison") {
			$toSee = "jeni@theodi.org";
		} 
		if ($toSee == "david-tarrant") {
			$toSee = "davetaz@theodi.org";
		} 
	}
	if (strpos($toSee,"-") !== false and strpos($toSee,"@") === false) {
		$toSee = str_replace("-",".",$toSee) . "@theodi.org";
	}
// End hacks
	
	if (hasPushNotifications($toSee)) {
		require_once('push-notification.php');
		$title = "Reception Notification";
		$body = $person["firstname"] . ' ' . $person["lastname"] . ' is here to see you.';
		$button = "View";
		sendNotification($toSee,$title,$body,$button);
	}
	if (hasEmailAlerts($toSee)) {
		$subject = "ODI Reception: Visitor waiting";
		$body = $person["firstname"] . ' ' . $person["lastname"] . ' is here to see you.';
		sendReceptionEmail($toSee,$subject,$body);
	}
}

function sign_out($person) {
	global $mysqli;
	$query = "select * from in_out where id='".$person["id"]."' and checkout='';";
	$res = $mysqli->query($query);
	if ($res->num_rows < 1) {
		return false;
	} 
	$date = date("Y-m-d",time());
	$time = date("H:i:s",time());
	$date_string = $date . 'T' . $time . 'Z';
	$query = "update in_out set checkout='$date_string' where id='".$person["id"]."' and checkout='';";
	$res = $mysqli->query($query);
	return $res; 
}

function getColumnsInTable($table) {
	global $mysqli;
	$query = "select * from $table limit 1";
	$res = $mysqli->query($query);
	$row = $res->fetch_assoc();
	foreach($row as $title => $value) {
		$columns[$title] = 1;
	}
	return $columns;
}

function update_role($person) {
	global $mysqli;
	$id = $person["id"];
	$role = $person["role"];
	if ($role == "") {
		return true;
	}
	$query = 'delete from people_roles where (DATE(`valid_from`) = CURDATE()) and person_id="' . $id . '";';
	$res = $mysqli->query($query);
	$query = 'insert into people_roles set person_id="'.$id.'", role="'.$role.'";';  
	$res = $mysqli->query($query);
	return $res; 
}

function register_keycard($keycard_id) {
	global $mysqli;
	$keycard_id = trim($keycard_id);
	$query = 'select id,firstname,lastname from people inner join people_keycards on people.id=people_keycards.person_id where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$row = $res->fetch_row();
	$person["id"] = $row[0];
	$person["firstname"] = $row[1];
	$person["lastname"] = $row[2];
	if ($person["id"]) {
		if (signed_in($person)) {
			if (sign_out($person)) {
				$output = '<h1 class="touch-in-event">Signed Out:</h1><h2 class="touch-in-detail">' . $person["firstname"] . ' ' . $person["lastname"] . '</h2>';
				write_screen_output($output);
				return 204; 
			} else { 
				return 500; 
			}
		} else {
			if (sign_in($person)) { 
				$output = '<h1 class="touch-in-event">Signed In:</h1><h2 class="touch-in-detail">' . $person["firstname"] . ' ' . $person["lastname"] . '</h2>';
				write_screen_output($output);
				return 201; 
			} else { 
				return 500; 
			}
		}
	} else {
		$file = '../data/keycard.txt';	
		$handle = fopen($file,"w");
		if (fwrite($handle,$keycard_id) !== false) { return 202; } else { return 500; }
		fclose($handle);
	}
}

function write_screen_output($output) {
	$file = '../data/touch-event.html';	
	$handle = fopen($file,"w");
	fwrite($handle,$output);
	fclose($handle);
}

function associate_keycard($person_id,$keycard_id) {
	global $mysqli;

	$keycard_id = trim($keycard_id);

	$query = 'delete from people_keycards where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$query = 'insert into people_keycards set keycard_id="'.$keycard_id.'",person_id="'.$person_id.'";';
	$res = $mysqli->query($query);

	$file = '/tmp/foo.txt';	
	$handle = fopen($file,"w");
	fwrite($handle,$query);
	fclose($handle);

	return ($res);
}

function reset_keycards() {
	$handle = fopen('../data/keycard.txt','w');
	if (!$handle) {
		return 500;
	}
	fwrite($handle,"");
	fclose($handle);
	return 204;
}

function hasEmailAlerts($email) {
	global $mysqli;
	$query = 'select * from alerts where email="'.$email.'" and authenticated=1 and subscribed=1;';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {
		return true;
	} 
	return false;
}

function hasPushNotifications($email) {
	global $mysqli;
	$query = 'select * from device_id where email="'.$email.'";';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {
		return true;
	} 
	return false;
}

function sendReceptionEmail($email,$subject,$body) {
	$to = $email;
	$from = "ODI Reception <no-reply@reception.theodi.org>";
	$headers = "From:" . $from;
	$message = "Dear $email,

$body

Many thanks

The ODI Reception Robot
http://www.theodi.org";

	mail($to,$subject,$message,$headers);
}
//GOT HERE WITH UPDATES

function associate_keycard_member($member_id,$keycard_id) {
	global $mysqli;

	$keycard_id = trim($keycard_id);

	$query = 'delete from member_keycards where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$query = 'insert into member_keycards set keycard_id="'.$keycard_id.'",member_id="'.$member_id.'";';
	$res = $mysqli->query($query);

	return ($res);
}

function update_keycard_cache() {
	global $keycards_last_update;
	$cache_file = "_keycard.txt";
	
	$file = "keycard.txt";
	$last_modified = filemtime($file);		

	$keycards_last_update = $last_modified;	
	$handle = fopen($cache_file,"w");
	fwrite($handle,$last_modified);
	fclose($handle);	
}	

function is_member($person_id) {
	global $mysqli;
	$query = 'select member_keycards.member_id from member_keycards inner join people_keycards on people_keycards.keycard_id=member_keycards.keycard_id where people_keycards.person_id="'.$person_id.'";';
	$res = $mysqli->query($query);
	$row = $res->fetch_row();
	if ($row[0] != "") {
		return true;
	}	
	return false;
}

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
	return $res; 
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

// GOT HERE WITH CHANGES

function update_role($id,$role) {
	global $mysqli;
	$query = 'delete from people_roles where (DATE(`valid_from`) = CURDATE()) and person_id="' . $id . '";';
	$res = $mysqli->query($query);
	$query = 'insert into people_roles set person_id="'.$id.'", role="'.$role.'";';  
	$res = $mysqli->query($query);
	return $res; 
}

function add_staff_to_database($staff) {
	global $mysqli;
	for ($i=0;$i<count($staff);$i++) {
		$person = $staff[$i];
		$company = "The Open Data Institute";
		$key_string = trim($person["forname"]) . trim($person["surname"]) . trim($person["email"]);
        	$key = md5($key_string);
		$query = "select * from people where id='$key';";
		$res = $mysqli->query($query);
		if ($res->num_rows < 1) {
			$query = "insert into people set id='$key',firstname='".$person["forname"]."',email='".$person["email"]."',lastname='".$person["surname"]."',company='$company';";
			$res = $mysqli->query($query);
		}
		$query = 'select * from people_roles where person_id="'.$key.'";';
		$res = $mysqli->query($query);
		if ($res->num_rows < 1) {
			update_role($key,"staff");
		}
	}
}

function associate_keycard_person($person_id,$keycard_id) {
	global $mysqli;

	$keycard_id = trim($keycard_id);

	$query = 'delete from people_keycards where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$query = 'insert into people_keycards set keycard_id="'.$keycard_id.'",person_id="'.$person_id.'";';
	$res = $mysqli->query($query);

	update_keycard_cache();

	return ($res);
}

function associate_keycard_member($member_id,$keycard_id) {
	global $mysqli;

	$keycard_id = trim($keycard_id);

	$query = 'delete from member_keycards where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$query = 'insert into member_keycards set keycard_id="'.$keycard_id.'",member_id="'.$member_id.'";';
	$res = $mysqli->query($query);

	update_keycard_cache();

	return ($res);
}

global $keycards_last_update;
function keycard_processor() {
	global $mysqli;
	global $keycards_last_update;
	
	$file = "keycard.txt";
	$cache_file = "_keycard.txt";
	$last_modified = filemtime($file);
	clearstatcache();
	$cache_last_modified = file_get_contents($cache_file);
	if ($keycards_last_update == "" || $keycards_last_update < $cache_last_modified) {
		$keycards_last_update = $cache_last_modified;	
	}

	if ($keycards_last_update != $last_modified) {
		$keycard_id = file_get_contents($file);
		$query = 'select person_id from people_keycards where keycard_id="'.$keycard_id.'";';
		$res = $mysqli->query($query);
		$row = $res->fetch_row();
		$id = $row[0];
		if ($id) {
			if (signed_in($id)) {
				sign_out($id);
			} else {
				sign_in($id);
			}
		} else {
			echo "Keycard not known!\n";
		}
		update_keycard_cache();
	} 
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

function register_keycard($keycard_id) {
	global $mysqli;
	$keycard_id = trim($keycard_id);
	$query = 'select person_id from people_keycards where keycard_id="'.$keycard_id.'";';
	$res = $mysqli->query($query);
	$row = $res->fetch_row();
	$id = $row[0];
	if ($id) {
		if (signed_in($id)) {
			if (sign_out($id)) { return 204; } else { return 500; }
		} else {
			if (sign_in($id)) { return 201; } else { return 500; }
		}
	} else {
		$file = '../keycard.txt';	
		$handle = fopen($file,"w");
		if (fwrite($handle,$keycard_id) !== false) { return 202; } else { return 500; }
		fclose($handle);
	}
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

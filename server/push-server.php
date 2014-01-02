<?php

error_reporting(E_ALL ^ E_NOTICE);
include('database-connector.inc.php');

$debug = true;

if ($debug) {
	$handle = fopen('/tmp/log','a+');
	fwrite($handle,print_r($_SERVER,true));
	$headers = apache_request_headers();
	fwrite($handle,print_r($headers,true));
	$content = file_get_contents('php://input');
	fwrite($handle,$content);
	fclose($handle);
}

$uri = $_SERVER["REQUEST_URI"];

if (strpos($uri,"v1/devices") > 0) {
	$uri = substr($uri,strpos($uri,"devices"),strlen($uri));
	$bits = explode("/",$uri);	
	$device_id = $bits[1];
	$domain = $bits[3];
	$auth = $headers["Authorization"];
	$auth_bits = explode(" ",$auth);
	$user_id = $auth_bits[1];
	if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
		$ret = deleteUser($domain,$user_id,$device_id);
	} else {
		$ret = addUser($domain,$user_id,$device_id);
	}
	if ($ret) {
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 500 INTERNAL SERVER ERROR');
	}
	exit();
}

if (strpos($uri,"v1/pushPackages") > 0) {
	$content = json_decode($content,true);
	$id = $content["email"];
	require_once('createPushPackage.php');
	$file = create_push_package();
	header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".basename($file));
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($file));
	ob_clean();
	flush();
	ob_start('ob_gzhandler');
      readfile($file);
	unlink($file);
      exit;
}

function addUser($domain,$user_id,$device_id) {
	global $mysqli;
	$query = 'select * from device_id where email="'.$user_id.'" and device_id="'.$device_id.'";';
	$res = $mysqli->query($query);
	if ($res->num_rows > 0) {	
		return true;	
	} 
	$query = 'insert into device_id set email="'.$user_id.'", device_id="'.$device_id.'";';
	$res = $mysqli->query($query);
	if ($res) {	
		return true;	
	} 
	return false;
}

function deleteUser($domain,$user_id,$device_id) {
	global $mysqli;
	$query = 'delete from device_id where email="'.$user_id.'" and device_id="'.$device_id.'";';
	$res = $mysqli->query($query);
	if ($res) {	
		return true;	
	} 
	return false;
}

?>

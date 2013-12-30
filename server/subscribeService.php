<?php

	error_reporting(E_ALL ^ E_NOTICE);
	include('functions.php');

	$email = $_POST["email"];
	$type = $_POST["type"];
	if (!$email) {
		$email = $_GET["email"];
	}
	if (!$type) {
		$type = $_GET["type"];
	}
	if ($type != "midata" && $type != "alerts") {
		$ret = "type $type not valid";
		$type = null;
	}
	if (!$email || !$type) {
		header("HTTP/1.0 400 Bad Request");
		echo "FAILED with $ret and $email";
		exit(1);
	}

	$uniqid = uniqid();
	
	$table = $type;
	
	$query = 'select * from '.$table.' where email="'.$email.'";';
        $res = $mysqli->query($query);
        if ($res->num_rows > 0) {
        	$row = $res->fetch_assoc();
		if ($row["authenticated"] == 1) {
			//$ok = sendUnsubscribeEmail($email,$uniqid,$type);
			$ok = 1;
		} else {
			$query = 'update ' . $table . ' set urlkey="'.$uniqid.'" where email="'.$email.'";';
			$res2 = $mysqli->query($query);
			if ($res2) {
				$ok = sendSubscribeEmail($email,$uniqid,$type);
			}
		}
	} else {
		$query = 'insert into ' . $table . ' set email="'.$email.'",urlkey="'.$uniqid.'", authenticated=0;';
		$res2 = $mysqli->query($query);
		if ($res2) {
			$ok = sendSubscribeEmail($email,$uniqid,$type);
		}
	}

	echo "OK";	

function sendSubscribeEmail($email,$uniqid,$type) {
	$to = $email;
	$from = "ODI Reception <no-reply@reception.theodi.org>";
	$headers = "From:" . $from;
	$subject = "ODI Reception: Subscription to $type service";
	$message = "Dear $email,

You (or someone who entered your email) have requested to be subscribed to the ODI reception $type service. 

In order to confirm this subscription please click the link below:

http://orthanc.ecs.soton.ac.uk/~davetaz/odi/signin-system/build/subscribe?type=$type&urlkey=$uniqid

If you have not reqeusted a subscription to this server, we appologise for any inconvienence this email has caused. You do not need to perform any further action and can delete this email.

Many thanks

The ODI Reception Robot
http://www.theodi.org";
	mail($to,$subject,$message,$headers);
}

?>

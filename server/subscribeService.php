<?php

	error_reporting(E_ALL ^ E_NOTICE);
	include('functions.php');

	$email = $_POST["email"];
	if (!$email) {
		$email = $_GET["email"];
	}
	if (!$email) {
		header("HTTP/1.0 400 Bad Request");
		echo "FAILED with $ret and $email";
		exit(1);
	}

	$uniqid = uniqid();
	
	$alerts = false;
	$res2 = true;	

	$res1 = prepareSubscription("midata",$email,$uniqid);
	
	$query = 'select role from people inner join people_roles on people_roles.person_id=people.id where email="'.$email.'" order by people_roles.valid_from desc limit 1;';
        $res = $mysqli->query($query);
        if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $role = $row["role"];
                if ($role=="staff" || $role=="startup") {
                	$res2 = prepareSubscription("alerts",$email,$uniqid);
			$alerts = true;
		}
        }

	if ($alerts && $res1 && $res2) {
		sendSubscriptionEmail($email,$uniqid,true);
	} else if ($res1) {
		sendSubscriptionEmail($email,$uniqid,false);
	} else {
		header("HTTP/1.0 500 Internal Server Error");
		echo "FAILED for $email";
		exit(1);
	}

function prepareSubscription($table, $email, $uniqid) {
	global $mysqli;	
	$query = 'select * from '.$table.' where email="'.$email.'";';
        $res = $mysqli->query($query);
        if ($res->num_rows > 0) {
		$query = 'update ' . $table . ' set urlkey="'.$uniqid.'" where email="'.$email.'";';
	} else {
		$query = 'insert into ' . $table . ' set email="'.$email.'",urlkey="'.$uniqid.'", authenticated=0;';
	}
	$res2 = $mysqli->query($query);
	return $res2;
}

function sendSubscriptionEmail($email,$uniqid,$alerts) {
	$to = $email;
	$from = "ODI Reception <no-reply@reception.theodi.org>";
	$headers = "From:" . $from;
	if ($alerts) {
		$subject = "ODI Reception: Alerts & MiData Subscription";
	} else {
		$subject = "ODI Reception: MiData Subscription";
	}
	$message = "Dear $email,

You (or someone who entered your email) have requested access to one or more of the ODI reception services detailed below. 

If you have not requested a subscription to this server, we appologise for any inconvienence this email has caused. You do not need to perform any further action and can delete this email.

MiData
======

The ODI Reception MiData service offers (up to) monthly emails detailing your visits to the ODI. This raw data gives you back ALL the data we collected about you. 

In order to confirm this subscription please click the link below:

https://reception.office.theodi.org/subscribe?type=midata&email=$email&urlkey=$uniqid
";

if ($alerts) {
	$message .= "
Alerts
======

As a member of staff/startups you can also subscribe to live alerts about visitors who are on site to visit you. 

In order to configure your alerts please click the link below:

https://reception.office.theodi.org/subscribe?type=alerts&email=$email&urlkey=$uniqid
";
}

$message .="
Many thanks

The ODI Reception Robot
http://www.theodi.org";

	mail($to,$subject,$message,$headers);
}

?>

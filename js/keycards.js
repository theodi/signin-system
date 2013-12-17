
$.ajaxSetup ({
    // Disable caching of AJAX responses
    	cache: false
});

//$(document).ready(function() {
//	$("#add-card").click(function() {
//		read_card_1();
//	});
//});

function activateReader() {
	$("#new-card").html("Please put card on the reader"); 
	$("#add-card").hide(function() {
		$("#new-card").show(function() {
			readCard();
		});
	}); 
}

function readCard() {
	read_card = false;
	cont = true;
	date_object = new Date();
	start = date_object.getTime();
	while (read_card == false && cont == true) {
		read_card = readFile();
		now = new Date().getTime();
		if ((now - start) > 10000) {
			cont = false;
		}
	}
	if (read_card) {
		theResource = "data/keycard.txt";
		$.get(theResource, function(data) {
			$('#new-card').html("Registering Card: " + data);
			if (registerCard(data)) {
				$('#new-card').html("SUCCESS Registered: " + data);
				$("#add-card").show();
			} else {
				$('#new-card').html("Failed to register card (either try again or contact tech team for help)");			
			}
		});
	} else {
		$('#new-card').html("No card recognised");
		$("#add-card").show();
	}
}

function registerCard(keycard_id) {
	ret = false;
	console.log("Registering: " + keycard_id);
	$.ajax({
		type: "POST",
		url: "server/cardProcessor.php",
		data: { "action": "associate_keycard", "person_id": person.id, "keycard_id": keycard_id },
		success: function(data) { 
				ret = true; 
			},
		error: function (data) {
				ret = false; 
			},		
		async: false
	});
	return ret;
}

function readFile() {
	var now = Date.now();
	theResource = "data/keycard.txt";
	var ret = true;
	$.ajax({
		url:theResource,
		type:"head",
		success:function(res,code,xhr) {
			var last_modified = xhr.getResponseHeader("Last-Modified");
			last_modified = new Date(last_modified);
			if (last_modified > now) {
				ret = true;
			} else {
				ret = false;
			}
		},
		error: function(res,code,xhr) {
		},
		async: false
	});
	return ret;
}

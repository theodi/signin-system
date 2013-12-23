
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
	$.ajax({
		type: "POST",
		url: "server/cardProcessor.php",
		data: { "action": "reset_keycards" },
		success: function(data) { 
			$("#new-card").html("Please put card on the reader"); 
			$("#add-card").hide(function() {
				$("#new-card").show(function() {
					readCard();
				});
			}); 
		},
		error: function (data) {
			$('#new-card').html("Failed to register card (either try again or contact tech team for help)");
			$("#new-card").show();
		},		
		async: false
	});
}

function readCard(timeout) {
	data = readFile();
	if (!timeout) {
		timeout = Date.now() + 10000;
	}
	if (data == "" && (Date.now() < timeout)) {
		setTimeout(function() { readCard(timeout); },1000);
	}
	if (data != "") {
		$('#new-card').html("Registering Card: " + data);
		if (registerCard(data)) {
			$('#new-card').html("SUCCESS Registered: " + data);
				$("#add-card").show();
		} else {
			$('#new-card').html("Failed to register card (either try again or contact tech team for help)");
		}
	} else if (Date.now() > timeout) {
		$('#new-card').html("No card recognised");
		$("#add-card").show();
	}
}

function readFile() {
	red = "";
	theResource = "data/keycard.txt";
	$.ajax({
		type: "GET",
		url: theResource,
		success: function(data) { 
				ret = data; 
			},
		error: function (data) {
				ret = ""; 
			},		
		async: false
	});
	return ret;
}

function registerCard(keycard_id) {
	ret = false;
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

$.ajaxSetup({ cache: false });

var person = {};

$( document ).ready(function() {
	registerListeners();
});

function registerListeners() {
	$("#welcome").click(function () {
		showEmailInput();
	});
	$("#home").click(function () {
		goHome();
	});
	$("#email-next").click(function () {
		if ($("#email-form")[0].checkValidity()) {
			getPersonFromEmail($("#email-input").val());
		}
	});
	$("#name-next").click(function () {
		if ($("#details")[0].checkValidity()) {
			person.firstName = $("#firstname").val();
			person.lastName = $("#lastname").val();
			person.email = $("#email").val();
			person.sector = $("#sector").val();
			if (!person.sector) {
				requestSectorForPerson(person);
			} else {
				processNFCCard(person);	
			}
		}
	});	
	$("li").click(function () {
		if (  $( this ).attr("id") == "sectorOption") {
			person.sector = $( this ).attr("value");
			processNFCCard(person);
		}
	});
}

function requestSectorForPerson(person) {
	showSection('sign-in-sector');	
	console.log(person);
}

function showEmailInput() {
	showSection('sign-in-email');
}

function processNFCCard(person) {
	$.ajax({
		type: 'get',
		url: 'server/hasIDCard.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			showDone();
		},
		error: function() {
			showNFCOption(person);
		}
	});	
}

function getPersonFromEmail(emailinput) {
	$.ajax({
		type: 'get',
		url: 'server/getPersonFromEmail.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			person = data;
			showNameInput(person);
		},
		error: function() {
			console.log("got an error");
			var data = {};
			data.email = emailinput;
			person = data;
			showNameInput(person);
		}
	});	
}

function showDone() {
	sections = document.getElementsByTagName(name);
	for (i=0;i<sections.length;i++) {
		if (section.is(':visible')) {
			section.fadeOut(function() {
				$("#complete").fadeIn( function() {
					setTimeOut(goHome(),2000);
				});
			});
		}
	}
}

function goHome() {
	showSection('welcome');
}

function showNameInput(person) {
	$("#email").val(person.email);
	$("#email").prop('disabled', true);
	
	if (person.firstName) {
		$("#details").html("Is this you? <br/> Please correct any details that are wrong and click next, else click back to start again.");
	}
	$("#sign-in-email").fadeOut(function() {
		$("#sign-in-name").fadeIn(function () {
			$('form:first *:input[type!=hidden]:first').focus();
		});
	});
}

function showSection(sectionName) {
	sections = document.getElementsByTagName("section");
	for (i=0;i<sections.length;i++) {
		section = sections[i];
		currentSection = section.getAttribute("id");
		if ($('#'+currentSection).is(':visible')) {
			$('#'+currentSection).fadeOut(function() {
				$("#"+sectionName).fadeIn(function() {
					$('form:first *:input[type!=hidden]:first').focus();
				});
			});
		}
	}
}

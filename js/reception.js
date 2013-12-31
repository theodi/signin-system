$.ajaxSetup({ cache: false });

var person = {};

$( document ).ready(function() {
	registerReceptionListeners();
	$("#name").hide();
});

function registerReceptionListeners() {
	$("#reception-email-next").click(function () {
		if ($("#email-form")[0].checkValidity()) {
			processEmail($("#email-input").val());
		}
	});
	$("#alerts-subscribe").click(function() {
		if ($("#name").val() == "") {
			processSubscription(person,"alerts");
		}
	});
	$("#midata-subscribe").click(function() {
		if ($("#name").val() == "") {
			processSubscription(person,"midata");
		}
	});
}

function updateSectionContent(type,content) {
	if (type == "alerts") {
		$("#alerts-subscription-status").html(content);
	}
	if (type == "midata") {
		$("#midata-subscription-status").html(content);
	}
}

function processSubscription(person,type) {
	content = '<img src="../img/ajax-loader.gif" alt="Loading"/>';
	updateSectionContent(type,content);
	$.ajax({
		type: 'post',
		url: 'server/subscribeService.php',
		timeout: 2000,
		data: {email: person.email, type: type},
		success: function(data) {
			content = "Success: An email has been sent to your email to confirm the subscription.";
			updateSectionContent(type,content);
		},
		error: function() {
			content = "Error: There was an error processing your request, please try again.";
			updateSectionContent(type,content);
		}
	});	
}

function processEmail(emailinput) {
	$.ajax({
		type: 'post',
		url: 'server/getSubscriptionsFromEmail.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			person = data;
			person.email = emailinput;
			console.log(person);
			if (person.miData == 1) {
				$("#midata").val("Retry Subscription");
			} 
			if (person.alerts == 1) {
				$("#alerts").val("Retry Subscription");
			}
			if (person.alerts_available == 1) {
				$("#alerts-section").show();
			}
			$("#email").val(person.email);
			$("#email").prop('disabled', true);
			showSection('complete');
		},
		error: function() {
			goHome();
		}
	});	
}

function goHome() {
	resetForms();
	showSection('welcome-email');
}

function resetForms() {
	forms = document.getElementsByTagName("form");
	for (i=0;i<forms.length;i++) {
		form = forms[i];
		formId = form.getAttribute("id");
		$('#'+formId)[0].reset();
	}
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

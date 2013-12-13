$.ajaxSetup({ cache: false });

var person = {};
var staff = {};
var timeout;

$( document ).ready(function() {
	loadStaff();	
	registerListeners();
});

function registerListeners() {
	$("#welcome").click(function () {
		showEmailInput();
	});
	$("#home").click(function () {
		goHome();
	});
	$("#home-button").click(function () {
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
				showHereToSee();	
			}
		}
	});	
	$("li").click(function () {
		if (  $( this ).attr("name") == "staffOption") {
			person.toSee = $( this ).attr("value");
			showTerms();
		}
		if (  $( this ).attr("id") == "sectorOption") {
			person.sector = $( this ).attr("value");
			showHereToSee();
		}
	});
	$('input[name=to-see]').keyup(function() {
		manageStaffOptions($('#here-to-see-input').val());
	});
	$('#terms-agree').click(function() {
		recordPerson(person);
		showDone();
	});
}

function recordPerson(person) {
	console.log(person);
}

function loadStaff() {
	$.ajax({
	  dataType: "json",
	  url: 'js/team.json',
	  timeout: 2000,
	  success: function(data) {
	        staff = data.results;
	        populate_staff(staff);
	  },
	  error: function() {
	         console.log("error loading staff");
	  }
	});
}

function populate_staff(staff) {
	for (i=0;i<staff.length;i++) {
                name = staff[i].title;
                key = staff[i].slug;
                img = staff[i].details.square;
                $('#to-see-sugestions').append('<li style="display: none;" name="staffOption" id="'+key+'" value="'+key+'"><figure class="staffOption"><img class="staffPic" src="'+img+'"/><caption>'+name+'</caption></figure>');
        }
	$("li").click(function () {
		if (  $( this ).attr("name") == "staffOption") {
			person.toSee = $( this ).attr("value");
			showTerms();
		}
		if (  $( this ).attr("id") == "sectorOption") {
			person.sector = $( this ).attr("value");
			showHereToSee();
		}
	});
}

function requestSectorForPerson(person) {
	showSection('sign-in-sector');	
}

function showEmailInput() {
	showSection('sign-in-email');
}

function showHereToSee() {
	showSection('sign-in-to-see');
}

function showTerms() {
	showSection('sign-in-terms');
}

function manageStaffOptions(input) {
        input = input.toLowerCase();
	viscount = 0;
        for (i=0;i<staff.length;i++) {
		member = staff[i];
		name = (member.title).toLowerCase();
		key = member.slug;
		if (name.substring(0,input.length) == input && input.length > 0) {
			$('#'+key).fadeIn('fast');
			viscount = viscount + 1;
		} else {
			$('#'+key).fadeOut('fast');
		}
	}
	if (viscount > 0) {
		$('#suggestions').fadeIn('fast');
		$('#to-see-next').fadeOut('fast');	
	} else {
		$('#suggestions').fadeOut('fast');
		$('#to-see-next').fadeIn('fast');	
	}
}

function processNFCCard(person) {
	$.ajax({
		type: 'get',
		url: 'server/hasIDCard.php',
		timeout: 2000,
		data: {email: person.email},
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
	showSection('complete');
	timeout = setTimeout(function(){goHome();},5000);
}

function goHome() {
	resetForms();
	clearTimeout(timeout);
	manageStaffOptions("");
	showSection('welcome');
}

function resetForms() {
	forms = document.getElementsByTagName("form");
	for (i=0;i<forms.length;i++) {
		form = forms[i];
		formId = form.getAttribute("id");
		$('#'+formId)[0].reset();
	}
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

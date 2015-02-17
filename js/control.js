$.ajaxSetup({ cache: false });

var person = {};
var staff = {};
var startups = {};
var bannerContent = "";
var timeout;

$( document ).ready(function() {
	loadStaff();	
	registerListeners();
	setInterval(function() {bannerThread();},2000);
});

function registerListeners() {
	$("#welcome").click(function () {
		showEmailInput();
	});
	$("#home").click(function () {
		goHome();
	});
	$("#home-button1").click(function () {
		goHome();
	});
	$("#home-button2").click(function () {
		goHome();
	});
	$("#email-input").change(function() {
		//if ($("#email-form")[0].checkValidity());
			getPersonFromEmail2($("#email-input").val());
		//}
	});
	$("#email-next").click(function () {
		if ($("#email-form")[0].checkValidity()) {
			getPersonFromEmail($("#email-input").val());
		}
	});
	$("#name-next").click(function () {
		if ($("#email-form")[0].checkValidity()) {
			person.firstname = $("#firstname").val();
			person.lastname = $("#lastname").val();
			person.email = $("#email-input").val();
//			person.sector = $("#sector").val();
			person.role = $("#role").val();
//			if (!person.sector) {
//				requestSectorForPerson(person);
//			} else {
			recordPerson(person);
			//	showHereToSee();	
//			}
		}
	});
/*	
	$("#change-sector").click(function () {
		if ($("#details")[0].checkValidity()) {
			person.firstname = $("#firstname").val();
			person.lastname = $("#lastname").val();
			person.email = $("#email").val();
			person.role = $("#role").val();
			person.sector = "";
			requestSectorForPerson(person);
		}
	});
*/
	$("li").click(function () {
		if (  $( this ).attr("name") == "staffOption") {
			person.toSee = $( this ).attr("value");
			showTerms();
		}
		/*
		if (  $( this ).attr("id") == "sectorOption") {
			person.sector = $( this ).attr("value");
			$("#sector").val(person.sector);
			showHereToSee();
		}
		*/
	});
	$('input[name=to-see]').keyup(function() {
		manageStaffOptions($('#here-to-see-input').val());
	});
	$('#to-see-next').click(function() {
                person.toSee = $('#here-to-see-input').val()
		showTerms();
        });
	$('#terms-agree').click(function() {
		recordPerson(person);
	});
	$("#terms-cancel").click(function () {
		goHome();
	});
	$('#sign-out').click(function() {
		signOut(person);
	});
	$('#add-card').click(function() {
		activateReader();
		clearTimeout(timeout);
		timeout = setTimeout(function(){goHome();},60000);
	});
	$("#midata-subscribe").click(function() {
		processSubscriptions(person);
	});
}

function recordPerson(person) {
	$.ajax({
		type: 'post',
		url: 'server/signInPerson.php',
		timeout: 3000,
		data: person,
		success: function(data) {
			$('#banner-content').html("Signed in: <b>" + person.firstname + " " + person.lastname + "</b>");
			$('#banner').slideDown(function() {
				setTimeout(function() {$('#banner').slideUp();},3000);
			});
			goHome();
		},
		error: function() {
			goHome();
		//	showDone();
		}
	});	
}

function signOut(person) {
	$.ajax({
		type: 'post',
		url: 'server/signOutPerson.php',
		timeout: 3000,
		data: person,
		success: function(data) {
			goHome();
		},
		error: function() {
			console.log("got an error");
			goHome();
		}
	});	
}

function loadStaff() {
	$.ajax({
	  dataType: "json",
	  url: 'js/team.json',
	  timeout: 2000,
	  success: function(data) {
	        staff = data.results;
	        populate_staff(staff);
		  loadStartups();	
	  },
	  error: function() {
	         console.log("error loading staff");
	  }
	});
}

function loadStartups() {
	$.ajax({
	  dataType: "json",
	  url: 'server/getStartUps.php',
	  timeout: 2000,
	  success: function(data) {
	        startups = data.results;
		  staff = staff.concat(startups);
	        populate_staff(startups);
	  },
	  error: function() {
	         console.log("error loading startups");
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
		/*
		if (  $( this ).attr("id") == "sectorOption") {
			person.sector = $( this ).attr("value");
			showHereToSee();
		}
		*/
	});
}
/*
function requestSectorForPerson(person) {
	showSection('sign-in-sector');	
}
*/
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

function processEmailSubscription(emailinput) {
	$.ajax({
		type: 'post',
		url: 'server/getSubscriptionsFromEmail.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			person = data;
			person.email = emailinput;
			if (person.miData == 1) {
				$("#midata-subscribe").val("Retry Subscription");
			} 
		},
		error: function() {
			goHome();
		}
	});	
}

function getPersonFromEmail2(emailinput) {
	processEmailSubscription(emailinput);
	$.ajax({
		type: 'get',
		url: 'server/getPersonFromEmail.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			person = data;
			if (person.signedIn) {
				showSection('sign-out');
			} else {
				$("#firstname").val(person.firstname);
				$("#lastname").val(person.lastname);
				$("#role").val(person.role);
			}
		},
		error: function() {
			var data = {};
			data.email = emailinput;
			person = data;
		}
	});	
}

function getPersonFromEmail(emailinput) {
	processEmailSubscription(emailinput);
	$.ajax({
		type: 'get',
		url: 'server/getPersonFromEmail.php',
		timeout: 2000,
		data: {email: emailinput},
		success: function(data) {
			person = data;
			if (person.signedIn) {
				showSection('sign-out');
			} else {
				showNameInput(person);
			}
		},
		error: function() {
			var data = {};
			data.email = emailinput;
			person = data;
			showNameInput(person);
		}
	});	
}

function showDone() {
	showSection('complete');
	timeout = setTimeout(function(){goHome();},8000);
}

function goHome() {
	resetForms();
//	$("#sector-name").hide();
//	$("#role-name").hide();
	$("#new-card").html("Please put card on the reader");
	$("#new-card").hide();
	$("#add-card").show();
	$("#midata-subscribe").val("Subscribe");
	$("#midata-subscribe").show();
	$("#midata-subscription-status").html('');
	
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
	
	if (person.firstname) {
		$("#role-name").fadeIn('fast');
		$("#details-text").html("Is this you? <br/> Please correct any details that are wrong and click next, else click back to start again.");
		$("#firstname").val(person.firstname);
		$("#lastname").val(person.lastname);
		$("#role").val(person.role);
//		if (person.sector) {
//			$("#sector").val(person.sector);
//			$("#sector-name").fadeIn('fast');
//		}
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

function bannerThread() {
	$.ajax({
		type: 'get',
		url: 'data/touch-event.html',
		cache: false,
		timeout: 1000,
		success: function(data) {
			if (data != bannerContent) {
				bannerContent = data;
				$('#banner-content').html(data);
				$('#banner').slideDown(function() {
					setTimeout(function() {$('#banner').slideUp();},3000);
				});
			}			
		},
		error: function() {
		}
	});	
}

function processSubscriptions(person) {
	$("#midata-subscribe").hide();
	content = '<img src="../img/ajax-loader.gif" alt="Loading"/>';
	$("#midata-subscription-status").html(content);
	$.ajax({
		type: 'post',
		url: 'server/subscribeService.php',
		timeout: 2000,
		data: {email: person.email },
		success: function(data) {
			content = "Success: An email has been sent to your email to confirm the subscription.";
			$("#midata-subscription-status").html(content);
		},
		error: function() {
			content = "Error: There was an error processing your request, please try again.";
			$("#midata-subscription-status").html(content);
		}
	});	
}

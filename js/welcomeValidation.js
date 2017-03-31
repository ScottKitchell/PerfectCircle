$(document).ready(function(){
	//Validate email
	function validateEmail(email){
		var re = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
		return re.test(email);
	}
	//Validate password
	function validatePassword(password){
		var re = /^[a-zA-Z0-9!@#$%^&*]{6,16}$/;
		return re.test(password);
	}
	//Validate new email
	$("#newEmail").blur(function(){ 
		$(document.body).css({ 'cursor': 'progress' });
		var email = $("#newEmail").val();
		if(!validateEmail(email)){
			$("#newEmailFail").slideDown('fast').html("Sorry, your email isn't right.");
		} else {
			//check to see if already in use
			$.post("php/validateUsername.php",
			 {
				 email: $("#newEmail").val(),
			 },
			 function(data, status){
				 if(data=='false'){
					 $("#newEmailFail").slideDown('fast').html("Sorry, this email is already in use.");
				 } else {
					 $("#newEmailFail").slideUp('fast');
				 }
			 });
		};
		$(document.body).css({ 'cursor': 'default' }); 
	});
	
	//validate password
	$("#newPassword1").blur(function(){ 
		var password = $("#newPassword1").val();
		if(!validatePassword(password)){
			$("#newPassword1Fail").slideDown('fast').html("A password must be between 6 and 16 characters long.");
		} else {
			$("#newPassword1Fail").slideUp('fast');
		}; 
	});
	
	//validate password
	$("#newPassword2").blur(function(){ 
		var password1 = $("#newPassword1").val();
		var password2 = $("#newPassword2").val();
		if(password1 != password2){
			$("#newPassword2Fail").slideDown('fast').html("Sorry, the passwords don't match.");
		} else {
			$("#newPassword2Fail").slideUp('fast');
		}; 
	});
	
	//Validate before sign-up submit
	$("#newSubmit").click(function(e){
		e.preventDefault();
		$(document.body).css({ 'cursor': 'progress' });
		var valError=false;
		if(!validateEmail($("#newEmail").val())){
			$("#newEmailFail").slideDown('fast').html("Sorry, your email isn't right.");
			valError=true;
		}  else {
			//check to see if already in use
			$.post("php/validateUsername.php",
			 {
				 email: $("#newEmail").val(),
			 },
			 function(data, status){
				 if(data=='false'){
					 valError=true;
				 }
			 });
		};
		if(!validatePassword($("#newPassword1").val())){
			$("#newPassword1Fail").slideDown('fast').html("A password must be between 6 and 16 characters long.");
			valError=true;
		}
		if($("#newPassword1").val() != $("#newPassword2").val()){
			$("#newPassword2Fail").slideDown('fast').html("Sorry, the passwords don't match.");
			valError=true;
		}
		if(valError==false){
			$("#signUpForm").submit();
		}
		$(document.body).css({ 'cursor': 'default' });
	});
	
	//Validate email
	$("#Email").blur(function(){ 
		var email = $("#Email").val();
		if(!validateEmail(email)){
			$("#emailFail").slideDown('fast').html("Your email probably isn't right.");
		} else {
			$("#emailFail").slideUp('fast');
		}; 
	});
	
	//validate login
	$("#Submit").click(function(e){
		e.preventDefault();
		$(document.body).css({ 'cursor': 'progress' });
		$.post("php/validateSignIn.php",
		 {
			 email: $("#Email").val(),
			 password: $("#Password").val()
		 },
		 function(data, status){
			 if(data=='false'){
				 $("#passwordFail").slideDown('fast').html("Your email or password are incorrect.");
			 } else {
				 $("#signInForm").submit();
			 }
		 });
		 $(document.body).css({ 'cursor': 'default' });
	 });
});
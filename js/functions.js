$(document).ready(function(){
	
	//Profile
	$("#Profile").change(function(event){
		event.preventDefault();
		$(document.body).css({ 'cursor': 'wait' });
		$.post("php/login.php",
		{
			user: $("#Profile").val()
		},
		function(data, status){
			 $("#profileName").html(data);
			 $(document.body).css({ 'cursor': 'default' });
			 location.reload(true); 
		 });
	 });
	
	//Notifications
	//setInterval(checkNotifications(), 30000);
	function checkNotifications(){
		var num = $("#numNotifications").val();
		var newNum = 0;
		$.post("php/checkNotifications.php",
		{
			 level: '0'
		},
		 function(data, status){
			 $("#notifications-mdBtn").html('<img src="images/notification.gif"><div id="notifications">'+data+'</div>');
			 $("#numNotifications").val(data);
			 newNum = data;
		 });
		 if(newNum > num){
			$.post("php/checkNotifications.php",
			{
				 level: '1'
			},
			 function(data, status){
				 $("#notificationsList1").html(data);
				 $("#notificationsList2").html(data);
				 
			 });
		 }
	}
	
	
	// New Post
	$("form.postForm").submit(function(event){
		event.preventDefault();
		$(document.body).css({ 'cursor': 'progress' });
		if($("#discussion_text").val()=="" && $("#discussion_file").val()=="" && $("#discussion_link").val()==""){
			return;
		}
		if($('#allow_share').is(':checked')){var shareVal = '1';} else {var shareVal = '0';}
		$.post("php/post.php",
		 {
			 postType: $("#discussion_action").val(),
			 user: $("#discussion_user").val(),
			 friend: $("#discussion_friend").val(),
			 text: $("#discussion_text").val(),
			 file: $("#discussion_file").val(),
			 linkAddress: $("#discussion_link").val(),
			 allowShare: shareVal
		 },
		 function(data, status){
			 $("#newPost").prepend('<div class="row"><!--Post-->' + data + '</div><!--END Post-->').slideDown(220);
			 desktopImagesOnly();
			 $("#articleUploadBox, #imageUploadBox, #videoUploadBox, #linkUploadBox").slideUp(220);
			 $(".postForm").find("input[type=text], textarea, input[type=file], input[type=url]").val("");
			 $("#imagePreview").html();
			 $(this).find("input.deleteFile").val();
			 $(document.body).css({ 'cursor': 'default' });
		 });
	 });
	 
	 
	 //New like
	 $(".likeBtn").click(function(event){
		 event.preventDefault();
		 $(document.body, this).css({ 'cursor': 'progress' });
		 var id = $(this).attr("name");
		 $.post("php/like.php",
		 {
			 action: $("#"+id+"_laction").val(),
			 user: $("#"+id+"_user").val(),
			 post: id
		 },
		 function(data, status){
			 $("#"+id+"_likes").html(data);
			 $("#"+id+"_laction").val("unlike").addClass("selected");
			 $(document.body, this).css({ 'cursor': 'default' });
		 });
	 });
	 
	 //New share
	 $(".shareBtn").click(function(event){
		 event.preventDefault();
		 $(document.body, this).css({ 'cursor': 'progress' });
		 var id = $(this).attr("name");
		 $.post("php/share.php",
		 {
			 action: $("#"+id+"_saction").val(),
			 user: $("#"+id+"_user").val(),
			 post: id
		 },
		 function(data, status){
			 $("#"+id+"_shares").html(data);
			 $(document.body, this).css({ 'cursor': 'default' });
		 });
	 });
	 
	 //New comment like
	 $(".likeCBtn").click(function(event){
		 event.preventDefault();
		 $(document.body, this).css({ 'cursor': 'progress' });
		 var id = $(this).attr("name");
		 $.post("php/likeC.php",
		 {
			 action: 'like',
			 comment: id
		 },
		 function(data, status){
			 $("#"+id+"_likesC").html(data);
			 //$("#"+id+"_lcaction").val("unlike");
			 $(document.body, this).css({ 'cursor': 'default' });
		 });
	 });
	 
	 //New comment button
	 $(".commentBtn").click(function(event){
		 event.preventDefault();
		 var id = $(this).attr("name");
		 $("#"+id+"_comment").focus();
	 });
	 
	
	//New comment
	 $(".commentForm").submit(function(event){
		 event.preventDefault();
		 $(document.body, this).css({ 'cursor': 'progress' });
		 var id = $(this).attr("name");
		 $.post("php/comment.php",
		 {
			 action: $("#"+id+"_caction").val(),
			 user: $("#"+id+"_user").val(),
			 post: id,
			 comment: $("#"+id+"_comment").val()
		 },
		 function(data, status){
			 $("#"+id+"_commentsBox").append(data);
			 $("#"+id+"_comment").val("")
			 $(document.body, this).css({ 'cursor': 'default' });
		 });
	});
	
	//New Pass on button
	 $(".commentBtn").click(function(event){
		 event.preventDefault();
		 var id = $(this).attr("name");
	 });
	 
	 
	 //Text section
	 $("#textUploadBtn").click(function(){
		 $(".types span").removeClass("selected");
		 $("#textUploadBtn").addClass("selected");
		 $("#articleUploadBox, #imageUploadBox, #videoUploadBox, #linkUploadBox").slideUp(220);
		 $("#discussion_action").val("1");
		 $("#discussion_file").val();
		 $("#discussion_link").val();
	 });
	 //Article Upload section
	 $("#articleUploadBtn").click(function(){
		 $(".types span").removeClass("selected");
		 $("#articleUploadBtn").addClass("selected");
		 $("#imageUploadBox, #videoUploadBox, #linkUploadBox").slideUp(220);
		 $("#articleUploadBox").slideDown(220);
		 $("#discussion_action").val("2");
		 $("#discussion_file").val();
		 $("#discussion_link").val($("#articleLink"));
	 });
	 //Image Upload section
	 $("#imageUploadBtn").click(function(){
		 $(".types span").removeClass("selected");
		 $("#imageUploadBtn").addClass("selected");
		 $("#articleUploadBox, #videoUploadBox, #linkUploadBox").slideUp(220);
		 $("#imageUploadBox").slideDown(220);
		 $("#discussion_action").val("3");
		 $("#discussion_file").val($("#imageFile"));
		 $("#discussion_link").val($("#imageLink"));
	 });
	 //Video Upload section
	 $("#videoUploadBtn").click(function(){
		 $(".types span").removeClass("selected");
		 $("#videoUploadBtn").addClass("selected");
		 $("#articleUploadBox, #imageUploadBox, #linkUploadBox").slideUp(220);
		 $("#videoUploadBox").slideDown(220);
		 $("#discussion_action").val("5");
		 $("#discussion_file").val($("#videoFile"));
		 $("#discussion_link").val($("#videoLink"));
	 });
	 
	 $("#discussion_submit").click(function(){
		 $("#DiscussionForm").submit();
	 });
	 
	 //image uploader
	$("#forImageFile").on('click',(function(e) {
		e.preventDefault();
		$('#imageFile').focus();
	}));
	$("#imageFileForm").on('change',(function(e) {
		e.preventDefault();
		$('#imageFileForm').submit();
	}));
	
	$('#imageFileForm').submit(function() { 
            $(document.body, this).css({ 'cursor': 'progress' });
		$("#imagePreview").html('<div class="loadfile"><img class="loading" src="images/loading.gif"><br>uploading...</div>');
		var user = $("#discussion_user").val();
			 var options = { 
             beforeSubmit: function(){  //function to check file size before uploading.
				//check whether browser fully supports all File API
				if (window.File && window.FileReader && window.FileList && window.Blob)
				{ 
					if(!$('#imageFile').val()) //check empty input filed
					{
						 $('#imagePreview').html("Are you kidding me?");
						return false
					}	 
					var fsize = $('#imageFile')[0].files[0].size; //get file size
					var ftype = $('#imageFile')[0].files[0].type; // get file type
					//allow only valid image file types 
					switch(ftype)
					{
						case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg': case 'image/jpg':
						 break;
						default:
						$("#imagePreview").html("Sorry we havnt learnt to speak "+ftype+" yet.");
						return false
					}	 
					//Allowed file size is less than 3MB (1048576 * 3)
					if(fsize>3145728) 
					{
						$("#imagePreview").html("It's too big! <br />You can reduce the size of your photo using an image editor.");
						return false
					}			 
				}
				else
				{
					//Output error to older browsers that do not support HTML5 File API
					$("#DPchangeWait").html("Please upgrade your browser, because your current browser lacks some new features we need!");
					return false;
				}
			},
            resetForm: true,      // reset the form after successful submit 
			success: function(data){
				$("#imagePreview").html('<img src="users/'+ user +'/tmp/'+ data + '">');
				$("#discussion_file").val(data);
				$("#discussion_action").val("3");
				$("#imageFileForm").find("input.deleteFile").val('users/'+ user +'/tmp/'+ data);
				$(document.body, this).css({ 'cursor': 'default' });
		    },
		  	error: function() {
				$("#imagePreview").html('<div class="loadfile">Something went wrong.. try again?</div>');
				$(document.body, this).css({ 'cursor': 'default' });
	    	} 
		};
		$(this).ajaxSubmit(options);  //Ajax Submit form            
        // return false to prevent standard browser submit and page navigation 
        return false; 
    }); 
	
	$("#imageLink").change(function(){
		$(document.body, this).css({ 'cursor': 'progress' });
		var linkAddress = $(this).val();
		var ext = linkAddress.substr(linkAddress.lastIndexOf('.') + 1);
		if(ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'gif'){
			   var img = new Image();
			   img.src = linkAddress;
			if(img.height != 0){
				$("#discussion_link").val(linkAddress);
				$("#imagePreview").html('<img src="' + linkAddress + '" alt="' + linkAddress + '">');
				$("#discussion_action").val("4");
				$(document.body, this).css({ 'cursor': 'default' });
			} else {
				$("#imagePreview").html('<div class="loadfile">Well... <i>'+linkAddress+'</i> doesn\'t exist.</div>');
				$(document.body, this).css({ 'cursor': 'default' });
			}
		} else {
			$("#imagePreview").html('<div class="loadfile">huh? <i>.'+ext+'</i> isn\'t an image.</div>');
			$(document.body, this).css({ 'cursor': 'default' });
		}
	});
	
	//URL uploader
	$("#articleLink").on('change',(function(e) {
		e.preventDefault();
		var URL = $(this).val();
		$(document.body, this).css({ 'cursor': 'progress' });
		$("#articlePreview").html('<div class="loadfile"><img class="loading" src="images/loading.gif"><br>finding the article...</div>');
		$.post("php/urlUpload.php",
		 {
			 url: URL,
		 },
		 function(data, status){
			$("#articlePreview").html(data);
			$("#discussion_link").val(URL);
			$("#discussion_action").val("2");
			$(document.body, this).css({ 'cursor': 'default' });
		 });
	}));
	
	//New DP uploader
	$("#DPchangeBtn").click(function(e) {
		e.preventDefault();
		$("#imageFile").trigger('click');
	});
	$("#DPchangeBtn2").click(function(e) {
		e.preventDefault();
		$("#imageFile").trigger('click');
	});
	$("#DPchangeForm").on('change',(function() {
		$('#DPchangeForm').submit();
	}));
	
	$('#DPchangeForm').submit(function() { 
            var user = $("#UserID").val();
			$(document.body, this).css({ 'cursor': 'progress' });
			$("#DPchangeBtn, #DPchangeBtn2").hide();
			$("#DPchangeWait, #DPchangeWait2").html('uploading your face...');
			$("#DPchangeImg img").css({'opacity': '0.5'});
			 var options = { 
             target:   '#DPchangeImg div',   // target element(s) to be updated with server response 
             beforeSubmit: function(){  //function to check file size before uploading.
				//check whether browser fully supports all File API
				if (window.File && window.FileReader && window.FileList && window.Blob)
				{ 
					if(!$('#imageFile').val()) //check empty input filed
					{
						 $('#DPchangeWait').html("Are you kidding me?");
						return false
					}	 
					var fsize = $('#imageFile')[0].files[0].size; //get file size
					var ftype = $('#imageFile')[0].files[0].type; // get file type
					//allow only valid image file types 
					switch(ftype)
					{
						case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
						 break;
						default:
						$("#DPchangeWait").html("<b>"+ftype+"</b> Unsupported file type!");
						return false
					}	 
					//Allowed file size is less than 3.5 MB (3.4 * 1048576)
					if(fsize>3145728) 
					{
						$("#DPchangeWait").html(fsize +" bytes is too large! <br />Please reduce the size of your photo using an image editor.");
						return false
					}			 
				}
				else
				{
					//Output error to older browsers that do not support HTML5 File API
					$("#DPchangeWait").html("Please upgrade your browser, because your current browser lacks some new features we need!");
					return false;
				}
			},
            resetForm: true,      // reset the form after successful submit 
			success: function(data){
				$("#DPchangeBtn").show();
				$("#DPchangeImg div").html('<img src="users/'+ user +'/'+ data +'" alt="Resized Image">');
				$("#DPchangeImg2 div").html('<img src="users/'+ user +'/thumb_'+ data +'" alt="Resized Image">');
				$("#DPchangeImg img").css({'opacity': '1.0'});
				$("#DPchangeWait, #DPchangeBtn2").html('<span style="color:green;">Nice!</span>');
				$(document.body, this).css({ 'cursor': 'default' });
			}
		};
		$(this).ajaxSubmit(options);  //Ajax Submit form            
        // return false to prevent standard browser submit and page navigation 
        return false; 
    }); 
	
	
	$("#EditUser").submit(function(e) {
        $("#DPchangeForm").find(".deleteFile").val();
    });
	
	//delete files not submitted
	/*$(window).bind('beforeunload', function(){
		$.each($(".deleteFile"), function(index, value) {
			return 'File: '+ value;
			if($(this).val()!=""){
				$.post("php/fileDelete.php",
				{
					file: $(this).val(),
				},
				function(data, status){
					$(this).val();
				});
			}
		});
	});*/
	
	
	//Main Menu
	$("#mainMenuBtn").on('click',(function(e) {
			//e.preventDefault();
			$("#notifications").slideUp(220);
			$("#mainMenu").slideToggle(220);
	}));
	$("#mainMenu-mdBtn").on('click',(function(e) {
			//e.preventDefault();
			$("#notifications-md").slideUp(220);
			$("#mainMenu-md").slideToggle(220);
	}));
	
	//Notifications Menu
	$("#notificationsBtn").on('click',(function(e) {
			//e.preventDefault();
			$("#mainMenu").slideUp(220);
			$("#notifications").slideToggle(220);
	}));
	$("#notifications-mdBtn").on('click',(function(e) {
			//e.preventDefault();
			$("#mainMenu-md").slideUp(220);
			$("#notifications-md").slideToggle(220);
	}));
	
	
	
	$(".signInBtn").click(function(e){
		e.preventDefault();
		$("#signUp").slideUp(220);
		$("#signIn").slideDown(220);
	});
	$(".signUpBtn").click(function(e){
		e.preventDefault();
		$("#signIn").slideUp(220);
		$("#signUp").slideDown(220);
	});
	
	
	//Large images only shown for desktop
	desktopImagesOnly();
	function desktopImagesOnly(){
	if (window.screen.width >= 992){
		var images = document.getElementsByClassName('desktop-only');

		for (var i = 0; i < images.length; i++)
		{
			images[i].setAttribute('src', images[i].getAttribute('src2'));
		}
	}
	}
	
	
	
	
	
	
	
	
});


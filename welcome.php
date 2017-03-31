<?php
	include('php/functions.php');
	//Try Sign In
	if(isset($_SESSION['user'])){
		setcookie('user', $_SESSION['user'], time() + (86400 * 30), "/"); // 86400 = 1 day
		header('location:http://www.perfectcircle.social');
	} else if(isset($_COOKIE['user'])){
		$_SESSION['user'] = $_COOKIE['user'];
		setcookie('user', $_SESSION['user'], time() + (86400 * 30), "/"); // 86400 = 1 day
		header('location:http://www.perfectcircle.social');
	}
	//User Profile
	$user = database("SELECT ID, FirstName, LastName, DisplayImage FROM USERS WHERE ID = '".$_SESSION['user']."'");//User Prifile
?>


<!doctype html>
<html prefix="og: http://ogp.me/ns#">
<head>
<!-- --- meta data --- -->
<meta charset="utf-8">
<title>Perfect Circle - A Social Network Designed for Intorverts</title>
<meta name="description" content="Because not everyone is a loud-mouth." />
<meta name="keywords" content="social network, friends, introvert, new, get out" />
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- --- Open Graph --- -->
<meta property="og:title" content="Perfect Circle - A Social Network Designed for Intorverts" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Perfect Circle" />
<meta property="og:url" content="http://www.perfectcircle.social/welcome.php" />
<meta property="og:image" content="http://www.perfectcircle.social/images/perfect_circle.jpg" />
<meta property="og:description" content="Because not everyone is a loud-mouth." />
<!--  jQuery  -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!--  Hammer  -->
<!--<script src="js/hammer.min.js"></script>-->
<!--  Bootstrap  -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<!--  stylesheet  -->
<link rel="stylesheet" type="text/css" href="css/fluid.css" />
<!--  scripts  -->
<script src="js/textareaAutoResize.js"></script><!--textarea resize-->
<script src="js/jquery.form.js"></script> <!--AJAX form submit helper-->
<script src="js/functions.js"></script><!--general functions-->
<script src="js/welcomeValidation.js"></script>
<!--DiscussionBox auto resize-->
<!--<script>$(document).ready(function(){$("#discussion_text").autoResize();});</script>--> <!--Discussion auto resize-->



</head>

<body style="padding:0;">

<main class="container-fluid" style="margin:0; max-width:none; padding-top:0;">
	<section class="row welcome-header">
		<header class="row"><div class="col-xs-3">
              
            </div>
            <div class="col-xs-6">
              <a href="http://www.perfectcircle.social"><img src="images/logo_xs_white.png"></a>
            </div>
            <div class="col-xs-3">
               <a href="" class="btn-welcome signInBtn" id="signInBtn">Sign in.</a>
            </div>
        </header>
        <div class="row">
        	<h1>A social network for introverts to connect more naturally.</h1>
            <p class="subH1">These are the best friends you've never meet.</p>
        </div>
        <div class="row" id="signUp">
        	<form id="signUpForm" method="post" action="php/signUp.php">
        	<div class="row"><input type ="email" name="email" id="newEmail" placeholder="Email" required></div>
            <div class="valErrorLight" id="newEmailFail"></div>
            <div class="row"><input type ="password" name="password1" id="newPassword1" placeholder="Password" required></div>
			<div class="valErrorLight" id="newPassword1Fail"></div>
            <div class="row"><input type ="password" name="password2" id="newPassword2" placeholder="Confirm password" required></div>
            <div class="valErrorLight" id="newPassword2Fail"></div>
            <div class="row"><input type="submit" class="btn-welcome" id="newSubmit" value="Join now!"></div>
            </form>
            <div class="row"><a href="" class="signInBtn">Already have an account? Sign in here.</a></div>
        </div>
        <div class="row" id="signIn">
        	<form id="signInForm" method="post" action="php/signIn.php">
        	<div class="row"><input type ="email" name="email" id="Email" placeholder="Email"></div>
            <div class="valErrorLight" id="emailFail"></div>
            <div class="row"><input type ="password" name="password" id="Password" placeholder="Password"></div>
            <div class="valErrorLight" id="passwordFail"></div>
            <div class="row"><input type="submit" class="btn-welcome" id="Submit" value="Sign in"></div>
            </form>
            <div class="row"><a href="" class="signUpBtn">Don't have an account? Sign up here.</a></div>
            <div class="row"><a href="mailto:contact@perfectcircle.social?subject=Password reset
 &body=Hi, I've forgotten my password and need it to be reset! %0D%0A  %0D%0A My username (email) is: ENTER HERE" class="">Forgotten password?</a></div>
        </div>
    </section>
    
    <section class="row welcome-div">
    	<h1>A space to think quietly with others.</h1>
        <p class="subH1">Read a news article others have shared, or view an ongoing discussion. If you need to argue about politics that’s fine too though.</p>
        <img src="images/welcome-friends.jpg" alt="find your perfect friends">
    </section>
    
    <section class="row welcome-div" style="border-top:solid 1px #EEE;">
    	<h1>Nobody likes hiding behind a mask.</h1>
        <p class="subH1">Large photos and snippets of what you love allow others to get to know you without all the small talk.</p>
        <img src="images/welcome-profile.jpg" alt="meet Elizabeth">
    </section>
    
    <section class="row welcome-div" style="border-top:solid 1px #EEE;">
    	<h1>Be one of the first to join (It’s free if you sign up now)</h1>
        <p class="subH1">Why isn’t it free normally? We want to focus on sharing our passions, not big businesses or ads… but of course, the reality is, we still need money to support the developer’s coffee addiction.</p>
        <img src="images/welcome-coffee.jpg" alt="coffee addiction comic">
    </section>
    
    <section class="row welcome-footer">
    	<h1>Our aim is to help intoverts socialise more naturally.</h1>
        <p class="subH1">Let's build a social network that works for everyone.<br><a href="mailto:s.kitchell@live.com">Email me to find out more.</a></p>
        <footer>
        <img src="images/ScottKitchell.jpg" alt="Meet Scott Kitchell, the creator of Perfect Circle">
        <p>&copy; 2015 - <? echo date("Y"); ?> <a href="http://www.perfectcircle.social">Perfect Circle</a>. All rights reserved. | Developed by <a href="https://www.linkedin.com/profile/view?id=AAIAAA1ZuBYB9hS-1N_FCw29gNYVNiZslyfnI0g" target="_blank">Scott Kitchell</a></p>
        <p><b>This is not a fully functional application and may not operate as specified.</b></p></footer>
    </section>
</main><!--END container-main-->



</body>
</html>

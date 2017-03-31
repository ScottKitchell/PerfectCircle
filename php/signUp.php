<?
include('functions.php');
session_destroy();
session_start(); 
///////////////////////////////////////////////////////////// New User //////////////////////////////////////////////////////////////

if($_SERVER['REQUEST_METHOD'] != 'POST'){
	header('location:http://www.perfectcircle.social/welcome.php');
} else {
	//Set new ID
	$ID = newID(32);
	$_SESSION['user'] = $ID;
	//get POST details
	$Email = $_POST['email'];
	$Password = $_POST['password1'];
	
	//check user email is not already in use
	$user = database("SELECT ID FROM USERS WHERE Email = '".$Email."' ");
	if(!empty($user['ID_0'])){
		header('location:http://www.perfectcircle.social/welcome.php');
	}
	
	//Create User DB Record
	database("INSERT INTO USERS (ID, Email, Password, DisplayImage, Date_Time) VALUES ('".$ID."','".$Email."','".md5($Password)."', 'defaultUser.jpg', '".date('Y-m-d H:i:s')."')");
	//Create User Position DB Record
	database("INSERT INTO USER_POSITIONS (ID, UserID) VALUES ('".newID(16)."', '".$ID."')");
	//Create Friendship Position DB Record
	database("INSERT INTO FRIENDSHIP_POSITIONS (ID, USerID) VALUES ('".newID(16)."', '".$ID."')");
	//Create File Path
	$path = '../users/'.$ID.'/';
	if (!mkdir($path, 0777)) {
    	die('Failed to create folder "'.$path.'"');
	}
	$path = '../users/'.$ID.'/tmp/';
	if (!mkdir($path, 0777)) {
    	die('Failed to create folder "'.$path.'"');
	}
	//default user image
	copy('../images/defaultUser.jpg', '../users/'.$ID.'/defaultUser.jpg');
	copy('../images/thumb_defaultUser.jpg', '../users/'.$ID.'/thumb_defaultUser.jpg');
	
	header('location:http://www.perfectcircle.social/editProfile.php');
	/*
	//Create U
	$User = database("SELECT DoB, Gender, Country, Language, DisplayImage, Interests FROM USERS WHERE ID = '".$ID."'");
	//ID
	$PosID = newID(32);
	//UserID
	$UserID = $ID;
	//Gender
	$Gender = $User['Gender_0'];
	//Age
	$Age = age($User['DOB_0']);
	//Country
	$Country = $User['Country_0'];
	//Language
	$Language = $User['Language_0'];
	//DisplayImage
	if(isset($User['DisplayImage_0'])){$DisplayImg = 1;} else {$DisplayImg = 0;}
	//wrote interests
	$Interests = $User['Interests_0'];
	//create user position
	database("INSERT INTO USER_POSITIONS (ID, UserID, Gender, Age, Country, Language, DisplayImg, WroteInterests, ViewedInterests, LikedInterests, CommentedInterests, SharedInterests, Warmth, Liveliness, Privateness, PostText, PostImage, PostVideo, PostArticle, PostShare, Date_Time) 
	VALUES ('".$PosID."', '".$UserID."', '".$Gender."', '".$Age."', '".$Country."', '".$Language."', '".$DisplayImg."', '".$Interests."', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, NOW())");
	database("INSERT INTO FRIENDSHIP_POSITIONS (ID, UserID, Gender, Gender_W, Age, Age_W, Country, Country_W, Language, Language_W, DisplayImg, DisplayImg_W, WroteInterests, WroteInterests_W, ViewedInterests, ViewedInterests_W, LikedInterests, LikedInterests_W, CommentedInterests, CommentedInterests_W, SharedInterests, SharedInterests_W, Warmth, Warmth_W, Liveliness, Liveliness_W, Privateness, Privateness_W, PostText, PostText_W, PostImage, PostImage_W, PostVideo, PostVideo_W, PostArticle, PostArticle_W, PostShare, PostShare_W, Date_Time)
	VALUES ('".$PosID."', '".$UserID."', '".$Gender."', '0.2', '".$Age."','0.8', '".$Country."','0.8', '".$Language."','0.8', '".$DisplayImg."','0.8', '".$Interests."','0.8', '','0.8', '','0.8', '','0.8', '','0.8', 0,'0.8', 0,'0.8', 0,'0.8', 0,'0.8', 0,'0.8', 0,'0.8', 0,'0.8', 0,'0.8', NOW())");
	*/
}
?>
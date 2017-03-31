<?php
include('functions.php');
$PostID = safeText($_POST['post']);
$UserID = safeText($_POST['user']);
//$action = safeText($_POST['action']);
$like = database("SELECT ID FROM LIKES WHERE PostID = '".$PostID."' AND UserID = '".$UserID."'");
if(isset($like['ID_0'])){$action = 'unlike';} else {$action = 'like';}
switch($action){
	case 'like':
		$ID = newID(32);
		database("INSERT INTO LIKES (ID, UserID, PostID, DateTime) VALUES ('".$ID."','".$UserID."','".$PostID."','".date('Y-m-d H:i:s')."')"); //create like
		$numLikes = database("SELECT COUNT(ID) AS Likes FROM LIKES WHERE PostID = '".$PostID."'");
		$responce = $numLikes['Likes_0']." like";
		$add ="+";
		//update notifications
		$postDetails = database("SELECT UserID, LEFT(Text,50) AS Text FROM POSTS WHERE ID='".$PostID."'");
		if($postDetails['userID']!=$UserID){
			database("INSERT INTO NOTIFICATIONS (ID, UserID, FriendID, AboutType, AboutID, Preview, Seen, Date_Time) 
			VALUES ('".newID(32)."', '".$postDetails['UserID_0']."', '".$UserID."', 1, '".$PostID."', '".$postDetails['Text_0']."...', '0', '".date('Y-m-d H:i:s')."')");
		}
	break;
	case 'unlike':
		$ID = $_POST['id'];
		database("DELETE FROM LIKES WHERE UserID = '".$UserID."'"); //delete like
		$numLikes = database("SELECT COUNT(ID) AS Likes FROM LIKES WHERE PostID = '".$PostID."'");
		$responce = $numLikes['Likes_0']." like";
		$add ="-";
		//update notifications
		database("DELETE FROM NOTIFICATIONS WHERE AboutID = '".$PostID."' AND FriendID='".$UserID."' AND AboutType='1'");
	break;
}

echo $responce;

//Update connections
$SQL= "INSERT INTO CONNECTIONS (User1ID,User2ID,Score_Real,Date_Time)
		VALUES ('".$UserID."', (SELECT UserID FROM POSTS WHERE ID = '".$PostID."'),".$GLOBALS['like_value'].",'".date('Y-m-d H:i:s')."')
		ON DUPLICATE KEY UPDATE
		Score_Real = Score_Real ".$add." ".$GLOBALS['like_value'].", Date_Time = '".date('Y-m-d H:i:s')."'";
database($SQL);

//Update LIKED_INTERESTS
$post = database("SELECT PostType, Text, Keywords FROM POSTS WHERE ID = '".$PostID."'");
updateUserInterests($UserID, "Liked", $post['Text_0']);
?>
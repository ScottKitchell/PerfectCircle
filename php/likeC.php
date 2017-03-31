<?php
include('functions.php');
$CommentID = safeText($_POST['comment']);
$UserID = $_SESSION['user'];
//$action = safeText($_POST['action']);
$like = database("SELECT ID FROM LIKESC WHERE CommentID = '".$CommentID."' AND UserID = '".$UserID."'");
if(isset($like['ID_0'])){$action = 'unlike';} else {$action = 'like';}
switch($action){
	case 'like':
		$ID = newID(32);
		database("INSERT INTO LIKESC (ID, UserID, CommentID, Date_Time) VALUES ('".$ID."','".$UserID."','".$CommentID."','".date('Y-m-d H:i:s')."')"); //create like
		$numLikes = database("SELECT COUNT(ID) AS Likes FROM LIKESC WHERE CommentID = '".$CommentID."'");
		$responce = $numLikes['Likes_0']."";
		$add ="+";
		//update notifications
		$postDetails = database("SELECT UserID, LEFT(Comment,50) AS Text FROM COMMENTS WHERE ID='".$CommentID."'");
		database("INSERT INTO NOTIFICATIONS (ID, UserID, FriendID, AboutType, AboutID, Preview, Seen, Date_Time) 
		VALUES ('".newID(32)."', '".$postDetails['UserID_0']."', '".$UserID."', 4, '".$PostID."', '".$postDetails['Text_0']."...', '0', '".date('Y-m-d H:i:s')."')");
	break;
	case 'unlike':
		$ID = $_POST['id'];
		database("DELETE FROM LIKESC WHERE UserID = '".$UserID."'"); //delete like
		$numLikes = database("SELECT COUNT(ID) AS Likes FROM LIKESC WHERE CommentID = '".$PostID."'");
		$responce = $numLikes['Likes_0']."";
		$add ="-";
		//update notifications
		database("DELETE FROM NOTIFICATIONS WHERE AboutID = '".$CommentID."' AND FriendID='".$UserID."' AND AboutType='4'");
	break;
}

echo $responce;

//Update connections
$SQL= "INSERT INTO CONNECTIONS (User1ID,User2ID,Score_Real,Date_Time)
		VALUES ('".$UserID."', (SELECT UserID FROM COMMENTS WHERE ID = '".$CommentID."'),".$GLOBALS['likeC_value'].",'".date('Y-m-d H:i:s')."')
		ON DUPLICATE KEY UPDATE
		Score_Real = Score_Real ".$add." ".$GLOBALS['likeC_value'].", Date_Time = '".date('Y-m-d H:i:s')."'";
database($SQL);

//Update LIKED_INTERESTS
$post = database("SELECT Comment FROM COMMENTS WHERE ID = '".$CommentID."'");
updateUserInterests($UserID,"Liked",$post['Comment_0']);
?>
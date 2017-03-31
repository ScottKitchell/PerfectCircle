<?php
include('functions.php');
$PostID = safeText($_POST['post']);
$UserID = safeText($_POST['user']);
//$action = safeText($_POST['action']);
$share = database("SELECT ID FROM POSTS WHERE SharedItemID = '".$PostID."' AND UserID = '".$UserID."'");
if(isset($share['ID_0'])){$action = 'unshare';} else {$action = 'share';}
switch($action){
	case 'share':
		$ID = newID(32);
		database("INSERT INTO POSTS (ID, UserID, PostType, SharedItemID, AllowShare, DateTime) VALUES ('".$ID."','".$UserID."', '1','".$PostID."','1','".date('Y-m-d H:i:s')."')"); //create Share
		$numShares = database("SELECT COUNT(ID) AS Shares FROM POSTS WHERE SharedItemID = '".$PostID."'");
		$responce = $numShares['Shares_0']." share";
		$add ="+";
		//update notifications
		$postDetails = database("SELECT UserID, LEFT(Text,50) AS Text FROM POSTS WHERE ID='".$PostID."'");
		if($postDetails['userID']!=$UserID){
			database("INSERT INTO NOTIFICATIONS (ID, UserID, FriendID, AboutType, AboutID, Preview, Seen, Date_Time) 
			VALUES ('".newID(32)."', '".$postDetails['UserID_0']."', '".$UserID."', '6', '".$PostID."', '".$postDetails['Text_0']."...', '0', '".date('Y-m-d H:i:s')."')");
		}
	break;
	case 'unshare':
	$add ="-";
	$numShares = database("SELECT COUNT(ID) AS Shares FROM POSTS WHERE SharedItemID = '".$PostID."'");
	$responce = $numShares['Shares_0']." share";
	break;
}

echo $responce;

//Update connections
$SQL= "INSERT INTO CONNECTIONS (User1ID,User2ID,Score_Real,Date_Time)
		VALUES ('".$UserID."', (SELECT UserID FROM POSTS WHERE ID = '".$PostID."'),".$GLOBALS['share_value'].",'".date('Y-m-d H:i:s')."')
		ON DUPLICATE KEY UPDATE
		Score_Real = Score_Real ".$add." ".$GLOBALS['share_value'].", Date_Time = '".date('Y-m-d H:i:s')."'";
database($SQL);

//Update SHARED_INTERESTS
$post = database("SELECT PostType, Text, Keywords FROM POSTS WHERE ID = '".$PostID."'");
updateUserInterests($UserID, "Shared", $post['Text_0']);
?>
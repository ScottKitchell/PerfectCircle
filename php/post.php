<?php
include('functions.php');
$Error=0;
//Create Unique ID
$ID = newID(32);
//store post values
$UserID = safeText($_POST['user']);
$FriendID = safeText($_POST['friend']);
$PostType = safeText($_POST['postType']);
$Text = safeText($_POST['text']);
$File = safeText($_POST['file']);
$Link = safeText($_POST['linkAddress']);
$AllowShare = safeText($_POST['allowShare']);
$Keywords = safeText($_POST['keywords']);
$SharedPostID = safeText($_POST['sharedPost']);

if($Text==""){
	if(empty($File) && empty($Link)){
		$Error = 1;
	}
}
if($Error!=1){
	//Save Post to DB
	database("INSERT INTO POSTS (ID, UserID, FriendID, PostType, Text, File, Link, Keywords, AllowShare, SharedItemID, DateTime) VALUES ('".$ID."','".$UserID."','".$FriendID."','".$PostType."','".$Text."','".$File."','".$Link."','".$Keywords."','".$AllowShare."','".$SharedPostID."','".date('Y-m-d H:i:s')."')");
	//Move from temp folder
	if($PostType=="3"){
		$sourcePath = '../users/'.$UserID.'/tmp/'.$File;
		$targetPath = '../users/'.$UserID.'/'.$File;
		rename($sourcePath,$targetPath);
	}
	//Return Confirmation
	$html = displayPost($ID);
	echo $html;
	
	//Update COMMENTED_INTERESTS
	updateUserInterests($UserID,"COMMENTED", $Text);
}
?>
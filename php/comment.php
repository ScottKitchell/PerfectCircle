<?php
include('functions.php');
	$PostID = safeText($_POST['post']);
	$UserID = safeText($_POST['user']);
	$Comment = safeText($_POST['comment']);
	$action = safeText($_POST['action']);
	
	if($Comment==""){return;}
	switch($action){
		case 'comment':
			$ID = newID(32);
			database("INSERT INTO COMMENTS (ID, UserID, PostID, Comment, DateTime) VALUES ('".$ID."','".$UserID."','".$PostID."','".$Comment."','".date('Y-m-d H:i:s')."')"); //create comment
			$responce = "commented";
		break;
		case 'delete':
			$ID = $_POST['id'];
			database("DELETE FROM COMMENTS WHERE ID = '".$ID."'");
			$responce = "uncommented";
		break;
	}
		$comments = database("SELECT c.ID AS CommentID, c.UserID, u.FirstName, u.LastName, u.DisplayImage, c.Comment, COUNT(l.ID) AS Likes, c.DateTime 
		FROM COMMENTS AS c 
		LEFT JOIN USERS AS u ON c.UserID = u.ID 
		LEFT JOIN LIKESC AS l ON c.ID = l.CommentID
		WHERE c.ID = '".$ID."' GROUP BY c.ID");

				for($c=0; $c<$comments["numRows"]; $c++){
					$html .= ('<div class="comment row">
                    	<div class="col-xs-3 col-md-3"><div class="DP-xs"><img src="users/'.$comments['UserID_'.$c].'/thumb_'.$comments["DisplayImage_".$c].'"></div></div>
                        <div class="col-xs-9 col-md-9"><span class="commentName">'.$comments["FirstName_".$c].' '.$comments["LastName_".$c].'</span>
                        	<span class="commentTime">'.timeAgo($comments["DateTime_".$c]).'</span>
                            <span class="commentText">'.symbols($comments["Comment_".$c]).'</span>
                            <span class="commentLikes"><a href="" class="likeCBtn" name="'.$comments["CommentID_".$c].'" id="'.$comments["CommentID_".$c].'_lcactionBtn">like</a> - <span id="'.$comments["CommentID_".$c].'_likesC">'.$comments['Likes_'.$c].'</span> others liked this</span>
                        </div>
                    </div>');
				}
	
	echo $html;

//update notifications
		$AllComments = database("SELECT DISTINCT UserID FROM COMMENTS WHERE PostID = '".$PostID."' AND UserID != '".$UserID."'");
		for($i=0;$i<$AllComments['numRows'];$i++){
			if($AllComments['UserID_'.$i]!=$UserID){
				database("INSERT INTO NOTIFICATIONS (ID, UserID, FriendID, AboutType, AboutID, Preview, Seen, Date_Time) 
				VALUES ('".newID(32)."', '".$AllComments['UserID_'.$i]."', '".$UserID."', 5, '".$PostID."', '".substr($Comment,0,50)."...', '0', '".date('Y-m-d H:i:s')."')");
			}
		}
		$postDetails = database("SELECT UserID FROM POSTS WHERE ID='".$PostID."'");
		if($postDetails['UserID_'.$i]!=$UserID){
			database("INSERT INTO NOTIFICATIONS (ID, UserID, FriendID, AboutType, AboutID, Preview, Seen, Date_Time) 
			VALUES ('".newID(32)."', '".$postDetails['UserID_0']."', '".$UserID."', 2, '".$PostID."', '".substr($Comment,0,50)."...', '0', '".date('Y-m-d H:i:s')."')");
		}
		
//Update connections
$SQL= "INSERT INTO CONNECTIONS (User1ID,User2ID,Score_Real,Date_Time)
		VALUES ('".$UserID."', (SELECT UserID FROM POSTS WHERE ID = '".$PostID."'),".$GLOBALS['comment_value'].",'".date('Y-m-d H:i:s')."')
		ON DUPLICATE KEY UPDATE
		Score_Real = Score_Real + ".$GLOBALS['comment_value'].", Date_Time = '".date('Y-m-d H:i:s')."'";
database($SQL);

//Update COMMENTED_INTERESTS
updateUserInterests($UserID,"Commented",$Comment);
?>
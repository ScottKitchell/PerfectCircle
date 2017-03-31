<?
include('functions.php');
$userID = $_SESSION['user'];
if($_POST['level']=='1'){
	$notifications = notifications($userID);
	if($notifications['numRows']>0){
		$html = '';
		for($i=0; $i<$notifications['numRows']; $i++){
			$html .= '<li><a href="#" class="notificationLink">
						<div><div class="DP-sm"><img src="users/'.$notifications['FriendID_'.$i].'/thumb_'.$notifications['DisplayImage_'.$i].'" ></div></div>
						<div><b>'.$notifications['FriendName_'.$i].'</b> ';
			switch($notifications['AboutType_'.$i]){
				case '1': $html .= 'liked your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
				case '2': $html .= 'commented on your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
				case '3': $html .= 'shared your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
				case '4': $html .= 'liked your comment <i>'.$notifications['Preview_'.$i].'</i>'; break;
				case '5': $html .= 'also commented on a post <i>'.$notifications['Preview_'.$i].'</i>'; break;
			}
			$html .= '</div></a></li>';
		}
		echo $html;
	} else {
		echo '<li><a>Sorry, no new notifications!</a></li>';
	}
} else {
	$num = database("SELECT COUNT(ID) AS num FROM NOTIFICATIONS WHERE UserID='".$userID."' AND Seen='0'");
	echo $num['num_0'];
}

?>
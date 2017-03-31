<?
include('functions.php');
///////////////////////////////////////////////////////////// Sign In //////////////////////////////////////////////////////////////

if($_SERVER['REQUEST_METHOD'] != 'POST'){
	header('location:http://www.perfectcircle.social/welcome.php');
} else {
	
	$Email = $_POST['email'];
	$Password = $_POST['password'];
	
	$user = database("SELECT ID FROM USERS WHERE Email = '".$Email."' AND Password='".md5($Password)."'");
	if($user['ID_0']!=""){
		$_SESSION['user'] = $user['ID_0'];
		setcookie('user', $user['ID_0'], time() + (86400 * 30), "/"); // 86400 = 1 day
		
		userPosition($_SESSION['user']);//update user position (and connections)
		updateFriendshipPosition($_SESSION['user']);//update friendship position
		
		header('location:http://www.perfectcircle.social');
	} else {
		header('location:http://www.perfectcircle.social/welcome.php');
	}
}
?>
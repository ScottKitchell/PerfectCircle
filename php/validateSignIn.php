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
		echo 'true';
	} else {
		echo 'false';
	}
}
?>
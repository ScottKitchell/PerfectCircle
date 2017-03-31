<?
include('functions.php');
//////////////////////////////////////////////////////// Validate Username //////////////////////////////////////////////////////////

if($_SERVER['REQUEST_METHOD'] != 'POST'){
	header('location:http://www.perfectcircle.social/welcome.php');
} else {
	
	$Email = $_POST['email'];
	
	$user = database("SELECT ID FROM USERS WHERE Email = '".$Email."' ");
	if(!empty($user['ID_0'])){
		echo 'false';
	} else {
		echo 'true';
	}
}
?>
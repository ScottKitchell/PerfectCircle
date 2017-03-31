<?php
include('functions.php');
$UserID = safeText($_POST['user']);
$_SESSION['user'] = $UserID;
setcookie('user', $UserID, time() + (86400 * 30), "/"); // 86400 = 1 day
$user = database("SELECT ID, CONCAT(FirstName,' ', LastName) as Name FROM USERS WHERE ID = '".$_SESSION['user']."'");//User Prifile
$html = $user['Name_0'];
echo $html;

//User Position
userPosition($userID);
updateFriendshipPosition($UserID);
//updateAllInterests();
?>
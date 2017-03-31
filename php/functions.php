<?php
session_start();

//////////////////////////////////////////////////////////// Global variables //////////////////////////////////////////////////////////

$root_path = $_SERVER['DOCUMENT_ROOT']; //Root path
$GLOBALS['friends_limit'] = 12;
$GLOBALS['interests_limit'] = 16;

$GLOBALS['like_value'] = 1;
$GLOBALS['likeC_value'] = 0.8;
$GLOBALS['comment_value'] = 2;
$GLOBALS['view_value'] = 0.5;
$GLOBALS['share_value'] = 3;



//////////////////////////////////////////////////////////////// Sign In ///////////////////////////////////////////////////////////////
function signIn(){
	if(!isset($_SESSION['user'])){
		if(isset($_COOKIE['user'])){
			$_SESSION['user'] = $_COOKIE['user'];
			setcookie('user', $_SESSION['user'], time() + (86400 * 30), "/"); // 86400 = 1 day
			userPosition($_SESSION['user']);//update user position (and connections)
			updateFriendshipPosition($_SESSION['user']);//update friendship position
		} else {
			header('location:http://www.perfectcircle.social/welcome.php');
		}
	}
}



//////////////////////////////////////////////////////////////// Database ///////////////////////////////////////////////////////////////
//SQL Database Access function
function database($SQL){
	//DB Details
	$servername = "";
	$username = "";
	$password = "";
	$dbname = "";
	// Create connection
	$connect = mysqli_connect($servername, $username, $password, $dbname);
	// Check connection
	if (!$connect) {
		$values=null;
		$values['numRows']=0;
		echo("<p><b style='color:#cc3434;'>&#x26A0; ERROR:</b> Connection failed <br> <b>&#x27A5;  Details:</b>" . mysqli_connect_error())." </p>";
	}
	
	//If Select Statement
	if(preg_match("/^\b(?i)SELECT\b/",$SQL)){
		// check the sql statement for errors and if errors report them 
		$query = mysqli_query($connect, $SQL); 
		if(!$query) {
			$values=null;
			$values['numRows']=0;
			echo ("<p><b style='color:#cc3434;'>&#x26A0; ERROR:</b> SQL string could not be parsed <br> <b>&#x27A5; String:</b> '".$SQL." ' <br> <b>&#x27A5; Details:</b> ".mysqli_error($connect)." </p>");
		}
		//store all reponces
		$i=0;
		while($row = mysqli_fetch_assoc($query)){
			foreach($row as $colName => $value){
				$values[$colName."_".$i] = $value;
			}
			$i++;
		}
		$values["numRows"] = $i;
		mysqli_free_result($query);
		mysqli_close($connect);
		if($errors==""){
			return $values; //return true
		} else {
			$values=null;
			$values['numRows']=0;
			return $values;
		}
	} else { //Else Not Select 
	//check if is not a multi querey
		if(substr_count($SQL,'INSERT INTO')<=1){
			// Run normal query
			if (mysqli_query($connect, $SQL)) {
				return true;
			} else {
				echo ("<p><b style='color:#cc3434;'> &#x26A0; ERROR:</b> SQL string could not be processed <br> <b>&#x27A5; String:</b> '".$SQL." ' <br> <b>&#x27A5; Details:</b> " . mysqli_error($connect)." </p>");
			}
		} else {
			// Run multi query
			if (mysqli_multi_query($connect, $SQL)) {
				return true;
			} else {
				echo ("<p><b style='color:#cc3434;'>&#x26A0; ERROR:</b> Multi SQL string could not be processed <br> <b>&#x27A5; String:</b> '".$SQL." ' <br> <b>&#x27A5; Details:</b> " . mysqli_error($connect)." </p>");
			}
		}
		mysqli_close($connect);
		if($errors==""){
			return true; //return true
		} else {
			echo $errors;
		}
		
	}
}
//////////////////////////////////////////////////////////// User Position /////////////////////////////////////////////////////////////
//Determine users position
function userPosition($ID){
	$errors="";
	$User = database("SELECT DoB, Gender, Country, Language, DisplayImage, Interests FROM USERS WHERE ID = '".$ID."'");
	//print_r($User);
	//ID
	$ID;
	//Gender
	$Gender = $User['Gender_0'];
	//Age
	$Age = age($User['DOB_0'])/100;
	//Country
	$Country = $User['Country_0'];
	//Language
	$Language = $User['Language_0'];
	//DisplayImage
	if(isset($User['DisplayImage_0'])){$DisplayImg = 1;} else {$DisplayImg = 0;}
	//wrote interests
	$WroteInterests = $User['Interests_0'];
	
	//viewed interests
	$interests = database("SELECT I.Phrase, ABS(uI.IntWeight - I.ViewedAvg) AS Weight FROM VIEWED_INTERESTS AS uI LEFT JOIN INTERESTS AS I ON uI.InterestID = I.Phrase WHERE uI.IntWeight > I.ViewedAvg AND uI.UserID='".$ID."' ORDER BY Weight DESC LIMIT 20");
	for($i=0;$i<$interests['numRows'];$i++){
		$interestsString = $interest['Phrase_'.$i].',';
	}
	$ViewedInterests = rtrim($interestsString,',');
	//liked interests
	$interests = database("SELECT I.Phrase, ABS(uI.IntWeight - I.LikedAvg) AS Weight FROM LIKED_INTERESTS AS uI LEFT JOIN INTERESTS AS I ON uI.InterestID = I.Phrase WHERE uI.IntWeight > I.LikedAvg AND uI.UserID='".$ID."' ORDER BY Weight DESC LIMIT 20");
	for($i=0;$i<$interests['numRows'];$i++){
		$interestsString = $interest['Phrase_'.$i].',';
	}
	$LikedInterests = rtrim($interestsString,',');
	//commented interests
	$interests = database("SELECT I.Phrase, ABS(uI.IntWeight - I.CommentedAvg) AS Weight FROM COMMENTED_INTERESTS AS uI LEFT JOIN INTERESTS AS I ON uI.InterestID = I.Phrase WHERE uI.IntWeight > I.CommentedAvg AND uI.UserID='".$ID."' ORDER BY Weight DESC LIMIT 20");
	for($i=0;$i<$interests['numRows'];$i++){
		$interestsString = $interest['Phrase_'.$i].',';
	}
	$CommentedInterests = rtrim($interestsString,',');
	//shared interests
	$interests = database("SELECT I.Phrase, ABS(uI.IntWeight - I.ViewedAvg) AS Weight FROM VIEWED_INTERESTS AS uI LEFT JOIN INTERESTS AS I ON uI.InterestID = I.Phrase WHERE uI.IntWeight > I.ViewedAvg AND uI.UserID='".$ID."' ORDER BY Weight DESC LIMIT 20");
	for($i=0;$i<$interests['numRows'];$i++){
		$interestsString = $interest['Phrase_'.$i].',';
	}
	$SharedInterests = rtrim($interestsString,',');
	//Warmth
	$warmthCalc = database("SELECT ((COUNT(POSTS.ID)+COUNT(COMMENTS.ID))/COUNT(VIEWS.ID)) AS Warmth FROM POSTS INNER JOIN COMMENTS ON POSTS.UserID=COMMENTS.UserID INNER JOIN VIEWS ON VIEWS.UserID WHERE VIEWS.UserID='".$ID."' GROUP BY VIEWS.UserID");
	
	//SELECT ((COUNT(POSTS.ID)+COUNT(COMMENTS.ID))/COUNT(VIEWS.ID)) AS Warmth FROM POSTS INNER JOIN COMMENTS ON POSTS.UserID=COMMENTS.UserID INNER JOIN VIEWS ON VIEWS.UserID WHERE VIEWS.UserID='c6370d644a4d618052c4768c3758d69e' GROUP BY VIEWS.UserID;
	$warmth = $warthCalc[0];
	//Liveliness
	//$livlinessCalc = database("SELECT AVG(VIEWS.DateTime - COMMENTS.DateTime) AS Liveliness FROM ");
	//Privateness
	$DaysCalc = database("SELECT MAX(DATEDIFF(NOW(),DateTime)) AS Days FROM POSTS WHERE UserID='".$ID."'");
	$Days = max($DaysCalc['Days_0'],90); //max of 90 days
	$postsTime = database("SELECT (COUNT(ID)/".$Days.") AS PostsPerDay FROM POSTS WHERE UserID='".$ID."' AND YEAR(DateTime) >= YEAR(DateTime - INTERVAL ".$Days." DAY) AND MONTH(DateTime) >= MONTH(DateTime - INTERVAL ".$Days." DAY) ");
	//SELECT MAX(DATEDIFF(NOW(),DateTime)) AS Days FROM POSTS WHERE UserID='c6370d644a4d618052c4768c3758d69e';
	//SELECT (COUNT(ID)/10) AS PostsPerDay FROM POSTS WHERE UserID='c6370d644a4d618052c4768c3758d69e' AND YEAR(DateTime) = YEAR(DateTime - INTERVAL 10 DAY) AND MONTH(DateTime) = MONTH(DateTime - INTERVAL 10 DAY);
	
	//Posts total (for use below)
	$numberPosts = database("SELECT COUNT(ID) AS NumPosts FROM POSTS WHERE UserID='".$ID."'");
	$numPosts = $numberPosts['NumPosts_0'];
	$postCalc = database("SELECT COUNT(ID) AS NumPosts, PostType FROM POSTS WHERE UserID='".$ID."' GROUP BY PostType Order BY PostType ASC");
	//Post Text
	$numTextPosts = $postCalc['NumPosts_0']/$numPosts;
	//Post Image
	$numImagePosts = $postCalc['NumPosts_1']/$numPosts;
	//Post Video
	$numVideoPosts = $postCalc['NumPosts_2']/$numPosts;
	//Post Link
	$numLinkPosts = $postCalc['NumPosts_3']/$numPosts;
	//Post Share
	$numSharePosts = ($postCalc['NumPosts_4']+$postCalc['NumPosts_5'])/$numPosts;


	 //Update Database with users position
	 database("UPDATE USER_POSITIONS SET Gender='".$Gender."', Age='".$Age."', Country='".$Country."', Language='".$Language."', DisplayImg='".$DisplayImg."', WroteInterests='".$WroteInterests."',ViewedInterests='".$ViewedInterests."' ,LikedInterests='".$LikedInterests."' ,CommentedInterests='".$CommentedInterests."', SharedInterests='".$SharedInterests."', Warmth='".$warmth."', Liveliness='".$Liveliness."', Privateness='".$Privitness."', PostText='".$numTextPosts."', PostImage='".$numImagePosts."', PostVideo='".$numVideoPosts."', PostArticle='".$numLinkPosts."', PostShare='".$numSharePosts."', Date_Time='".date('Y-m-d H:i:s')."' WHERE UserID='".$ID."'");
	
}



///////////////////////////////////////////////////////// Friendship Position //////////////////////////////////////////////////////////

function updateFriendshipPosition($ID){
	//Get connections IDs from users where they have scored above the average
	$IDs = database("SELECT User2ID AS UserID, Score_Real AS Score FROM CONNECTIONS WHERE User1ID = '".$ID."' AND Score_Real >= (SELECT AVG(Score_Real) FROM CONNECTIONS WHERE User1ID = '".$ID."') ORDER BY Score DESC 
	LIMIT ".$GLOBALS['friends_limit']);
	if($IDs['numRows']<8){
		$IDs = database("SELECT User2ID AS UserID, Score_Ideal AS Score FROM CONNECTIONS WHERE User1ID = '".$ID."' ORDER BY Score DESC 
	LIMIT ".$GLOBALS['friends_limit']);
	}
	
	$allIDs = parseList($IDs,'UserID');
	//Gender
	$values = database("SELECT Gender, COUNT(Gender) AS Count FROM USER_POSITIONS WHERE UserID IN (".$allIDs.") GROUP BY Gender ORDER BY Count DESC LIMIT 1");
	$Gender = $values['Gender_0'];
	$Gender_W = $values['Count_0']/$IDs['numRows'];
	//Age
	$val = 'Age';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$Age = $Circle['Average_0']/100;
	$Age_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Country
	$values = database("SELECT Country, COUNT(Country) AS Count FROM USER_POSITIONS WHERE UserID IN (".$allIDs.") GROUP BY Country ORDER BY Count DESC LIMIT 1");
	$Country = $values['Country_0'];
	$Country_W = $values['Count_0']/$IDs['numRows'];
	//Language
	$values = database("SELECT Language, COUNT(Language) AS Count FROM USER_POSITIONS WHERE UserID IN (".$allIDs.") GROUP BY Language ORDER BY Count DESC LIMIT 1");
	$Language = $values['Language_0'];
	$Language_W = $values['Count_0']/$IDs['numRows'];
	//DisplayImage
	$values= database("SELECT DisplayImg, COUNT(DisplayImg) AS Count FROM USER_POSITIONS WHERE UserID IN (".$allIDs.") GROUP BY DisplayImg ORDER BY Count DESC LIMIT 1");
	$DisplayImg = $values['DisplayImg_0'];
	$DisplayImg_W = $values['Count_0']/$IDs['numRows'];
	//All interests (for use below)
	$InterestsAll = database("SELECT WroteInterests, ViewedInterests, LikedInterests, CommentedInterests, SharedInterests FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	//Wrote Interests
	$values = topInterests($InterestsAll,'WroteInterests',$GLOBALS['interests_limit']/2);
	//print_r($values);
	$WroteInterests = $values['Interest'];
	$WroteInterests_W = $values['Interest_W'];
	//viewed interests
	$values = topInterests($InterestsAll,'ViewedInterests',$GLOBALS['interests_limit']);
	$ViewedInterests = $values['Interest'];
	$ViewedInterests_W = $values['Interest_W'];
	//liked interests
	$values = topInterests($InterestsAll,'LikedInterests',$GLOBALS['interests_limit']);
	$LikedInterests = $values['Interest'];
	$LikedInterests_W = $values['Interest_W'];
	//commented interests
	$values = topInterests($InterestsAll,'CommentedInterests',$GLOBALS['interests_limit']);
	$CommentedInterests = $values['Interest'];
	$CommentedInterests_W = $values['Interest_W'];
	//shared interests
	$values = topInterests($InterestsAll,'SharedInterests',$GLOBALS['interests_limit']);
	$SharedInterests = $values['Interest'];
	$SharedInterests_W = $values['Interest_W'];
	//Warmth
	$val = 'PostText';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$Warmth = $Circle['Average_0'];
	$Warmth_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Liveliness
	$val = 'PostText';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$Warmth = $Circle['Average_0'];
	$Warmth_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Privateness
	$val = 'PostText';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$Privateness = $Circle['Average_0'];
	$Privateness_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	
	//Post Text
	$val = 'PostText';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$PostText = $Circle['Average_0'];
	$PostText_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Post Image
	$val = 'PostImage';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$PostImage = $Circle['Average_0'];
	$PostImage_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Post Video
	$val = 'PostVideo';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$PostVideo = $Circle['Average_0'];
	$PostVideo_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Post Link
	$val = 'PostArticle';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$PostArticle = $Circle['Average_0'];
	$PostArticle_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	//Post Share
	$val = 'PostShare';
	$Circle = database("SELECT AVG(".$val.") AS Average, MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS WHERE UserID IN (".$allIDs.")");
	$Range = database("SELECT MAX(".$val.") AS Maximum, MIN(".$val.") AS Minimum FROM USER_POSITIONS");
	$PostShare = $Circle['Average_0'];
	$PostShare_W = 1 - (($Circle['Maximum_0']-$Circle['Minimum_0']) / ($Range['Maximum_0']-$Range['Minimum_0'])); // 1 - (Users range / Entire range)
	
	//Update Friedship Position
	database("UPDATE FRIENDSHIP_POSITIONS SET 
	Gender='".$Gender."', Gender_W='".$Gender_W."',
	Age='".$Age."', Age_W='".$Age_W."', 
	Country='".$Country."', Country_W='".$Country_W."',
	Language='".$Language."', Language_W='".$Language_W."',
	DisplayImg='".$DisplayImg."', DisplayImg_W='".$DisplayImg_W."',
	WroteInterests='".$WroteInterests."', WroteInterests_W='".$WroteInterests_W."',
	ViewedInterests='".$ViewedInterests."', ViewedInterests_W='".$ViewedInterests_W."',
	LikedInterests='".$LikedInterests."', LikedInterests_W='".$LikedInterests_W."',
	CommentedInterests='".$CommentedInterests."', CommentedInterests_W='".$CommentedInterests_W."',
	SharedInterests='".$SharedInterests."', SharedInterests_W='".$SharedInterests_W."',
	Warmth='".$warmth."', Warmth_W='".$warmth_W."',
	Liveliness='".$Liveliness."', Liveliness_W='".$Liveliness_W."',
	Privateness='".$Privitness."', Privateness_W='".$Privitness_W."',
	PostText='".$PostText."', PostText_W='".$PostText_W."',
	PostImage='".$PostImage."', PostImage_W='".$PostImage_W."',
	PostVideo='".$PostVideo."', PostVideo_W='".$PostVideo_W."',
	PostArticle='".$PostArticle."', PostArticle_W='".$PostArticle_W."',
	PostShare='".$PostShare."', PostShare_W='".$PostShare_W."',
	Date_Time='".date('Y-m-d H:i:s')."' 
	WHERE UserID='".$ID."'");

	//update users connections
	updateConnections($ID);
}




////////////////////////////////////////////////////////// Update Connections ////////////////////////////////////////////////////////////

function updateConnections($ID){
	//Variables
	$errors['total']=0;
	//Get friendship position
	$fPos = database("SELECT * FROM FRIENDSHIP_POSITIONS WHERE UserID='".$ID."'");
	//connection scoring SQL statement
	$score = "(".matchingSQL('Gender',$fPos['Gender_0']).") * ".$fPos['Gender_W_0']." 
	+ ABS('Age' - ".$fPos['Age_0'].") * ".$fPos['Age_W_0']." 
	+ (".matchingSQL('Country',$fPos['Country_0']).") * ".$fPos['Country_W_0']." 
	+ (".matchingSQL('Language',$fPos['Language_0']).") * ".$fPos['Language_W_0']." 
	+ (".matchingSQL('DisplayImg',$fPos['DisplayImg_0']).") * ".$fPos['DisplayImg_W_0']." 
	+ (".matchingSQL('WroteInterests',$fPos['WroteInterests_0']).") * ".$fPos['WroteInterests_W_0']." 
	+ (".matchingSQL('ViewedInterests',$fPos['ViewedInterests_0']).") * ".$fPos['ViewedInterests_W_0']." 
	+ (".matchingSQL('LikedInterests',$fPos['LikedInterests_0']).") * ".$fPos['LikedInterests_W_0']." 
	+ (".matchingSQL('CommentedInterests',$fPos['CommentedInterests_0']).") * ".$fPos['CommentedInterests_W_0']." 
	+ (".matchingSQL('SharedInterests',$fPos['SharedInterests_0']).") * ".$fPos['SharedInterests_W_0']." 
	+ ABS(Warmth - ".$fPos['Warmth_0'].") * ".$fPos['Warmth_W_0']." 
	+ ABS(Liveliness - ".$fPos['Liveliness_0'].") * ".$fPos['Liveliness_W_0']." 
	+ ABS(Privateness - ".$fPos['Privateness_0'].") * ".$fPos['Privateness_W_0']." 
	+ ABS(PostText - ".$fPos['PostText_0'].") * ".$fPos['PostText_W_0']." 
	+ ABS(PostImage - ".$fPos['PostImage_0'].") * ".$fPos['PostImage_W_0']." 
	+ ABS(PostVideo - ".$fPos['PostVideo_0'].") * ".$fPos['PostVideo_W_0']." 
	+ ABS(PostArticle - ".$fPos['PostArticle_0'].") * ".$fPos['PostArticle_W_0']."
	+ ABS(PostShare - ".$fPos['PostShare_0'].") * ".$fPos['PostShare_W_0'];
	//Connections find (based on score)
	$ConnectionID = database("SELECT UserID, (".$score.") AS Score FROM USER_POSITIONS WHERE UserID != '".$ID."' GROUP BY UserID ORDER BY Score ASC LIMIT ". $GLOBALS['friends_limit']*3);
	//Update connections
	$SQL = "";
	for($i=0;$i<$ConnectionID['numRows'];$i++){
		$SQL .= "INSERT INTO CONNECTIONS (User1ID, User2ID, Score_Ideal, Date_Time) 
		VALUES ('".$ID."', '".$ConnectionID['UserID_'.$i]."', '".$ConnectionID['Score_'.$i]."', '".date('Y-m-d H:i:s')."') 
		ON DUPLICATE KEY UPDATE Score_Ideal = '".$ConnectionID['Score_'.$i]."', Date_Time = '".date('Y-m-d H:i:s')."';";
	}
	$SQL = rtrim($SQL,';');
	database($SQL); //multi querey
}

//////////////////////////////////////////////////////////// Get Friends /////////////////////////////////////////////////////////////
 
//Determine closest users to friendship position
function findFriends($ID){
	$Friends = database("SELECT u.ID AS UserID, u.FirstName, u.LastName, u.DisplayImage, (c1.Score_Ideal + c2.Score_Ideal) AS Score 
	FROM CONNECTIONS AS c1 
	LEFT JOIN CONNECTIONS AS c2 ON (c1.User2ID = c2.User1ID AND c2.User2ID = c1.User1ID)
	RIGHT JOIN USERS AS u ON u.ID = c1.User2ID 
	WHERE c1.User1ID = '".$ID."' AND c2.User1ID != '".$ID."' AND u.AllowableScore >= (c1.Score_Ideal + c2.Score_Ideal) 
	ORDER BY Score ASC LIMIT ".$GLOBALS['friends_limit']);
	$max = $Friends['numRows']-1;
	if($Friends['numRows']<8){
		$Friends = findInitialFriends($ID);
	} else {
		database("UPDATE USERS SET AllowableScore='".$MaxScore."' WHERE ID = '".$ID."'");
	}
	return $Friends;
}

//Determine closest users to friendship position FIRST TIME USERS
function findInitialFriends($ID){
	$Friends = database("SELECT u.ID AS UserID, u.FirstName, u.LastName, u.DisplayImage, c1.Score_Ideal AS Score 
	FROM CONNECTIONS AS c1 
	RIGHT JOIN USERS AS u ON u.ID = c1.User2ID 
	WHERE c1.User1ID = '".$ID."'
	ORDER BY Score ASC LIMIT ".$GLOBALS['friends_limit']);
	
	$max = $Friends['numRows']*2;
	database("UPDATE USERS SET AllowableScore='".$MaxScore."' WHERE ID = '".$ID."'");
	return $Friends;
}




///////////////////////////////////////////////////////////// Display Posts ////////////////////////////////////////////////////////////
function displayPost($PostID){
	$post = database("SELECT * FROM POSTS WHERE ID = '".$PostID."'");		
			
		$user = database("SELECT ID, FirstName, LastName, DoB, DisplayImage, Occupation, TalkAbout FROM USERS WHERE ID = '".$post['UserID_0']."'");
		
		//If Shared post or post on another users profile the displayed name area will be different
		if(!empty($post['SharedItemID_0'])){ //If post is a shared post
			$sharedPost = database("SELECT * FROM POSTS WHERE ID = '".$post['SharedItemID_0']."'");
			$post['UserID_0'] = $sharedPost['UserID_0'];
			$post['ID_0'] = $sharedPost['ID_0'];
			$post['Text_0'] = $sharedPost['Text_0'];
			$post['PostType_0'] = $sharedPost['PostType_0'];
			$post['File_0'] = $sharedPost['File_0'];
			$post['Link_0'] = $sharedPost['Link_0'];
			$post['Keywords_0'] = $sharedPost['Keywords_0'];
			$sharedUser = database("SELECT ID, FirstName, LastName FROM USERS WHERE ID = '".$sharedPost['UserID_0']."'");
			$userName = '<a href="yaMate.php?ID='.$user['ID_0'].'">'.$user['FirstName_0'].' '.$user['LastName_0'].'</a> shared <a href="yaMate.php?ID='.$sharedUser['ID_0'].'">'.$sharedUser['FirstName_0'].' '.$sharedUser['LastName_0'].'\'s</a> post';
		} else if(!empty($post['FriendID_0'])){ //If post is on another users profile
			$friendUser = database("SELECT ID, FirstName, LastName FROM USERS WHERE ID = '".$post['FriendID_0']."'");
			$userName = '<a href="yaMate.php?ID='.$user['ID_0'].'">'.$user['FirstName_0'].' '.$user['LastName_0'].'</a> &#9656; <a href="yaMate.php?ID='.$friendUser['ID_0'].'">'.$friendUser['FirstName_0'].' '.$friendUser['LastName_0'].'</a>';
		} else { //If normal post
			$userName = '<a href="yaMate.php?ID='.$user['ID_0'].'">'.$user['FirstName_0'].' '.$user['LastName_0'].'</a>';
		}
		
		if($post['AllowShare_0']=='1'){
			$shareClass = "btn shareBtn";	
		} else {
			$shareClass = "btn-disabled";	
			
		}
		$countLikes = database("SELECT COUNT(ID) AS Likes FROM LIKES WHERE PostID = '".$post['ID_0']."'");
		$countComments = database("SELECT COUNT(ID) AS Comments FROM COMMENTS WHERE PostID = '".$PostID."'");
		$countShares = database("SELECT COUNT(ID) AS Shares FROM POSTS WHERE SharedItemID = '".$post['ID_0']."'");
		
		$comments = database("SELECT c.ID AS CommentID, c.UserID, u.FirstName, u.LastName, u.DisplayImage, c.Comment, COUNT(l.ID) AS Likes, c.DateTime 
		FROM COMMENTS AS c 
		LEFT JOIN USERS AS u ON c.UserID = u.ID 
		LEFT JOIN LIKESC AS l ON c.ID = l.CommentID
		WHERE c.PostID = '".$PostID."' GROUP BY c.ID ORDER BY c.DateTime ASC");
		//print_r($comments);
		
		$html = ('<section class="col-xs-0 col-md-4"><!--userDisplay-->
					  <div class="DP-bg">
						  <img src2="users/'.$user['ID_0'].'/'.$user['DisplayImage_0'].'" class="desktop-only">
						  <p><span><b><a href="yaMate.php?ID='.$user['ID_0'].'">'.$user['FirstName_0'].' '.$user['LastName_0'].'</a></b><br>'.$user['Occupation_0'].'<br> Talk to me about '.$user['TalkAbout_0'].'</span></p>
					  </div>
				  </section><!--END userDisplay-->
				  
				  <section class="panel col-xs-12 col-md-8"><!--PostDisplay-->
					  <div class="row hide-md">
						  <div class="col-xs-6 col-md-0 userBox">
							  <div class="DP-sm"><img src="users/'.$user['ID_0'].'/thumb_'.$user['DisplayImage_0'].'"></div>
						  </div>
						  <div class="col-xs-6 detailsBox">
							  <span class="postuName"><a href="yaMate.php?ID='.$user['ID_0'].'">'.$user['FirstName_0'].' '.$user['LastName_0'].'</a></span>
							  <span class="postuDetails">'.$user['Occupation_0'].'</span>
							  <span class="postuDetails">Talk to me about '.$user['TalkAbout_0'].'</b></span>
						  </div>
					  </div>
					  <div class="row">
						  <div class="col-xs-12 postBox">
						   	  <span class="postuName show-md">'.$userName.'</span>
							  <span class="postTime">'.timeAgo($post['DateTime_0']).'</span>
							  <span class="postText">'.symbols($post['Text_0']).'</span>
						  </div>
					  </div>');
		switch($post['PostType_0']){
			case '1': //Text Post
				$html .= ('	<div class="row">
								<div class="col-xs-6">
									<a class="btn likeBtn" id="'.$post['ID_0'].'_lactionBtn" name="'.$post['ID_0'].'"><span class="icon like"></span> <span id="'.$post['ID_0'].'_likes">'.$countLikes['Likes_0'].' like</span></a>
								</div>
								<div class="col-xs-6">
									<a class="'.$shareClass.'" id="'.$post['ID_0'].'_sactionBtn" name="'.$post['ID_0'].'"><span class="icon sharepost"></span> <span id="'.$post['ID_0'].'_shares">'.$countShares['Shares_0'].' share</a>
								</div>
							</div>
							');
				break;
			case '2': //Article link Post
				require_once('OpenGraph.php');
				$graph = OpenGraph::fetch($post['Link_0']);
				foreach ($graph as $key => $value) {$OGdata[$key] = $value;}
				$html .= ('	<div class="row">
								<div class="col-xs-12 extraBox">
									<div class="articleBox"><div>
									<p class="headline"><a target="_blank" href="'.$post['Link_0'].'">'.$OGdata['title'].'</a></p>
									<p class="description">'.$OGdata['description'].'</p>
									<a class="img" target="_blank" href="'.$OGdata['url'].'"><img  src="'.$OGdata['image'].'"></img></a>
									<p class="info">via '.$OGdata['site_name'].'</p>
									</div></div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-6">
									<a class="btn likeBtn" id="'.$post['ID_0'].'_lactionBtn" name="'.$post['ID_0'].'"><span class="icon like"></span> <span id="'.$post['ID_0'].'_likes">'.$countLikes['Likes_0'].' like</span></a>
								</div>
								<div class="col-xs-6">
									<a class="'.$shareClass.'" id="'.$post['ID_0'].'_sactionBtn" name="'.$post['ID_0'].'"><span class="icon sharepost"></span> <span id="'.$post['ID_0'].'_shares">'.$countShares['Shares_0'].' share</a>
								</div>
							</div>');
				break;
			case '3': //Image upload Post
				$html .= ('	<div class="row">
								<div class="col-xs-12 extraBox">
									<img src="users/'.$post['UserID_0'].'/'.$post['File_0'].'">
								</div>
							</div>
							
							<div class="row">
								<div class="col-xs-6">
									<a class="btn likeBtn" id="'.$post['ID_0'].'_lactionBtn" name="'.$post['ID_0'].'"><span class="icon like"></span> <span id="'.$post['ID_0'].'_likes">'.$countLikes['Likes_0'].' like</span></a>
								</div>
								<div class="col-xs-6">
									<a class="'.$shareClass.'" id="'.$post['ID_0'].'_sactionBtn" name="'.$post['ID_0'].'"><span class="icon sharepost"></span> <span id="'.$post['ID_0'].'_shares">'.$countShares['Shares_0'].' share</a>
								</div>
							</div>');
				break;
				case '4': //Image link Post
				require_once('OpenGraph.php');
				$graph = OpenGraph::fetch($post['Link_0']);
				foreach ($graph as $key => $value) {$OGdata[$key] = $value;}
				$html .= ('<div class="row">
								<div class="col-xs-12 extraBox">
									<img src="'.$post['Link_0'].'">
									<p class="info">via '.$OGdata['site_name'].'</p>
								</div>
							</div>
							
							<div class="row">
								<div class="col-xs-6">
									<a class="btn likeBtn" id="'.$post['ID_0'].'_lactionBtn" name="'.$post['ID_0'].'"><span class="icon like"></span> <span id="'.$post['ID_0'].'_likes">'.$countLikes['Likes_0'].' like</span></a>
								</div>
								<div class="col-xs-6">
									<a class="'.$shareClass.'" id="'.$post['ID_0'].'_sactionBtn" name="'.$post['ID_0'].'"><span class="icon sharepost"></span> <span id="'.$post['ID_0'].'_shares">'.$countShares['Shares_0'].' share</a>
								</div>
							</div>');
				break;
				case '5': //Video link Post
				require_once('OpenGraph.php');
				$graph = OpenGraph::fetch($post['Link_0']);
				foreach ($graph as $key => $value) {$OGdata[$key] = $value;}
				$html .= ('	<div class="row">
								<div class="col-xs-12 extraBox">
									
								</div>
							</div>
							
							<div class="row">
								<div class="col-xs-6">
									<a class="btn likeBtn" id="'.$post['ID_0'].'_lactionBtn" name="'.$post['ID_0'].'"><span class="icon like"></span> <span id="'.$post['ID_0'].'_likes">'.$countLikes['Likes_0'].' like</span></a>
								</div>
								<div class="col-xs-6">
									<a class="'.$shareClass.'" id="'.$post['ID_0'].'_sactionBtn" name="'.$post['ID_0'].'"><span class="icon sharepost"></span> <span id="'.$post['ID_0'].'_shares">'.$countShares['Shares_0'].' share</a>
								</div>
							</div>');
				break;
		}
		
		
		//Comments
		$html .= '<div class="col-xs-12 commentsBox" id="'.$PostID.'_commentsBox">';
		for($i=0; $i < $comments["numRows"]; $i++){
			$html .= ('<div class="comment row">
                    	<div class="col-xs-3 col-md-3"><div class="DP-xs"><img src="users/'.$comments['UserID_'.$i].'/thumb_'.$comments["DisplayImage_".$i].'"></div></div>
                        
						<div class="col-xs-9 col-md-9"><span class="commentName"><a href="yaMate.php?ID='.$comments['UserID_'.$i].'">'.$comments["FirstName_".$i].' '.$comments["LastName_".$i].'</a></span>
                        	<span class="commentTime">'.timeAgo($comments["DateTime_".$i]).'</span>
                            <span class="commentText">'.symbols($comments["Comment_".$i]).'</span>
                            <span class="commentLikes"><a href="" class="likeCBtn" name="'.$comments["CommentID_".$i].'" id="'.$comments["CommentID_".$i].'_lcactionBtn">like</a> - <span id="'.$comments["CommentID_".$i].'_likesC">'.$comments['Likes_'.$i].'</span> others liked this</span>
                        </div>
                    </div>');
		}
		$html .= ('</div>');
		
		$html .= ('<div class="row">
                    <form class="commentForm" method="post" action="php/comment.php" name="'.$PostID.'">
						<div class="col-xs-9"><input type="text" name="comment" class="commentInput" id="'.$PostID.'_comment" placeholder="Talk about it..."  autocomplete="off"></div>
						<div class="col-xs-3"><input class="btn" type="submit" value="comment"></div>
						<input type="hidden" name="user" id="'.$PostID.'_user" value="'.$_SESSION['user'].'">
						<input type="hidden" name="laction" id="'.$PostID.'_laction" value="like">
						<input type="hidden" name="caction" id="'.$PostID.'_caction" value="comment">
                    </form>
                </div>');
		$html .= ('</section><!--END PostDisplay-->');
	return $html;
}
///////////////////////////////////////////////////////////// Notifications ///////////////////////////////////////////////////////////
function notifications($ID){
	$notifications = database("SELECT n.ID, n.FriendID, CONCAT(u.FirstName,' ', u.LastName) AS FriendName, u.DisplayImage, n.AboutType, n.AboutID, n.Preview, n.Date_Time 
	FROM NOTIFICATIONS AS n LEFT JOIN USERS AS u ON n.FriendID = u.ID
	WHERE UserID = '".$ID."' AND n.Seen = '0' ORDER BY n.Date_Time DESC");
	return $notifications;
}

function notificationDisplay($nID){
	$post = database("SELECT AboutID FROM NOTIFICATIONS WHERE ID ='".$nID."'");
	database("UPDATE NOTIFICATIONS SET Seen='1' WHERE ID='".$nID."'");
	$html = '<div class="row"><div class="col-xs-0 col-md-4"></div><div class="col-xs-12 col-md-8"><div class="sectionH1">NOTIFICATIONS</div></div>';
	$html .= displayPost($post['AboutID_0']);
	$html .= '</div>';
	return $html;
}

/////////////////////////////////////////////////////////// Update Interests /////////////////////////////////////////////////////////
function updateUserInterests($ID, $Type, $InterestsString){
	//Variables
	$TYPE = strtoupper($Type);
	$Sum = database("SELECT SUM(IntCount) AS num FROM ".$TYPE."_INTERESTS WHERE UserID = '".$ID."'");
	if($Sum['num_0']==""){$Sum['num_0']=1;}
	//Update Selected interest
	$values = "";
	$interests = wordsArray($InterestsString);
	foreach($interests as $interest){
		$values .= "('".md5($UserID.$interest)."','".$ID."','".$interest."',1, 1/".$Sum['num_0']."),";
	}
	$values = rtrim($values,",");
	$SQL= "INSERT INTO ".$TYPE."_INTERESTS (ID, UserID, InterestID, IntCount, IntWeight)
		VALUES ".$values."
		ON DUPLICATE KEY UPDATE
		IntCount = IntCount + 1 , 
		IntWeight = IntCount / ".$Sum['num_0']."";
	database($SQL); //Insert or Update $TYPE_Interests in DB
	
	//Update all other interests
	database("UPDATE ".$TYPE."_INTERESTS SET IntWeight = IntCount / ".$Sum['num_0']." WHERE UserID='".$ID."'");
}




///////////////////////////////////////////////////////////// Misc Functions ///////////////////////////////////////////////////////////

//Parse Regex 
function parseRegex($text){
	$text = str_replace(","," ",$text);
	$words = explode(" ",$text);
	foreach($words as $word){
		if(!preg_match("\b(?i)(the|be|to|of|and|a|in|that|have|I|it|for|not|on|with|he|as|you|do|at)\b",$word)){ //Excluding most common words
			$expression = $expression.$word."|";
		}
	}
	$expression = rtrim($expression, "|");
	$expression = "(?i)\b(".$expression.")\b";
	return $expression;
}

//String of words to an array
function wordsArray($text){
	$text = str_replace(","," ",$text);
	$text = preg_replace("/(?![=$'€%-])\p{P}/u", "", $text);
	$words = explode(" ",$text);
	foreach($words as $word){
		if(!preg_match("\b(?i)(the|be|to|of|and|a|in|that|have|I|it|for|not|on|with|he|as|you|do|at)\b",$word)){ //Excluding most common words
			$wordsArray[] = trim($word);
		}
	}
	return $wordsArray;
}

//Create an ID 
//Create ID values of length $len (max length: 32 characters)
function newID($len){
	$ID = md5(microtime().rand(0,1000));
	$ID = substr($ID,0,$len);
	return $ID;
}

//parse to comma seperated string e.g. 'value 1','value 2','value 3',...
function parseList($array,$colName){
	$string = "";
	if(!empty($colName)){
		$numRows = $array['numRows'];
		for($i=0; $i<$numRows; $i++){
			$value = $array[$colName.'_'.$i];
			if($string != ""){$string = $string.",'".$value."'";} else {$string = "'".$value."'";}
		}
	} else {
		foreach($array as $value){
			if($string != ""){$string = $string.",'".$value."'";} else {$string = "'".$value."'";}
		}
	}
	return $string;
}



function safeText($text){
	$text = trim($text);
	$text = stripslashes($text);
	$text = htmlspecialchars($text, ENT_QUOTES);
	return $text;
}

function symbols($text){
	$text = str_ireplace(array(" :)"," :-)"),' <span class="emoticon smiley"></span>',$text); //smiley face
	$text = str_ireplace(array(" :D"," :-D"),' <span class="emoticon verySmiley"></span>',$text); //very smiley face
	$text = str_ireplace(array(" >:("," >:-(","&gt;:(","&gt;:-("),' <span class="emoticon angry"></span>',$text); //angry face
	$text = str_ireplace(array(" >:o"," >:-o","&gt;:o","&gt;-o",">:("),' <span class="emoticon shouting"></span>',$text); //shouting face
	$text = str_ireplace(array(" :("," :-("),' <span class="emoticon sad"></span>',$text); //sad face
	$text = str_ireplace(array(" ;)"," ;-)","&semi;)","&semi;-)"),' <span class="emoticon winky"></span>',$text); //winky face
	$text = str_ireplace(array(" :P"," :-P"),' <span class="emoticon cheeky"></span>',$text); //cheeky face
	$text = str_ireplace(array(" ;p"," ;-p","&semi;p","&semi;-p"),' <span class="emoticon cheekyWinky"></span>',$text); //cheeky-winky face
	$text = str_ireplace(array(" :|"," :-|"),' <span class="emoticon blank"></span>',$text); //blank face
	$text = str_ireplace(array(" :/"," :-/"),' <span class="emoticon confused"></span>',$text); //blank face
	$text = str_ireplace(array(" 8)"," 8-)","B)","B-)","B|","B-|"),' <span class="emoticon cool"></span>',$text); //cheeky face
	$text = str_ireplace(array(" :s"," :-s"),' <span class="emoticon unsure"></span>',$text); //unsure face
	$text = str_ireplace(array(" :o"," :-o"),' <span class="emoticon surprised"></span>',$text); //surprised face
	$text = str_ireplace(array(" <3"," &lt;3"),' <span class="emoticon hearts"></span>',$text); //heart
	$text = str_ireplace(array(" :'("," :'-(",":&#039;(",":&#039;-("),' <span class="emoticon crying"></span>',$text); //crying
	$text = str_ireplace(array(" x_x"),' <span class="emoticon dead"></span>',$text); //dead
	$text = str_ireplace(array("\n"),'<br />',$text); //break
	$text = str_ireplace(array("\r"),'<br />',$text); //break
	return $text;
}

function storeConnections($Friends,$ID){
	database("DELETE FROM CONNECTIONS WHERE User1ID='".$ID);
	for($i=0;$i<$Friends['numRows'];$i++){
		$SQlValues = $SQlValues."('".newID(32)."','".$ID."','".$Friends['UserID_'.$i]."','".$Friends['UserID_'.$i]."','".date('Y-m-d H:i:s')."'),";
	}
	$SQlValues = rtrim($SQlValues, ",");
	database("INSERT INTO CONNECTIONS (ID,User1ID,User2ID,Score,Date_Time) VALUES ".$SQlValues);
}

//Update all Interests at once
function updateAllInterests(){
	$IDs = database("SELECT InterestID FROM WROTE_INTERESTS UNION SELECT InterestID FROM VIEWED_INTERESTS UNION SELECT InterestID FROM LIKED_INTERESTS UNION SELECT InterestID FROM COMMENTED_INTERESTS UNION SELECT InterestID FROM SHARED_INTERESTS");
	for($i=0;$i<$IDs['numRows'];$i++){
		if($IDs['InterestID_'.$i]!=""){
			$WroteAvg = database("SELECT AVG(IntWeight) AS Weight FROM WROTE_INTERESTS WHERE InterestID = '".$IDs['InterestID_'.$i]."'");
			if($WroteAvg['Weight_0']==""){$WroteAvg['Weight_0'] = 0;}
			$ViewedAvg = database("SELECT AVG(IntWeight) AS Weight FROM VIEWED_INTERESTS WHERE InterestID = '".$IDs['InterestID_'.$i]."'");
			if($ViewedAvg['Weight_0']==""){$ViewedAvg['Weight_0'] = 0;}
			$LikedAvg = database("SELECT AVG(IntWeight) AS Weight FROM LIKED_INTERESTS WHERE InterestID = '".$IDs['InterestID_'.$i]."'");
			if($LikeedAvg['Weight_0']==""){$LikedAvg['Weight_0']=0;}
			$CommentedAvg = database("SELECT AVG(IntWeight) AS Weight FROM COMMENTED_INTERESTS WHERE InterestID = '".$IDs['InterestID_'.$i]."'");
			if($CommentedAvg['Weight_0']==""){$CommentedAvg['Weight_0']=0;}
			$SharedAvg  = database("SELECT AVG(IntWeight) AS Weight FROM SHARED_INTERESTS WHERE InterestID = '".$IDs['InterestID_'.$i]."'");
			if($SharedAvg['Weight_0']==""){$SharedAvg['Weight_0'] = 0;}
			database("REPLACE INTO INTERESTS (Phrase,WroteAvg,ViewedAvg,LikedAvg,CommentedAvg,SharedAvg) VALUES ('".$IDs['InterestID_'.$i]."',".$WroteAvg['Weight_0'].",".$ViewedAvg['Weight_0'].",".$LikedAvg['Weight_0'].",".$CommentedAvg['Weight_0'].",".$SharedAvg['Weight_0'].")");
		}
	}
}





//create if matching sql staement function
	function matchingSQL($col,$value){
		if($value!=""){
			$phrases = explode(",",$value);
			$string = "0";
			foreach($phrases as $phrase){
				$string = $string." + CASE WHEN LOCATE('".$phrase."',".$col.")>0 THEN 0 ELSE 1 END";
			}
		} else {
			$string = 0;
		}
		return $string;
	}

//Top Interests function
	function topInterests($InterestsAll,$type,$numInterests){
		$total =0;
		$totalTop=0;
		for($i=0; $i<$InterestsAll['numRows']; $i++){
			$InterestString .= $InterestsAll[$type.'_'.$i].',';
		}
		$InterestString = trim($InterestString,",");
		$InterestString = str_replace(" ","",$InterestString);
		$values = explode(',',$InterestString);
		foreach($values as $value){
			if(isset($Score[$value])){$Score[$value]++;}else{$Score[$value]=1;}
			$total++;
		}
		arsort($Score,1);
		//print_r($Score);
		$Interests = array_slice($Score,0,$numInterests-1,true);
		foreach($Interests as $value=>$count){
			$String .= $value.',';
			$totalTop += $count;
		}
		$String = rtrim($InterestString,",");
		$String_W = $totalTop / $total;
		$array = array("Interest"=>$String,"Interest_W"=>$String_W);
		return $array;
	}

//website address from address
function siteURL($fullURL){
	$siteURL = substr($fullURL,0,strpos($fullURL, "/", 8));
	return $siteURL;
}
function timestamp($date_time){
	if($date_time=='NOW'){
		
	} else {
		
		
	}
	return $timestamp;
}

function age($date){
	$Age = floor((time() - strtotime($date)) / 31556926);
	return $Age;
}

//Time since post was made
function timeAgo($datetime)
{
    $time_ago = time() - strtotime($datetime);

    if ($time_ago < 15)
    {
        return 'just now';
    } else if ($time_ago < 60*45){
		 return round($time_ago/60).' minutes ago';
	} else if ($time_ago < 60*60*24){
		 return round($time_ago/(60*60)).' hours ago';
	} else if ($time_ago < 60*45*24*6){
		 return round($time_ago/(60*60*24)).' days ago';
	} else {
		return date("j M Y",strtotime($datetime));
	}
}

function tempUpdate($ID){
	if($ID=='all'){
	$allUserss = database("SELECT ID FROM USERS");//All users
	for($x=0;$x<$allUserss['numRows'];$x++){
		
		$allUsers = database("SELECT * FROM USER_POSITIONS WHERE UserID = '".$allUserss['ID_'.$x]."'");
		$i=0;
		//database("UPDATE USER_POSITIONS SET Gender='".$allUsers['Gender_'.$i]."', Age='".$allUsers['Age_'.$i]."', Country='".$allUsers['Country_'.$i]."', Language='".$allUsers['Language_'.$i]."', DisplayImg='".$allUsers['DisplayImg_'.$i]."', WroteInterests='".$allUsers['WroteInterests_'.$i]."',ViewedInterests='".$allUsers['ViewedInterests_'.$i]."' ,LikedInterests='".$allUsers['LikedInterests_'.$i]."' ,CommentedInterests='".$allUsers['CommentedInterests_'.$i]."', SharedInterests='".$allUsers['SharedInterests_'.$i]."', Warmth='".$allUsers['warmth_'.$i]."', Liveliness='".$allUsers['Liveliness_'.$i]."', Privateness='".$allUsers['Privitness_'.$i]."', PostText='".$allUsers['numTextPosts_'.$i]."', PostImage='".$allUsers['numImagePosts_'.$i]."', PostVideo='".$allUsers['numVideoPosts_'.$i]."', PostArticle='".$allUsers['numLinkPosts_'.$i]."', PostShare='".$allUsers['numSharePosts_'.$i]."', Date_Time=NOW() WHERE UserID='".$allUserss['ID_'.$x]."'");
		database("UPDATE FRIENDSHIP_POSITIONS SET Gender='".$allUsers['Gender_'.$i]."', Age='".$allUsers['Age_'.$i]."', Country='".$allUsers['Country_'.$i]."', Language='".$allUsers['Language_'.$i]."', DisplayImg='".$allUsers['DisplayImg_'.$i]."', WroteInterests='".$allUsers['WroteInterests_'.$i]."',ViewedInterests='".$allUsers['ViewedInterests_'.$i]."' ,LikedInterests='".$allUsers['LikedInterests_'.$i]."' ,CommentedInterests='".$allUsers['CommentedInterests_'.$i]."', SharedInterests='".$allUsers['SharedInterests_'.$i]."', Warmth='".$allUsers['warmth_'.$i]."', Liveliness='".$allUsers['Liveliness_'.$i]."', Privateness='".$allUsers['Privitness_'.$i]."', PostText='".$allUsers['numTextPosts_'.$i]."', PostImage='".$allUsers['numImagePosts_'.$i]."', PostVideo='".$allUsers['numVideoPosts_'.$i]."', PostArticle='".$allUsers['numLinkPosts_'.$i]."', PostShare='".$allUsers['numSharePosts_'.$i]."', Date_Time=NOW() WHERE UserID='".$allUserss['ID_'.$x]."'");
		
	}
	} else {
		$allUsers = database("SELECT * FROM USER_POSITIONS WHERE UserID = '".$ID."'");
		$i = 0; $x =0;
		//database("UPDATE USER_POSITIONS SET Gender='".$allUsers['Gender_'.$i]."', Age='".$allUsers['Age_'.$i]."', Country='".$allUsers['Country_'.$i]."', Language='".$allUsers['Language_'.$i]."', DisplayImg='".$allUsers['DisplayImg_'.$i]."', WroteInterests='".$allUsers['WroteInterests_'.$i]."',ViewedInterests='".$allUsers['ViewedInterests_'.$i]."' ,LikedInterests='".$allUsers['LikedInterests_'.$i]."' ,CommentedInterests='".$allUsers['CommentedInterests_'.$i]."', SharedInterests='".$allUsers['SharedInterests_'.$i]."', Warmth='".$allUsers['warmth_'.$i]."', Liveliness='".$allUsers['Liveliness_'.$i]."', Privateness='".$allUsers['Privitness_'.$i]."', PostText='".$allUsers['numTextPosts_'.$i]."', PostImage='".$allUsers['numImagePosts_'.$i]."', PostVideo='".$allUsers['numVideoPosts_'.$i]."', PostArticle='".$allUsers['numLinkPosts_'.$i]."', PostShare='".$allUsers['numSharePosts_'.$i]."', Date_Time=NOW() WHERE UserID='".$allUserss['ID_'.$x]."'");
		database("UPDATE FRIENDSHIP_POSITIONS SET Gender='".$allUsers['Gender_'.$i]."', Age='".$allUsers['Age_'.$i]."', Country='".$allUsers['Country_'.$i]."', Language='".$allUsers['Language_'.$i]."', DisplayImg='".$allUsers['DisplayImg_'.$i]."', WroteInterests='".$allUsers['WroteInterests_'.$i]."',ViewedInterests='".$allUsers['ViewedInterests_'.$i]."' ,LikedInterests='".$allUsers['LikedInterests_'.$i]."' ,CommentedInterests='".$allUsers['CommentedInterests_'.$i]."', SharedInterests='".$allUsers['SharedInterests_'.$i]."', Warmth='".$allUsers['warmth_'.$i]."', Liveliness='".$allUsers['Liveliness_'.$i]."', Privateness='".$allUsers['Privitness_'.$i]."', PostText='".$allUsers['numTextPosts_'.$i]."', PostImage='".$allUsers['numImagePosts_'.$i]."', PostVideo='".$allUsers['numVideoPosts_'.$i]."', PostArticle='".$allUsers['numLinkPosts_'.$i]."', PostShare='".$allUsers['numSharePosts_'.$i]."', Date_Time='".date('Y-m-d H:i:s')."' WHERE UserID='".$allUserss['ID_'.$x]."'");
	}
}


#####  This function will proportionally resize image ##### 
function normal_resize_image($source, $destination, $image_type, $max_size, $image_width, $image_height, $quality){
     
     if($image_width <= 0 || $image_height <= 0){return false;} //return false if nothing to resize
     
     //do not resize if image is smaller than max size
     if($image_width <= $max_size && $image_height <= $max_size){
         if(save_image($source, $destination, $image_type, $quality)){
             return true;
         }
     }
     
     //Construct a proportional size of new image
     $image_scale    = min($max_size/$image_width, $max_size/$image_height);
     $new_width      = ceil($image_scale * $image_width);
     $new_height     = ceil($image_scale * $image_height);
     
     $new_canvas     = imagecreatetruecolor( $new_width, $new_height ); //Create a new true color image
     
     //Copy and resize part of an image with resampling
     if(imagecopyresampled($new_canvas, $source, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height)){
         save_image($new_canvas, $destination, $image_type, $quality); //save resized image
     }

     return true;
}

##### This function corps image to create exact square, no matter what its original size! ######
function crop_image_square($source, $destination, $image_type, $square_size, $image_width, $image_height, $quality){
     if($image_width <= 0 || $image_height <= 0){return false;} //return false if nothing to resize
     
     if( $image_width > $image_height )
     {
         $y_offset = 0;
         $x_offset = ($image_width - $image_height) / 2;
         $s_size     = $image_width - ($x_offset * 2);
     }else{
         $x_offset = 0;
         $y_offset = ($image_height - $image_width) / 2;
         $s_size = $image_height - ($y_offset * 2);
     }
     $new_canvas = imagecreatetruecolor( $square_size, $square_size); //Create a new true color image
     
     //Copy and resize part of an image with resampling
     if(imagecopyresampled($new_canvas, $source, 0, 0, $x_offset, $y_offset, $square_size, $square_size, $s_size, $s_size)){
         save_image($new_canvas, $destination, $image_type, $quality);
     }

     return true;
}

##### Saves image resource to file ##### 
function save_image($source, $destination, $image_type, $quality){
     switch(strtolower($image_type)){//determine mime type
         case 'image/png': 
             imagepng($source, $destination); return true; //save png file
             break;
         case 'image/gif': 
             imagegif($source, $destination); return true; //save gif file
             break;          
         case 'image/jpeg': case 'image/pjpeg': case 'image/jpg':
             imagejpeg($source, $destination, $quality); return true; //save jpeg file
             break;
         default: return false;
     }
}

?>
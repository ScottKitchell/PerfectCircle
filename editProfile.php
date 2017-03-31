<?php
	include('php/functions.php');
	signIn();
	
	//IF post then update profile
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		$ID = safeText($_POST['UserID']);
		$FirstName = safeText($_POST['FirstName']);
		$LastName = safeText($_POST['LastName']);
		$Email = safeText($_POST['Email']);
		$DoB = safeText($_POST['DoB']);
		$Age = age($DoB);
		$Gender = safeText($_POST['Gender']);
		$Country = safeText($_POST['Country']);
		$Language = safeText($_POST['Language']);
		$DisplayImg = safeText($_POST['DisplayImage']);
		$Relationship = safeText($_POST['Relationship']);
		$Occupation = safeText($_POST['Occupation']);
		$WroteInterests = safeText($_POST['Interests']);
		$TalkAbout = safeText($_POST['TalkAbout']);
		
		database("UPDATE USERS SET FirstName='".$FirstName."', LastName='".$LastName."', Email='".$Email."', DoB='".$DoB."', Gender='".$Gender."', Country='".$Country."', Language='".$Language."', Relationship='".$Relationship."', Interests='".$WroteInterests."', Occupation='".$Occupation."', TalkAbout='".$TalkAbout."' 
		WHERE ID='".$ID."'");
		
		$saved = "<span style='display:block; color:green;'>Profile updated successfully!</span>";
	}
	
	//User Profile
	$user = database("SELECT ID, FirstName, LastName, Email, DoB, Gender, Country, Language, DisplayImage, Relationship, Interests, Occupation, TalkAbout FROM USERS WHERE ID = '".$_SESSION['user']."'");//User Profile
	
	if($user['FirstName_0']==""){
		$newUser = 'true';
	} else {
		$newUser = 'false';
	}

	
	//BETTY
	//userPosition($user['ID_0']);//update user position (and connections)
	//updateFriendshipPosition($user['ID_0']);//update friendship position
	$friends = findFriends($user['ID_0']); // get friends
	$peopleList = parseList($friends,'UserID'); //list friends
	
	
	//notifications
	$notifications = notifications($user['ID_0']);
	if($notifications['numRows']>0){$notifications['display']= '<div id="notifications">'.$notifications['numRows'].'</div>';} else {$notifications['display'] ='';}
	
	if($_POST['newUser']=='true'){
		
		//Update Friedship Position
		database("UPDATE FRIENDSHIP_POSITIONS SET 
		Gender='".$Gender."', Gender_W='0',
		Age='".($Age/100)."', Age_W='0.8', 
		Country='".$Country."', Country_W='0.4',
		Language='".$Language."', Language_W='1',
		DisplayImg='".$DisplayImg."', DisplayImg_W='0.2',
		WroteInterests='".$WroteInterests."', WroteInterests_W='1',
		ViewedInterests='".$ViewedInterests."', ViewedInterests_W='0',
		LikedInterests='".$LikedInterests."', LikedInterests_W='0',
		CommentedInterests='".$CommentedInterests."', CommentedInterests_W='0',
		SharedInterests='".$SharedInterests."', SharedInterests_W='0',
		Warmth='', Warmth_W='0',
		Liveliness='', Liveliness_W='0',
		Privateness='', Privateness_W='0',
		PostText='', PostText_W='0',
		PostImage='', PostImage_W='0',
		PostVideo='', PostVideo_W='0',
		PostArticle='', PostArticle_W='0',
		PostShare='', PostShare_W='0',
		Date_Time=NOW() 
		WHERE UserID='".$user['ID_0']."'");
		
		//BETTY
		userPosition($user['ID_0']);//update user position (and connections)
		updateConnections($user['ID_0']);
		$friends = findInitialFriends($user['ID_0']); // get initial friends
		
		for($i=0; $i<$friends['numRows']; $i++){
			//updateConnections($friends['UserID_'.$i]); // update all connection's friends
		}
		
		//First Post
		//Save Post to DB
		database("INSERT INTO POSTS (ID, UserID, PostType, Text, keywords, DateTime) VALUES ('".newID(32)."','".$ID."','1','I&#039;m new on here, hi everyone!', 'new-friend', NOW())");
		
	}
?>

<!doctype html>
<html prefix="og: http://ogp.me/ns#">
<head>
<!--  meta data  -->
<meta charset="utf-8">
<title>Edit - <? echo $user['FirstName_0'].' '.$user['LastName_0']; ?></title>
<meta name="description" content="Because not everyone is a loud-mouth.">
<meta name="keywords" content="social network, friends, introvert, new, get out">
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!----- Open Graph ----->
<meta property="og:title" content="Perfect Circle - A Social Network Designed for Intorverts" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Perfect Circle" />
<meta property="og:url" content="http://www.perfectcircle.social/welcome.php" />
<meta property="og:image" content="http://www.perfectcircle.social/images/perfect_circle.jpg" />
<meta property="og:description" content="Because not everyone is a loud-mouth." />
<!--  jQuery  -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!--  Hammer  -->
<!--<script src="js/hammer.min.js"></script>-->
<!--  Bootstrap  -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<!--  stylesheet  -->
<link rel="stylesheet" type="text/css" href="css/fluid.css" />
<!--  scripts  -->
<script src="js/textareaAutoResize.js"></script><!--textarea resize-->
<script src="js/jquery.form.js"></script> <!--AJAX form submit helper-->
<script src="js/functions.js"></script><!--general functions-->
<!--DiscussionBox auto resize-->
<script>$(document).ready(function(){$("#discussion_text").autoResize();});</script> <!--Discussion auto resize-->
</head>

<body>
<nav class="navBox">
	<menu id="mainMenu" class="topMenu">
        <div class="container-fluid">
            <div class="row profile">
                <div class="col-xs-4">
                    <div class="DP-sm"><img src="users/<? echo $user['ID_0'].'/thumb_'.$user['DisplayImage_0']; ?>"></div>
                </div>
                <div class="col-xs-8">
                	<? echo '<b>'.$user['FirstName_0'].' '.$user['LastName_0'].'</b><br>Talk to me about '.$user['TalkAbout_0']; ?>
                </div>
            </div>
            <div class="row"><ul>
                <li><a href="http://www.perfectcircle.social">Social Feed</a></li>
                <li><a href="editProfile.php">Edit Profile</a></li>
                <li><a href="#">Settings</a></li>
                <li><a href="php/signOut.php">Log out</a></li>
            </ul></div>
        </div>
    </menu>
    <menu id="notifications"  class="topMenu">
        <div class="container-fluid">
            <div class="row"><ul class="notificationsList">
				<?  if($notifications['numRows']>0){
					for($i=0; $i<$notifications['numRows']; $i++){
					$html = '<li><a href="index.php?notification='.$notifications['ID_'.$i].'" class="notificationLink">
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
					echo $html;
					}
					} else {
						echo '<li><a>Sorry, no new notifications!</a></li>';
					}?>
            </ul></div>
        </div>
    </menu>
	<div class="container-fluid">
        <div id="mobileNav" class="mobileNav row">
            <div class="col-xs-3">
              <span id="mainMenuBtn"><img src="images/mainMenu.gif"></span>
            </div>
            <div class="col-xs-6">
              <a href="http://www.perfectcircle.social"><img src="images/logo_xs.gif"></a>
            </div>
            <div class="col-xs-3">
              <span id="notificationsBtn" class="notificationsIcon"><img src="images/notification.gif"><? echo $notifications['display']; ?></span>
            </div>
		</div>
        <div id="desktopNav" class="desktopNav row">
            <div class="col-xs-3">
            	<a href="http://www.perfectcircle.social"><img src="images/logo_xs.gif"></a>
            </div>
            <div class="col-xs-6">
            	<span class="pageTitle">EDIT PROFILE</span>
            </div>
            <div class="col-xs-3">
            	<span id="mainMenu-mdBtn" class="mainMenuIcon"><img src="images/mainMenu.gif"></span>
            	<span id="notifications-mdBtn" class="notificationsIcon"><img src="images/notification.gif"><? echo $notifications['display']; ?></span>
            </div>
		</div>
	</div>
</nav>
<div class="downMenus"><div>
    <div class="container-fluid">
    	<menu id="mainMenu-md" class="row">
        <div class="col-xs-9"></div>
        <div class="col-xs-3 mainMenu-md"><ul>
            <li><a href="http://www.perfectcircle.social">Social Feed</a></li>
            <li><a href="editProfile.php">Edit Profile</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="php/signOut.php">Log out</a></li>
        </ul></div>
        </menu>
        <menu id="notifications-md" class="notifications-md row">
        <div class="col-xs-9"></div>
        <div class="col-xs-3"><ul>
            <?  if($notifications['numRows']>0){
				  for($i=0; $i<$notifications['numRows']; $i++){
				  $html = '<li><a href="index.php?notification='.$notifications['ID_'.$i].'" class="notificationLink">
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
				  echo $html;
				  }
				  } else {
					  echo '<li><a>Sorry, no new notifications!</a></li>';
				  }?>
             </ul></div>
        </menu>
    </div></div>
</div>
<nav class="nav2Box">
</nav>

<main class="container-fluid">
	<div class="row container-main">
        <div class="col-xs-12 col-md-9"><!--left panel-->
        
    	<div class="statusBox">
        	
            <section class="userDisplay col-xs-0 col-md-4" id="DPchangeImg"><!--userDisplay-->
                  <div class="DP-bg">
                      <img src="users/<? echo $user['ID_0'].'/'.$user['DisplayImage_0']; ?>">
                      <p><span><b><? echo $user['FirstName_0'].' '.$user['LastName_0'].'</b><br>'.$user['Occupation_0'].'<br> Talk to me about '.$user['TalkAbout_0']; ?></span></p>
                  </div>
                  <form  id="DPchangeForm" action="php/DPUpload.php" method="post" enctype="multipart/form-data">
                      <input id="imageFile" name="imageFile" type="file" style="display:none;">
                      <input id="userID" name="userID" type="hidden" value="<? echo $user['ID_0']; ?>">
                  </form>
                  <a id="DPchangeBtn" href="#" style="display:block; text-align:center;">Upload new display picture</a>
                  <span id="DPchangeWait" style="display:block; text-align:center;"></span>
			</section><!--END userDisplay-->
			
            <section class="panel col-xs-12 col-md-8" ><!--PostDisplay-->
                <div class="row hide-md">
                    <div class="col-xs-6 col-md-0 userBox" id="DPchangeImg2">
                    	<div class="DP-sm"><img src="users/<? echo $user['ID_0'].'/thumb_'.$user['DisplayImage_0']; ?>"></div>
                    </div>
					<div class="col-xs-6 detailsBox">
                    	<span class="postuName"><? echo $user['FirstName_0'].' '.$user['LastName_0']; ?></span>
                    	<span class="postuDetails"><? echo $user['Occupation_0']; ?></span>
                    	<span class="postuDetails">Talk to me about <? echo $user['TalkAbout_0']; ?></b></span>
					</div>
                </div>
                <div class="row hide-md">
                	<a id="DPchangeBtn2" href="#" class="btn">Upload new display picture</a>
                    <span id="DPchangeWait2" style="display:block; text-align:center;"></span>
                </div>
                <div class="row"><p class="panel-title">Edit Profile</p></div>
                <div class="row"><? echo $saved; ?></div>
                
                <form id="EditUser" method="post" action="editProfile.php">
                 <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="Email">Email:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="Email" id="Email" type="email" autocomplete="off" value="<? echo $user['Email_0']; ?>" placeholder="" /></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="FirstName">First name:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="FirstName" id="FirstName" type="text" autocomplete="off" value="<? echo $user['FirstName_0']; ?>" placeholder="" required/></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="LastName">Last name:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="LastName" id="LastName" type="text" autocomplete="off" value="<? echo $user['LastName_0']; ?>" placeholder="" required/></div>
                </div>
               <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Gender:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="Gender" id="Gender" type="text" autocomplete="off" value="<? echo $user['Gender_0']; ?>" placeholder=""/></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">DOB:</label></div>
                	<div class="col-xs-9 col-md-10"> <input name="DoB" id="DoB" type="date" autocomplete="off" value="<? echo $user['DoB_0']; ?>" placeholder=""/></div>
                </div>
                
                <div class="row">
                	<div class="col-xs-3 col-md-2"> <label for="Country">Country:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="Country" id="Country" type="text" autocomplete="off" value="<? echo $user['Country_0']; ?>" placeholder=""/></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Language:</label></div>
                	<div class="col-xs-9 col-md-10"> <input name="Language" id="Language" type="text" autocomplete="off" value="<? echo $user['Language_0']; ?>" placeholder=""/></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Relationship:</label></div>
                	<div class="col-xs-9 col-md-10"> <select name="Relationship" id="Relationship">
                    	<option <? if($user['Relationship_0']=="Single"){echo "selected";} ?> value="Single">Single</option>
                        <option <? if($user['Relationship_0']=="In a relationship"){echo "selected";} ?> value="In a relationship">In a relationship</option>
                        <option <? if($user['Relationship_0']=="It's complicated"){echo "selected";} ?> value="It's complicated">It's complicated</option>
                        </select>
                     </div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="WroteInterests">Interests:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="Interests" id="WroteInterests" type="text" autocomplete="off" value="<? echo $user['Interests_0']; ?>" placeholder="" /></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="WroteInterests">Occupation:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="Occupation" id="Occupation" type="text" autocomplete="off" value="<? echo $user['Occupation_0']; ?>" placeholder="K" max="30" required/></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="TalkAbout">Talk to me about:</label></div>
                	<div class="col-xs-9 col-md-10"><input name="TalkAbout" id="TalkAbout" type="text" autocomplete="off" value="<? echo $user['TalkAbout_0']; ?>" placeholder="" max="50" required/></div>
                </div>
                
                <input name="DisplayImage" id="DisplayImage" type="hidden" value="<? echo $user['DisplayImage_0']; ?>" />
                <input name="UserID" id="UserID" type="hidden" value="<? echo $user['ID_0']; ?>" />
                <input name="newUser" type="hidden" value="<? echo $newUser; ?>" />
                 <div class="row">
                 	<div class="col-md-6"></div>
                	<div class="col-xs-6 col-md-3"><input class="btn" type="button" value="Go home" onclick="location.href='perfectcircle.social'"/></div>
                    <div class="col-xs-6 col-md-3"><input class="btn" type="submit" value="Save" /></div>
                </div>	
                
            	</form>
			</section>
        </div>
       
        </div><!--END left panel-->
        
        <div class="col-xs-0 col-md-3 right-panel"><div><!--right panel-->
        
        <section class="adCard row" data-spy="affix" data-offset-top="2000">
        	<a href="https://www.facebook.com/sharer/sharer.php?u=http%3A//www.perfectcircle.social/welcome.php" target="_blank"></a>
        </section>
        
        <section class="row" ><!--data-spy="affix" data-offset-top="270"-->
        	<div class="friendsCard">
            	<p class="sectionName">Current friends</p>
                <ul>
            	<? for($i=0; $i<$friends['numRows']; $i++){
                    echo '<li class="friend row">
						<div class="col-md-4"><div class="DP-xs"><img src="users/'.$friends['UserID_'.$i].'/thumb_'.$friends['DisplayImage_'.$i].'"></div></div>
						<div class="col-md-8"><a href="yaMate.php?ID='.$friends['UserID_'.$i].'">'.$friends['FirstName_'.$i].' '.$friends['LastName_'.$i].'</a><span style="color:#FFF;"> ['.$friends['Score_'.$i].']</span></div>
						</li>';
                } ?>
                </ul>       
            </div>
            
            <footer class="about">
                <p>&copy; 2015 <a href="http://www.perfectcircle.social">Perfect Circle</a>. All rights reserved. | Developed by <a href="https://www.linkedin.com/profile/view?id=AAIAAA1ZuBYB9hS-1N_FCw29gNYVNiZslyfnI0g" target="_blank">Scott Kitchell</a> | Social Feed Test Page<br><b>This is not a fully functional application and may not operate as specified.</b></p>
            </footer>
        </section>
        
        
        
        
		</div></div><!--END right panel-->
	</div><!--END container-main-->
</main><!--END container-main-->



</body>
</html>

<?php
	include('php/functions.php');
	signIn();
	//User Profile
	$user = database("SELECT ID, FirstName, LastName, TalkAbout, DisplayImage, Relationship FROM USERS WHERE ID = '".$_SESSION['user']."'");//User Prifile
	
	//BETTY
	$friends = findFriends($_SESSION['user']); // get friends
	$peopleList = parseList($friends,'UserID'); //list friends
	
	if(isset($_GET['ID'])){
		$FriendID = $_GET['ID'];
		$friend = database("SELECT * FROM USERS WHERE ID='".$FriendID."'");
	} else {
		header('location:http://www.perfectcircle.social');
	}
	
	//Display Discussions
	$Posts = database("SELECT ID FROM POSTS WHERE UserID='".$FriendID."' OR FriendID='".$FriendID."' ORDER BY DateTime DESC LIMIT 50");
	
	//notifications
	$notifications = notifications($_SESSION['user']);
	if($notifications['numRows']>0){$notifications['display']= '<div id="notifications">'.$notifications['numRows'].'</div>';} else {$notifications['display'] ='';}
	if(isset($_GET['notification'])){
		$notificationDisplay = notificationDisplay($_GET['notification']);
	}
	
	
	
?>

<!doctype html>
<html prefix="og: http://ogp.me/ns#">
<head>
<!--  meta data  -->
<meta charset="utf-8">
<title>Perfect Circle - <? echo $friend['FirstName_0'].' '.$friend['LastName_0']; ?></title>
<meta name="description" content="Go to <? echo $friend['FirstName_0'].' '.$friend['LastName_0']; ?>">
<meta name="keywords" content="social network, friends, mates, easy">
<link rel='shortcut icon' type='image/x-icon' href='favicon.ico' />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--  Open Graph  -->
<meta property="og:title" content="Perfect Circle Sign Up - The easiest way to stay social" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Perfect Circle" />
<meta property="og:url" content="http://www.perfectcircle.social/yaMate.php?<? echo $friend['ID_0']; ?>" />
<meta property="og:image" content="http://www.perfectcircle.social/images/perfect_circle.jpg" />
<meta property="og:description" content="Go to <? echo $friend['FirstName_0'].' '.$friend['LastName_0']; ?>" />
<!--  jQuery  -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!--  Hammer  -->
<script src="js/hammer.min.js"></script>
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
                <li><a href="#">Friends</a></li>
                <li><a href="#">Experiences</a></li>
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
					$html = '<li><a href="#" class="notificationLink">
								<div><div class="DP-sm"><img src="users/'.$notifications['FriendID_'.$i].'/thumb_'.$notifications['DisplayImage_'.$i].'" ></div></div>
								<div><b>'.$notifications['FriendName_'.$i].'</b> ';
					switch($notifications['AboutType_'.$i]){
						case '1': $html .= 'liked your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
						case '2': $html .= 'commented on your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
						case '3': $html .= 'shared your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
						case '4': $html .= 'liked your comment <i>'.$notifications['Preview_'.$i].'</i>'; break;
						case '5': $html .= 'also commented on a post <i>'.$notifications['Preview_'.$i].'</i>'; break;
						case '6': $html .= 'shared your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
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
            	<span class="pageTitle"><? echo strtoupper($friend['FirstName_0'].' '.$friend['LastName_0']); ?></span>
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
            <li><a href="#">Friends</a></li>
            <li><a href="#">Experiences</a></li>
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
						case '6': $html .= 'shared your post <i>'.$notifications['Preview_'.$i].'</i>'; break;
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
	<div class="container-fluid">
        <div class="row">
        	<div class="col-xs-3">
              <a href="#" class="selected"><span class="icon textIco"></span></a>
            </div>
            <div class="col-xs-3">
              <a href="#"><span class="icon articleIco"></span></a>
            </div>
            <div class="col-xs-3">
              <a href="#"><span class="icon imageIco"></span></a>
            </div>
            <div class="col-xs-3">
              <a href="#"><span class="icon videoIco"></span></a>
            </div>
		</div>
	</div>
</nav>

<main class="container-fluid">
	<div class="row container-main">
        <div class="col-xs-12 col-md-9"><!--left panel-->
        
         <div class="statusBox">
        	
            <section class="userDisplay col-xs-0 col-md-4" id="DPchangeImg"><!--userDisplay-->
                  <div class="DP-bg">
                      <img src="users/<? echo $friend['ID_0'].'/'.$friend['DisplayImage_0']; ?>">
                      <p><span><b><? echo $friend['FirstName_0'].' '.$friend['LastName_0'].'</b><br>'.$friend['Relationship_0'].'<br> Talk to me about '.$friend['TalkAbout_0']; ?></span></p>
                  </div>
			</section><!--END userDisplay-->
			
            <section class="editProfile col-xs-12 col-md-8" ><!--PostDisplay-->
                <div class="row hide-md">
                    <div class="col-xs-6 col-md-0 userBox" id="DPchangeImg2">
                    	<div class="DP-sm"><img src="users/<? echo $friend['ID_0'].'/thumb_'.$friend['DisplayImage_0']; ?>"></div>
                    </div>
					<div class="col-xs-6 detailsBox">
                    	<span class="postuName"><? echo $friend['FirstName_0'].' '.$friend['LastName_0']; ?></span>
                    	<span class="postuDetails"><? echo $friend['Relationship_0']; ?></span>
                    	<span class="postuDetails">Talk to me about <? echo $friend['TalkAbout_0']; ?></b></span>
					</div>
                </div>

                <div class="row"><p class="panel-title"><? echo $friend['FirstName_0'].' '.$friend['LastName_0']; ?></p></div>
                
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">DOB:</label></div>
                	<div class="col-xs-9 col-md-10"> <? echo $friend['DoB_0']; ?></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Gender:</label></div>
                	<div class="col-xs-9 col-md-10"><? echo $friend['Gender_0']; ?></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"> <label for="Country">Country:</label></div>
                	<div class="col-xs-9 col-md-10"><? echo $friend['Country_0']; ?></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Language:</label></div>
                	<div class="col-xs-9 col-md-10"> <? echo $friend['Language_0']; ?></div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="DOB">Relationship:</label></div>
                	<div class="col-xs-9 col-md-10"> <? echo $friend['Relationship_0']; ?>     </div>
                </div>
                <div class="row">
                	<div class="col-xs-3 col-md-2"><label for="WroteInterests">Interests:</label></div>
                	<div class="col-xs-9 col-md-10"><? echo $friend['Interests_0']; ?></div>
                </div> 	               
			</section>
        </div>    
        
    	<header class="row">
        <section class="userDisplay col-xs-0 col-md-4"><!--userDisplay-->
        	<div class="DP-bg">
            	<img src="users/<? echo $user['ID_0'].'/'.$user['DisplayImage_0']; ?>">
                <p style="opacity:1;"><span><b><? echo $user['FirstName_0'].' '.$user['LastName_0']; ?></b><br><? echo $user['Relationship_0']; ?><br><? echo 'Talk to me about '.$user['TalkAbout_0']; ?></span></p>
            </div>
        </section><!--END userDisplay-->
        
    	<section class="col-xs-12 col-md-8">
        	<section class="panel"><!--newPost-->
                <div class="row">
                    <form class="postForm" method="post" action="index.php" name="DiscussionForm" id="DiscussionForm">
                        <div class="col-xs-12">
                            <textarea name="post" id="discussion_text" placeholder="Talk to <? echo $friend['FirstName_0']; ?>" rows="1"></textarea>
                            <input type="hidden" name="user" id="discussion_user" value="<? echo $_SESSION['user']; ?>">
                            <input type="hidden" name="friend" id="discussion_friend" value="<? echo $friend['ID_0']; ?>">
                            <input type="hidden" name="action" id="discussion_action" value="1">
                            <input type="hidden" name="file" id="discussion_file" value="">
                            <input type="hidden" name="link" id="discussion_link" value="">
                        </div>
                    </form>
                </div>
                <div class="row types">
                    <div class="col-xs-3"><span id="textUploadBtn" class="btn btn-simple selected"><span class="icon textIco"></span> Text</span></div>
                    <div class="col-xs-3"><span  id="articleUploadBtn" class="btn btn-simple"><span class="icon articleIco"></span> Article</span></div>
                    <div class="col-xs-3"><span id="imageUploadBtn" class="btn btn-simple"><span class="icon imageIco"></span> Picture</span></div>
                    <div class="col-xs-3"><span  id="videoUploadBtn" class="btn btn-simple"><span class="icon videoIco"></span> Video</span></div>
                </div>
              <!--Articles-->
                <div id="articleUploadBox" class="row extrasBox">
                    <div class="col-xs-3"><label for="imageLink">link:</label></div>
                    <div class="col-xs-9"><input type="url" name="articleLink" id="articleLink" placeholder="Paste here" autocomplete="off"></div>
                    <div class="col-xs-12 PreviewBox" id="articlePreview"></div>
                </div>
             <!--Images-->
                <div id="imageUploadBox" class="row extrasBox">
                    <div class="col-xs-3"><label for="imageFile">upload:</label></div>
                    <div class="col-xs-9"><form method="post" action="php/fileUpload.php" id="imageFileForm" enctype="multipart/form-data"><label for="imageFile" placeholder="Browse" style="padding:10px; color:#777;">Browse</label>
                    <input type="file" name="imageFile" id="imageFile"><input type="hidden" name="deleteFile" class="deleteFile" value=""></form></div>
                    <div class="clearfix"></div>
                    <div class="col-xs-3"><label for="imageLink">or link:</label></div>
                    <div class="col-xs-9"><input type="url" name="imageLink" id="imageLink" placeholder="Paste here" autocomplete="off"></div>
                    <div class="col-xs-12 PreviewBox" id="imagePreview"></div>
                </div>
             <!--videos-->
                <div id="videoUploadBox" class="row extrasBox">
                    <div class="col-xs-3"><label for="videoLink">link:</label></div>
                    <div class="col-xs-9"><input type="url" name="videoLink" id="videoLink" placeholder="Paste here" autocomplete="off"></div>
                    <div class="col-xs-12 PreviewBox" id="videoPreview"></div>
                </div>
    
                <div class="row startBox">
                    <div class="col-xs-9"><input type="checkbox" name="allow_share" id="allow_share"> <label for="allow_share">Allow this to be shared publicly</label></div>
                    <div class="col-xs-3"><input class="btn" id="discussion_submit" type="button" value="post it!"></div>
                </div>
            </section><!--END newPost-->
            
            <div class="sectionH1 show-md">HOW DO YOU WANT YOUR SOCIAL SERVED?</div>
            
            <section class="feedType col-xs-0 col-md-12"><!--Feed Type-->
        	<div class="row types">
                <div class="col-xs-3">
                  <a href="#" class="selected"><span class="icon textIco"></span> Feed it all</a>
                </div>
                <div class="col-xs-3">
                  <a href="#"><span class="icon articleIco"></span> Articles feed</a>
                </div>
                <div class="col-xs-3">
                  <a href="#"><span class="icon imageIco"></span> Pics feed</a>
                </div>
                <div class="col-xs-3">
                  <a href="#"><span class="icon videoIco"></span> Video feed</a>
                </div>
            </div>
        </section><!--END Feed Type-->
        
        </section>
        
    	</header>
        <div class="col-xs-0 col-md-4"></div><div class="col-xs-12 col-md-8"><div class="sectionH1">FEED IT ALL</div></div>
        <div id="newPost" class="row"></div>
    	<? for($i=0; $i<$Posts['numRows']; $i++){
			$postHTML = displayPost($Posts['ID_'.$i]);
			echo ' <div class="row"><!--Post-->'.$postHTML.'</div><!--END Post -->';
		} ?>
       
        </div><!--END left panel-->
        
        <div class="col-xs-0 col-md-3 right-panel"><div><!--right panel-->
        
        <section class="adCard row" data-spy="affix" data-offset-top="2000">
        	<a href="https://www.facebook.com/sharer/sharer.php?u=http%3A//www.perfectcircle.social/welcome.php" target="_blank"></a>
        </section>
        
        <section class="row" ><!--data-spy="affix" data-offset-top="270"-->
        	<div class="friendsCard">
            	<p class="panel-title">Current friends</p>
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

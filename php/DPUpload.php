<?php
include('functions.php');
$userID = $_SESSION['user'];
/*if(is_array($_FILES)) {
	if(is_uploaded_file($_FILES['imageFile']['tmp_name'])) {
		$sourcePath = $_FILES['imageFile']['tmp_name'];
		$fileName = newID(12).'.'. pathinfo($_FILES['imageFile']['name'],PATHINFO_EXTENSION);
		$targetPath = '../users/'.$userID.'/'.$fileName;
		if(move_uploaded_file($sourcePath,$targetPath)) {
			echo $fileName;
		}
	}
}*/

############ Configuration ##############
$thumb_square_size      = 140; //Thumbnails will be cropped to 200x200 pixels
$max_image_size         = 250; //Maximum image size (height and width)
$thumb_prefix           = "thumb_"; //Normal thumb Prefix
$destination_folder     = '../users/'.$userID.'/'; //upload directory ends with / (slash)
$jpeg_quality           = 90; //jpeg quality
##########################################

//continue only if $_POST is set and it is a Ajax request
if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){

     // check $_FILES['ImageFile'] not empty
     if(!isset($_FILES['imageFile']) || !is_uploaded_file($_FILES['imageFile']['tmp_name'])){
             die('Image file is Missing!'); // output error when above checks fail.
     }
     
     //get uploaded file info before we proceed
     $image_name = $_FILES['imageFile']['name']; //file name
     $image_size = $_FILES['imageFile']['size']; //file size
     $image_temp = $_FILES['imageFile']['tmp_name']; //file temp

     $image_size_info    = getimagesize($image_temp); //gets image size info from valid image file
     
     if($image_size_info){
         $image_width        = $image_size_info[0]; //image width
         $image_height       = $image_size_info[1]; //image height
         $image_type         = $image_size_info['mime']; //image type
     }else{
         die("Make sure image file is valid!");
     }

     //switch statement below checks allowed image type 
     //as well as creates new image from given file 
     switch($image_type){
         case 'image/png':
             $image_res =  imagecreatefrompng($image_temp); break;
         case 'image/gif':
             $image_res =  imagecreatefromgif($image_temp); break;           
         case 'image/jpeg': case 'image/pjpeg': case 'image/jpg':
             $image_res = imagecreatefromjpeg($image_temp); break;
         default:
             $image_res = false;
     }

     if($image_res){
         //Get file extension and name to construct new file name 
         $image_info = pathinfo($image_name);
         $image_extension = strtolower($image_info["extension"]); //image extension
         $image_name_only = strtolower($image_info["filename"]);//file name only, no extension
         
         //create a random name for new image (Eg: fileName_293749.jpg) ;
         $new_file_name = 'DP_'.newID(9) . '.' . $image_extension;
         
         //folder path to save resized images and thumbnails
         $thumb_save_folder  = $destination_folder . $thumb_prefix . $new_file_name; 
         $image_save_folder  = $destination_folder . $new_file_name;
         
         //call normal_resize_image() function to proportionally resize image
         if(crop_image_square($image_res, $image_save_folder, $image_type, $max_image_size, $image_width, $image_height, $jpeg_quality))
         {
             //call crop_image_square() function to create square thumbnails
             if(!crop_image_square($image_res, $thumb_save_folder, $image_type, $thumb_square_size, $image_width, $image_height, $jpeg_quality))
             {
                 die('Error Creating thumbnail');
             }
             
             /* We have succesfully resized and created thumbnail image
             We can now output image to user's browser or store information in the database*/
             
			echo $new_file_name;
			database("UPDATE USERS SET DisplayImage='".$new_file_name."' WHERE ID='".$userID."'");
         }
         
         imagedestroy($image_res); //freeup memory
     }
}
?>
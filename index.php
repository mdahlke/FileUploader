<?php

require '../db.php';

if(isset($_POST['submitForm'])){
	require 'ImageUploader.class.php';
	
	$uploader = new ImageUploader();
	$uploader->setOriginalFilePath('formUpload/');
	$uploader->setPrependToFileName('testingScript');
	$uploader->setNumberOfAllowedImagesToUpload(20);
	$uploader->setNumberOfFilesPerDirectory(20);
	$uploader->uploadImage($_FILES['image']);

	if($uploader->getNumberOfErrorsDuringUpload() > 0){
		echo $uploader->returnFormattedErrors();
	}
	else {
		$uploader->echoAllFileNames();
		$uploader->echoAllPathNames();
	}
}
else if(isset($_POST['submitURL'])){
	
	require 'ImageUploader.class.php';
	require 'SingleURLImageUploader.class.php';
	
	$uploader = new SingleURLImageUploader();
	$uploader->setOriginalFilePath('URLUpload/');
	$uploader->setPrependToFileName('testingScript');
	$uploader->setNumberOfAllowedImagesToUpload(20);
	$uploader->setNumberOfFilesPerDirectory(20);
	$uploader->uploadSingleURLImage($_POST['url']);
	
	if($uploader->getNumberOfErrorsDuringUpload() > 0){
		echo $uploader->returnFormattedErrors();
	}
	else {
		$uploader->echoAllFileNames();
		$uploader->echoAllPathNames();
	}


}
?>

<!doctype html>
<head>
	<title>Image Class Testing Script</title>
</head>

</head>

<body>
	<form action="" method="post" enctype="multipart/form-data">
		<h3><label for="image">Select Image:</label></h3>
		
		<?php
			$numOfImagesToUpload = 20;
			for($i = 0; $i < $numOfImagesToUpload; $i++){
				echo "
					<input type='file' name='image[]'/>
				";
			}
		?>
		<br />
		<br />
		
		<input type="submit" name="submitForm" value="Upload"/>
	</form>
	
	<form action="" method="post" enctype="multipart/form-data">
		<h3><label for="image">Select Image:</label></h3>

					<input type='url' name='url' placeholder="enter url"/>
		
		<input type="submit" name="submitURL" value="UploadURL"/>
	</form>
</body>
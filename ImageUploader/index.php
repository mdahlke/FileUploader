<?php

if(isset($_POST['submitForm'])){
	require 'classes/UploadImage.class.php';
	
	$imageUploader = new UploadImage();
	$imageUploader->setOriginalFilePath('ImageUpload/FormUpload');
	$imageUploader->setPrependToFileName('testingScript');
	$imageUploader->setNumberOfAllowedFilesToUpload(20);
	$imageUploader->setNumberOfFilesPerDirectory(20);
	$imageUploader->upload($_FILES['image']);

	if($imageUploader->getNumberOfErrorsDuringUpload() > 0){
		echo '<pre>'.$imageUploader->returnFormattedErrors().'</pre>';
	}
	
	for($i = 0; $i < $imageUploader->getNumberOfSuccessfulUploads(); $i++){
		echo '<img src="'.$imageUploader->getFilePathThumbAtIndex($i).$imageUploader->getFileNameThumbAtIndex($i).'"/>';
	}
}
else if(isset($_POST['submitURL'])){
	require 'classes/SingleURLImageUploader.class.php';
	
	$URLUploader = new SingleURLImageUploader();
	$URLUploader->setOriginalFilePath('ImageUpload/URLUpload/');
	$URLUploader->setPrependToFileName('testingScript');
	$URLUploader->setNumberOfFilesPerDirectory(20);
	$URLUploader->uploadSingleURLImage($_POST['url']);
	
	if($URLUploader->getNumberOfErrorsDuringUpload() > 0){
		echo '<pre>'.$URLUploader->returnFormattedErrors().'</pre>';
	}
	
	for($i = 0; $i < $URLUploader->getNumberOfSuccessfulUploads(); $i++){
		echo '<img src="'.$URLUploader->getFilePathThumbAtIndex($i).$URLUploader->getFileNameThumbAtIndex($i).'"/>';
	}

}
else if(isset($_POST['submitAudio'])){
	require 'classes/UploadAudio.class.php';
	
	if(!empty($_POST['newDirectory'])){
		$directory = $_POST['newDirectory'];
	}
	else {
		$directory = 'AudioUpload/';
	}
	
	$audioUploader = new UploadAudio();
	$audioUploader->setOriginalFilePath($directory);
	$audioUploader->setPrependToFileName('testingScript');
	$audioUploader->setNumberOfAllowedFilesToUpload(20);
	$audioUploader->upload($_FILES['audio']);
	
	if($audioUploader->getNumberOfErrorsDuringUpload() > 0){
		echo '<pre>'.$audioUploader->returnFormattedErrors().'</pre>';
	}
	
	for($i = 0; $i < $audioUploader->getNumberOfSuccessfulUploads(); $i++){
		echo 
			$audioUploader->getFileNameAtIndex($i)
			."	<br />
			<audio style='display:block;' controls src='".$audioUploader->getFilePathAtIndex($i).$audioUploader->getFileNameAtIndex($i)."' type='".$audioUploader->getFullFileTypeAtIndex($i)."'>
				Your browser does not support HTML audio
			</audio>
		";
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
		<h3><label for="image">Select Image(s):</label></h3>
		
		<input type='file' name='image[]' multiple/>
		
		<input type="submit" name="submitForm" value="Upload"/>
	</form>
	
	<hr />
	
	<form action="" method="post" enctype="multipart/form-data">
		<h3><label for="image">Enter URL of image:</label></h3>

		<input type='url' name='url' placeholder="enter url"/>
		
		<input type="submit" name="submitURL" value="UploadURL"/>
	</form>
	
	<hr />
	
	<form action="" method="post" enctype="multipart/form-data">
		<h3><label for="image">Select Audio:</label></h3>

		<input type='file' name='audio[]' multiple/>
		<br />
		<label>Create new directory (ex. audio/playlists/Eminem/)</label>
		<input type="text" name="newDirectory" />
		
		<input type="submit" name="submitAudio" value="UploadAudio"/>
	</form> 
</body>
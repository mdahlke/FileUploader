<?php

	if(isset($_POST['tenImageSubmit'])){
		
		require 'ImageUploader.class.php';
		
		$uploader = new ImageUploader();

		$uploader->setOriginalFilePath('images/singleUpload/');

		$uploader->setPrependToFileName('ImageUploader');

		$uploader->setNumberOfAllowedImagesToUpload(10);

		$uploader->setNumberOfFilesPerDirectory(10);

		$uploader->uploadImage($_FILES['image']);

		echo "<h4>File Names:</h4>";
		echo $uploader->echoAllFileNames();

		echo "<h4>File Paths:</h4>";
		echo $uploader->echoAllPathNames();

		echo "<h4>MySQL Query:</h4>";
		echo $uploader->returnMysqlQuery();

		echo "<h4>Uploading Errors: ";
		if($uploader->getNumberOfErrorsDuringUpload() > 0){
			foreach($uploader->returnAllUploadErrorMessages() as $error){
				echo '<p style="color:red">'.$error.'</p>';
			}
		}
		else {
			echo "No uploading errors!";
		}
	}

?>

<!doctype html>
<head>

	<title>Image Class Testing Script</title>

	<style type="text/css">
		div {
			display:block;
			position:relative;
			border:1px solid black;
			padding:10px;
		}
	</style>

</head>

<body>
	<h1>Welcome to the Image Uploader!</h1>
	<p>This PHP class will help handle image uploading and can do so adding just two lines of code!</p>

	<div id='tenImages'>

		<form action="" method="post" enctype="multipart/form-data">
			<h3><label for="image">Select Image:</label></h3>
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<input type="file" name="image[]"/>
			<br />
			<br />
			
			<input type="submit" name="tenImageSubmit" value="Upload"/>
		</form>

	</div>

</body>
</html>

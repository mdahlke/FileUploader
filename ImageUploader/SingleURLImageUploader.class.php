<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of uploadSingleURLImage
 *
 * @author Michael
 */

require ('ImageUploader.class.php');
class SingleURLImageUploader extends ImageUploader {

	public function uploadSingleURLImage($file){
		
		$startingFilePath = $this->getOriginalFilePath();
		$continueWithUpload = true;

		$image = getimagesize($file);
		$imageWidth = $image[0];
		$imageHeight = $image[1];
		$URLimageType = $image['mime'];

		try {
			if(!$this->isImage($URLimageType)){
				throw new Exception($file . ' is not an image so it was not uploaded.',  1001);
			}
		}
		catch(Exception $e) {
			$this->numberOfErrors += 1;			
			$this->userReadableUploadErrorMessages[] = $e->getMessage();
			$this->userReadableUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorMessages[] = $e->getMessage();
			$this->allUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorsLineNumber[] = $e->getLine();
			$this->allUploadErrorsFileName[] = $e->getFile();
			$continueWithUpload = false;
		}

		if($continueWithUpload){			
			//$u = $this->getNumberOfSuccessfulUploads();
			if($URLimageType == 'image/jpeg'){
				$imageType = 'jpg';
			}
			else if($URLimageType == 'image/png'){
				$imageType = 'png';
			}
			else if($URLimageType == 'image/gif'){
				$imageType = 'gif';
			}

			$uploadedFile = $file;

			try {// Check image type and then create jpeg of it
				if($URLimageType === 'image/jpg' || $URLimageType === 'image/jpeg' || $URLimageType === 'image/pjpeg') {
					$src = imagecreatefromjpeg($uploadedFile);
				}
				else if($URLimageType === 'image/png'){
					$imageType = 'png';
					$src = imagecreatefrompng($uploadedFile);
				}
				else if($URLimageType === 'image/gif') {
					$imageType = 'gif';
					$src = imagecreatefromgif($uploadedFile);
				}
				else {
					throw new Exception('Not an image', 1001);
				}
			}
			catch(Exception $e){
				$this->numberOfErrors += 1;			
				$this->userReadableUploadErrorMessages[] = $e->getMessage();
				$this->userReadableUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			$smallWidth = $this->getSmallImageWidth();
			$smallHeight = $this->getSmallImageHeight();

			$thumbWidth = $this->getThumbImageWidth();
			$thumbHeight = $this->getThumbImageHeight();

			if($imageWidth > $imageHeight){
				$ratioOfDifference = $smallWidth / $imageWidth;

				$smallImageWidth = $ratioOfDifference * $imageWidth;
				$smallImageHeight = $ratioOfDifference * $imageHeight;
			}
			else {
				$ratioOfDifference = $smallHeight / $imageHeight;

				$smallImageWidth = $ratioOfDifference * $imageWidth;
				$smallImageHeight = $ratioOfDifference * $imageHeight;
			}

			if($imageWidth > $imageHeight){
				$ratioOfDifference = $thumbWidth / $imageWidth;

				$thumbImageWidth = $ratioOfDifference * $imageWidth;
				$thumbImageHeight = $ratioOfDifference * $imageHeight;
			}
			else {
				$ratioOfDifference = $thumbHeight / $imageHeight;

				$thumbImageWidth = $ratioOfDifference * $imageWidth;
				$thumbImageHeight = $ratioOfDifference * $imageHeight;
			}

			// Original
			$tmp = imagecreatetruecolor($imageWidth,$imageHeight);

			// Small
			$tmp1 = imagecreatetruecolor($smallImageWidth,$smallImageHeight);

			// Thumbnail
			$tmp2 = imagecreatetruecolor($thumbImageWidth,$thumbImageHeight);
			
			$this->keepImageTransparency($tmp);
			$this->keepImageTransparency($tmp1);
			$this->keepImageTransparency($tmp2);

			// Copy the images with the new $width & $height
			imagecopyresampled($tmp,$src,0,0,0,0,$imageWidth,$imageHeight,$imageWidth,$imageHeight);
			imagecopyresampled($tmp1,$src,0,0,0,0,$smallImageWidth,$smallImageHeight,$imageWidth,$imageHeight);
			imagecopyresampled($tmp2,$src,0,0,0,0,$thumbImageWidth,$thumbImageHeight,$imageWidth,$imageHeight);

			/*
				Creating the file paths to the images
				This will count the number of folders in the directory,
					to get the correct folder to enter,
				then count the number of images in that folder
				and when there are 100 images inside that folder
				a new folder is created with the next number in line
			*/
			$directoryFull = $startingFilePath;						// The path to upload to
			//
//			if($this->inParentDirectory){
//				$upDirectory = '../';
//			}
//			else {
//				$upDirectory = '';
//			}
			
			if(!file_exists($directoryFull)){
				parent::createUploadingDirectory($directoryFull);
			}
			$glob  = glob( realpath( $directoryFull ) . '/*' );					

			$sub_directories = array_filter( $glob, 'is_dir' );				
			$sub_directories_count = count($sub_directories);					// Counts the directories in the file path

			$sub_directory = $sub_directories_count;							// The count of the directories is going to be the name
																					// of our sub folder
			$filecount = count(glob($directoryFull.$sub_directory."*/*"));		// Count the number of files in the sub directory

			if($filecount >= $this->getNumberOfFilesPerDirectory()){							// When there are 100 files in that sub directory we create a new on
				$sub_directories_count++;														// We then add 1 to the old sub directory (it is a number)
				$newSubDirectory = $directoryFull.$sub_directories_count;						// The new sub directory will be the old directory + 1
				$directorySmall = str_replace("full/", "small/", $newSubDirectory);				// To create a directory for the small images and thumb images
				$directoryThumb = str_replace("full/", "thumbnail/", $newSubDirectory);			// I used the str_replace() function

				mkdir($newSubDirectory."/", 0777);												// We then create the 3 new sub directories in their respective
				mkdir($directorySmall."/", 0777);												// Parent directory and make the read/write
				mkdir($directoryThumb."/", 0777);

				$directoryFull = $newSubDirectory."/";
			}
			else {																				// If there are not 100 files in that folder yet we continue
				$directoryFull .= $sub_directories_count."/";
				$directorySmall = str_replace("full/", "small/", $directoryFull);					// /* To create a directory for the small images and thumb images
				$directoryThumb = str_replace("full/", "thumbnail/", $directoryFull);				//    I used the str_replace() function */
			}

			/* Here are the 3 new directories! */
			$this->setFilePathFull($directoryFull);
			$this->setFilePathSmall($directorySmall);
			$this->setFilePathThumb($directoryThumb);

			// Create a filename that won't be repeated
			$this->setFileNameFull($this->getPrependToFileName().md5(rand()).'.'.$imageType);

			$fullFileName = $this->getFileNameFullAtIndex(0);

			try {
				$this->setFileNameSmall($smallImageWidth."x".$smallImageHeight."_".$fullFileName);
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				$this->setFileNameThumb($thumbImageWidth."x".$thumbImageHeight."_".$fullFileName);
			}
			catch (Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			// Send the Images to the correct place
			if($imageType === $this::GIF && self::$allowGIFImages){
				
				try {
					if(!imagegif($tmp, $this->getFilePathFullAtIndex(0).$this->getFileNameFullAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e) {
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}

				try {
					if(!imagegif($tmp1, $this->getFilePathSmallAtIndex(0).$this->getFileNameSmallAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
				try {
					if(!imagegif($tmp2, $this->getFilePathThumbAtIndex(0).$this->getFileNameSmallAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
			}
			else if($imageType === $this::PNG){

				try {
					if(!imagepng($tmp, $this->getFilePathFullAtIndex(0).$this->getFileNameFullAtIndex(0), $this->getThumbImageQuality($imageType))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e) {
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}

				try {
					if(!imagepng($tmp1, $this->getFilePathSmallAtIndex(0).$this->getFileNameSmallAtIndex(0), $this->getThumbImageQuality($imageType))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
				try {
					if(!imagepng($tmp2, $this->getFilePathThumbAtIndex(0).$this->getFileNameSmallAtIndex(0), $this->getThumbImageQuality($imageType))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
			}
			else if(($imageType === $this::GIF  || $imageType === $this::PNG) && !self::$allowGIFImages){
				try {
					if(!imagepng($tmp, $this->getFilePathFullAtIndex(0).$this->getFileNameFullAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e) {
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}

				try {
					if(!imagepng($tmp1, $this->getFilePathSmallAtIndex(0).$this->getFileNameSmallAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
				try {
					if(!imagepng($tmp2, $this->getFilePathThumbAtIndex(0).$this->getFileNameSmallAtIndex(0))){
						throw new Exception('Could not create "imagegif". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1003);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
			}
			elseif($imageType === $this::JPG){
				try {
					if(!imagejpeg($tmp, $this->getFilePathFullAtIndex(0).$this->getFileNameFullAtIndex(0), $this->getFullImageQuality($imageType))){
						throw new Exception('Could not create "imagejpeg". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1004);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}

				try {
					if(!imagejpeg($tmp1, $this->getFilePathSmallAtIndex(0).$this->getFileNameSmallAtIndex(0), $this->getSmallImageQuality($imageType))){
						throw new Exception('Could not create "imagejpeg". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1004);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}

				try {
					if(!imagejpeg($tmp2, $this->getFilePathThumbAtIndex(0).$this->getFileNameThumbAtIndex(0), $this->getThumbImageQuality($imageType))){
						throw new Exception('Could not create "imagejpeg". 
											Filepath: '.$this->getFilePathFullAtIndex(0). ' or 
											Filename: '.$this->getFileNameFullAtIndex(0).' does not exist.', 1004);
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
			}
			// Destroy temporary images
			imagedestroy($tmp);
			imagedestroy($tmp1);
			imagedestroy($tmp2);

			$this->numberOfSuccessfulUploads = $this->getNumberOfSuccessfulUploads() + 1;

		} // end if(....);
		
		if(count($this->returnAllUploadErrorMessages()) > 0){
			$this->writeAllErrorsToLog();
		}
		
		if($this->inParentDirectory){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$this->filePathFull[$i] = substr($this->getFilePathFullAtIndex($i), 3);
				$this->filePathSmall[$i] = substr($this->getFilePathSmallAtIndex($i), 3);
				$this->filePathThumb[$i] = substr($this->getFilePathThumbAtIndex($i), 3);
			}
		}

	} // end imageUploader
	
}


?>

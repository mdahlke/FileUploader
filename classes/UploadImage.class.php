<?php
/**
 * 
 * UploadImage will create an object that will allow
 * for easy implementation of uploading an image
 * 
 * This image uploader will allow for multiple images to be uploaded at once
 * The uploader will also make two copies of the image (small, thumb)
 *	that will be uploaded to their respective folder within the same parent folder
 *	as the original image
 *
 *  IMPORTANT
 *	- The <input> name must be an array even if there is one <input>
 *		+ ex. <input type='file' name='image[]'/>
 *	- You must set the originalFilePath unless 'images/full/' is your working
 *		directory
 *		+ the small & thumbnail directories will be set in accordance to your
 *			full directory
 *			+ ex. 
 *				<?php
 *					$imageUploader = new ImageUploader();
 *					$imageUploader->setOriginalFilePath('images/uploaded/full');
 *				?>
 *				
 *				after the uploadImage() function is called, the small and thumbnail
 *				directories variables will be automatically set to...
 *				'images/uploaded/small' && 'images/uploaded/thumb'
 *	- The default maxUploadSize of an image is 5MB
 *	- You may set a custom prependToFileName by calling the 
 *		setPrependToFileName() function.
 *		+ This will take whatever you set it to and prepend it to the filename
 *			so you can better organize your photos
 *	- If you are uploading more than one image then you can set the number of
 *		uploading images by calling numberOfAllowedImagesToUpload();
 *	- It is also possible to set the smallImage and the thumbImage desired height
 *		and width using setSmallImageWidthAndHeight() and setThumbImageWidthAndHeight()
 *		respectively
 *		+ It's important to note that this script will ignore either the desired
 *			height or width in order to maintain aspect ratio
 *  - You may set the number of files allowed per directory (default: 100)
 * 
 * @author Michael Dahlke
 */
require ('ImageUploader.class.php');

class UploadImage extends ImageUploader {
	/**
	 * Image uploader magic is done here!
	 * 
	 * we take an image and then call the above functions to verify
	 * the file is indeed an image and it is not too large to be uploaded
	 * 
	 * @param string $file the path of the file that is to be uploaded( $_FILES[''] from a form)
	 * 
	 */
	public function upload($file){		
		$startingFilePath = $this->getOriginalFilePath();
		
		try {
			if(!is_array($file['name'])){
				throw new Exception('Input name must be an array ( ex. &lt;input type="file" name="image[]"/&gt; )', '3001');
			}
		}
		catch (Exception $e){
			echo 'ERROR '. $e->getCode() .': '. $e->getMessage();
			exit();
		}
		
		for($i = 0; $i < $this->getNumberOfAllowedFilesToUpload(); $i++){
			
			$continueWithUpload = $this->validateFile($file['name'][$i], $file['size'][$i], $file['type'][$i]);
			
			if($continueWithUpload){			
				$u = $this->getNumberOfSuccessfulUploads();
				$imageType = 'jpg';
				
				$uploadedFile = $file['tmp_name'][$i];
				
				// Check image type and then create jpeg of it
				if($file['type'][$i] === 'image/jpg' || $file['type'][$i] === 'image/jpeg' || $file['type'][$i] === 'image/pjpeg') {
					$src = imagecreatefromjpeg($uploadedFile);
				}
				else if($file['type'][$i] === 'image/png') {
					$imageType = 'png';
					$src = imagecreatefrompng($uploadedFile);
				}
				else if($file['type'][$i] === 'image/gif') {
					$imageType = 'png';
					$src = imagecreatefromgif($uploadedFile);
				}

				list($imageWidth,$imageHeight) = getimagesize($uploadedFile);
				
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
					This will count the number of folder in the directory,
						to get the correct folder to enter,
					then count the number of images in that folder
					and when there are 100 images inside that folder
					a new folder is created with the next number in line
				*/
				if($this->inParentDirectory){
					$upDirectory = '../';
				}
				else {
					$upDirectory = '';
				}
				$directoryFull = $startingFilePath;						// The path to upload to

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

					mkdir($newSubDirectory."/", 0755);												// We then create the 3 new sub directories in their respective
					mkdir($directorySmall."/", 0755);												// Parent directory and make the read/write
					mkdir($directoryThumb."/", 0755);

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
				
				$fullFileName = $this->getFileNameFullAtIndex($u);
				
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
				if($imageType === $this::GIF && false){
					try {
						if(!imagegif($tmp, $this->getFilePathFullAtIndex($u).$this->getFileNameFullAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e) {
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					
					try {
						if(!imagegif($tmp1, $this->getFilePathSmallAtIndex($u).$this->getFileNameSmallAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					try {
						if(!imagegif($tmp2, $this->getFilePathThumbAtIndex($u).$this->getFileNameSmallAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
				}
				elseif($imageType === $this::PNG || $imageType === $this::GIF){
					try {
						if(!imagepng($tmp, $this->getFilePathFullAtIndex($u).$this->getFileNameFullAtIndex($u), $this->getFullImageQuality($imageType))){
							throw new Exception('Could not create "imagepng". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					
					try {
						if(!imagepng($tmp1, $this->getFilePathSmallAtIndex($u).$this->getFileNameSmallAtIndex($u), $this->getSmallImageQuality($imageType))){
							throw new Exception('Could not create "imagepng". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					
					try {
						if(!imagepng($tmp2, $this->getFilePathThumbAtIndex($u).$this->getFileNameThumbAtIndex($u), $this->getThumbImageQuality($imageType))){
							throw new Exception('Could not create "imagepng". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
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
						if(!imagejpeg($tmp, $this->getFilePathFullAtIndex($u).$this->getFileNameFullAtIndex($u), $this->getFullImageQuality($imageType))){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					
					try {
						if(!imagejpeg($tmp1, $this->getFilePathSmallAtIndex($u).$this->getFileNameSmallAtIndex($u), $this->getSmallImageQuality($imageType))){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						$this->allUploadErrorsLineNumber[] = $e->getLine();
						$this->allUploadErrorsFileName[] = $e->getFile();
					}
					
					try {
						if(!imagejpeg($tmp2, $this->getFilePathThumbAtIndex($u).$this->getFileNameThumbAtIndex($u), $this->getThumbImageQuality($imageType))){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
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
			
		} // for() loop;
		
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
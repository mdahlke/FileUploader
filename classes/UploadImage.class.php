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
require ( __DIR__ . '/ImageUploader.class.php');

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
	public function upload(){		
		$startingFilePath = $this->getOriginalFilePath();
		
		try {
			if(!is_array($this->_file['name'])){
				throw new Exception('Input name must be an array ( ex. &lt;input type="file" name="image[]"/&gt; )', '3001');
			}
		}
		catch (Exception $e){
			echo 'ERROR '. $e->getCode() .': '. $e->getMessage();
			exit;
		}
				
		$this->_smallWidth = $this->getSmallImageWidth();
		$this->_smallHeight = $this->getSmallImageHeight();

		$this->_thumbWidth = $this->getThumbImageWidth();
		$this->_thumbHeight = $this->getThumbImageHeight();
		
		$numberOfImagesToBeUploaded = $this->getNumberOfAllowedFilesToUpload() > count($this->_file['name']) ? count($this->_file['name']) : $this->getNumberOfAllowedFilesToUpload();
		
		for($i = 0; $i < $numberOfImagesToBeUploaded; $i++){
			
			$continueWithUpload = $this->validateFile($this->_file['name'][$i], $this->_file['size'][$i], $this->_file['type'][$i]);
			
			
			if($continueWithUpload){
				$this->resizeImage($i);
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
				$directoryFull = $startingFilePath;	// The path to upload to
		
				$this->createImageDirectory($directoryFull, $i);

				// Send the Images to the correct place
				$this->createImage($this->_type, $this->_tmp, $this->_tmp1, $this->_tmp2, $i);
				
				// Destroy temporary images
				imagedestroy($this->_tmp);
				imagedestroy($this->_tmp1);
				imagedestroy($this->_tmp2);

				
				$this->numberOfSuccessfulUploads = $this->getNumberOfSuccessfulUploads() + 1;
				
			} // end if(....);
			else {
				echo 'Did not continue.';
			}
			
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
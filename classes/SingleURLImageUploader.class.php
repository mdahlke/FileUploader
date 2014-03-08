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

require ( __DIR__ . '/UploadImage.class.php');

class SingleURLImageUploader extends UploadImage {
		
	public function __construct($file){
		$this->_file = $file;
	}
	
//	/**
//	 * OVERWRITE parent function
//	 * 
//	 * Check to make sure the file is an acceptable file for upload
//	 * throw exception
//	 * 
//	 * @param string $fileType The file type of an image
//	 * 
//	 * @example 'image/jpeg'
//	 * 
//	 * @return boolean returns true/false
//	 */
//	protected function isAcceptableFileType($fileType){
//		$fileCategory = $this->detectFileType($fileType);
//		
//		if($fileCategory == $this->getFileTypeForUpload()){
//			foreach($this->allowedAudioTypes as $allowedType){
//				if($fileType == $allowedType){
//					return true;
//				}
//			}
//		}
//		else if($fileCategory == $this->getFileTypeForUpload()){
//			foreach($this->allowedImageTypes as $allowedType){
//				if($fileType == $allowedType){
//					return true;
//				}
//			}
//		}
//		
//		return false;
//	}

	public function uploadSingleURLImage(){
		
		$startingFilePath = $this->getOriginalFilePath();
		
		$continueWithUpload = true;
		
		if($continueWithUpload){

			$this->resizeImage(0);

			/*
				Creating the file paths to the images
				This will count the number of folders in the directory,
					to get the correct folder to enter,
				then count the number of images in that folder
				and when there are 100 images inside that folder
				a new folder is created with the next number in line
			*/
			$directoryFull = $startingFilePath;
			//
//			if($this->inParentDirectory){
//				$upDirectory = '../';
//			}
//			else {
//				$upDirectory = '';
//			}
			
			if(!file_exists($directoryFull)){
				$this->createUploadingDirectory($directoryFull);
			}
			$this->createImageDirectory($directoryFull);
			
			// Send the Images to the correct place
			$this->createImage($this->_type, $this->_tmp, $this->_tmp1, $this->_tmp2, true);
			
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

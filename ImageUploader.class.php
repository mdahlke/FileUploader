<?php 

/**
 * ImageUploader will create an object that will allow
 * for easy implementation of uploading an image
 * 
 * This image uploader will allow for multiple images to be uploaded at once
 * The uploader will also make two copies of the image (small, thumb)
 *	that will be uploaded to their respective folder within the same parent folder
 *	as the original image
 *
 * 
 * IMPORTANT
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
 * @author Michael
 */
class ImageUploader {
	
	// Variables
	private $originalFilePath				= 'images/full/';
	private $fileNameFull					= array();
	private $fileNameSmall					= array();
	private $fileNameThumb					= array();
	private $filePathFull					= array();
	private $filePathSmall					= array();
	private $filePathThumb					= array();
	private $smallImageWidth				= 500;
	private $smallImageHeight				= 500;
	private $thumbImageWidth				= 200;
	private $thumbImageHeight				= 200;
	private $fullImageQuality				= 90;
	private $smallImageQuality				= 75;
	private $thumbImageQuality				= 65;
	private $prependToFileName				= "";
	private $numberOfFilesPerDirectory		= 100;
	private $numberOfAllowedImagesToUpload	= 1;
	private $maxUploadSize					= 5242880; // 5MB
	
	// Read only variables
	private $numberOfSuccessfulUploads		= 0;
	private $numberOfErrors					= 0;
	private $userReadableUploadErrorMessages= array();
	private $userReadableUploadErrorCodes	= array();
	private $allUploadErrorMessages			= array();
	private $allUploadErrorCodes			= array();
	
	// Static variables
	public static $allowGIFImages = true;
	
	// Constants
	const JPG = 'jpg';
	const PNG = 'png';
	const GIF = 'gif';
	
	
	/*
	 * Getters and Setters
	 */	
	public function getOriginalFilePath(){
		return $this->originalFilePath;
	}
	public function setOriginalFilePath($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}
		if(!file_exists($input)){
			$input = $this->createUploadingDirectory($input);
		}
		
		$subFolders = explode('/', $input);
		$subFoldersCount = count($subFolders) - 2;
		$lastSubFolder = $subFolders[$subFoldersCount];
		
		if(strtolower($lastSubFolder) !== 'full'){
			$input .= 'full/';
		}
		$this->originalFilePath = $input;
	}
	
	public function getFileNameFull(){
		return $this->fileNameFull;
	}
	public function setFileNameFull($input){
		$this->fileNameFull[] = $input;
	}
	
	public function getFileNameSmall(){
		return $this->fileNameSmall;
	}
	public function setFileNameSmall($input){
		$this->fileNameSmall[] = $input;
	}

	public function getFileNameThumb(){
		return $this->fileNameThumb;
	}
	public function setFileNameThumb($input){
		$this->fileNameThumb[] = $input;
	}

	public function getFilePathFull(){
		return $this->filePathFull;
	}
	public function setFilePathFull($input){
		$this->filePathFull[] = $input;
	}

	public function getFilePathSmall(){
		return $this->filePathSmall;
	}
	public function setFilePathSmall($input){
		$this->filePathSmall[] = $input;
	}

	public function getFilePathThumb(){
		return $this->filePathThumb;
	}
	public function setFilePathThumb($input){
		$this->filePathThumb[] = $input;
	}
	
	public function getSmallImageWidth(){
		return $this->smallImageWidth;
	}
	public function setSmallImageWidth($input){
		$this->smallImageWidth = $input;
	}
	
	public function getSmallImageHeight(){
		return $this->smallImageHeight;
	}
	public function setSmallImageHeight($input){
		$this->smallImageheight = $input;
	}
	
	public function getThumbImageWidth(){
		return $this->smallImageWidth;
	}
	public function setThumbImageWidth($input){
		$this->smallImageWidth = $input;
	}
	
	public function getThumbImageHeight(){
		return $this->thumbImageHeight;
	}
	public function setThumbImageHeight($input){
		$this->thumbImageHeight = $input;
	}
	
	public function getFullImageQuality(){
		return $this->fullImageQuality;
	}
	public function setFullImageQuality($input){
		$this->fullImageQuality = $input;
	}
	
	public function getSmallImageQuality(){
		return $this->smallImageQuality;
	}
	public function setSmallImageQuality($input){
		$this->smallImageQuality = $input;
	}
	
	public function getThumbImageQuality(){
		return $this->thumbImageQuality;
	}
	public function setThumbImageQuality($input){
		$this->thumbImageQuality = $input;
	}
	
	public function getPrependToFileName(){
		return $this->prependToFileName;
	}
	public function setPrependToFileName($input){
		$this->prependToFileName = $input.'_';
	}
	
	public function getNumberOfAllowedImagesToUpload(){
		return $this->numberOfAllowedImagesToUpload;
	}
	public function setNumberOfAllowedImagesToUpload($input){
		$this->numberOfAllowedImagesToUpload = $input;
	}
	
	public function getNumberOfFilesPerDirectory() {
		return $this->numberOfFilesPerDirectory;
	}
	public function setNumberOfFilesPerDirectory($input){
		$this->numberOfFilesPerDirectory = $input;
	}
	
	public function getNumberOfSuccessfulUploads(){
		return $this->numberOfSuccessfulUploads;
	}
	
	public function getMaxUploadSize(){
		return $this->maxUploadSize;
	}
	public function setMaxUploadSize(int $input){
		$this->maxUploadSize = $input;
	}
	
	/*
	 * Set the desired height and width of the small and thumbnail images
	 * in two functions instead of using the setters
	 */
	public function setSmallImageWidthAndHeight($w, $h){
		$this->setSmallImageWidth($w);
		$this->setSmallImageHeight($h);
	}
	public function setThumbImageWidthAndHeight($w, $h){
		$this->setThumbImageWidth($w);
		$this->setThumbImageHeight($h);
	}
	
	/*
	 * return FileName(Full/Small/Thumb) at index number
	 */
	public function getFileNameFullAtIndex($index){
		return $this->fileNameFull[$index];
	}
	public function getFileNameSmallAtIndex($index){
		return $this->fileNameSmall[$index];
	}
	public function getFileNameThumbAtIndex($index){
		return $this->fileNameThumb[$index];
	}
	/*
	 * return all fileNames
	 */
	public function echoAllFileNames(){
		echo "<pre>";
		print_r($this->fileNameFull);
		print_r($this->fileNameSmall);
		print_r($this->fileNameThumb);
		echo "</pre>";
	}
	
	/*
	 * return FilePath(Full/Small/Thumb) at index number
	 */
	public function getFilePathFullAtIndex($index){
		return $this->filePathFull[$index];
	}
	public function getFilePathSmallAtIndex($index){
		return $this->filePathSmall[$index];
	}
	public function getFilePathThumbAtIndex($index){
		return $this->filePathThumb[$index];
	}
	/*
	 * return all filePaths
	 */
	public function echoAllPathNames(){
		echo "<pre>";
		print_r($this->filePathFull);
		print_r($this->filePathSmall);
		print_r($this->filePathThumb);
		echo "</pre>";
	}
	

	/*
	 * Check to make sure the file is an image
	 * 
	 * throw exception
	 * 
	 * return true/false
	 */
	public function isImage($fileType){
		if (($fileType != NULL)
		&& ($fileType != 'image/gif')
		&& ($fileType != 'image/png')
		&& ($fileType != 'image/jpeg')
		&& ($fileType != 'image/jpg')
		&& ($fileType != 'image/pjpeg')){
			
			return false;
		}
		
		return true;
	}
	
	/*
	 * Check to make sure the file isn't over the maximum allowed filesize
	 * 
	 * Max Allowed Filesize: 5MB
	 * 
	 * throw exception
	 * 
	 * return true/false
	 */
	public function imageSmallEnoughForUpload($fileSize){
		if($fileSize > $this->getMaxUploadSize()){			
			return false;
		}

		return true;		
	}
	
	/*
	 * Check that there is indeed an image present
	 * 
	 * throw exception
	 * 
	 * return true/false
	 */
	public function imageIsNotEmpty($fileSize){
		if($fileSize <= 0){
			throw new Exception('Image is empty.', 1003);
			
			return false;
		}
		
		return true;
	}
	
	/*
	 * Create a standard MySQL query for quick access to a query
	 * 
	 * This may not suit everyone ones needs so it may be ignored
	 * 
	 * The order of the query goes as follows.....
	 * (
	 *	fileNameFull, fileNameSmall, fileNameThumb,
	 *	filePathFull, filePathSmall, filePathThumb
	 * )
	 * 
	 * These functions have been "overloaded" to allow for one or two more fields
	 *		to be applied to the beginning of the query so it could be something
	 *		like the following....
	 *	(
	 *	primaryKey, fileNameFull, fileNameSmall, fileNameThumb,
	 *	filePathFull, filePathSmall, filePathThumb
	 *	)
	 * or....
	 *	(
	 *	primaryKey, userID, fileNameFull, fileNameSmall, fileNameThumb,
	 *	filePathFull, filePathSmall, filePathThumb
	 *	)
	 */
	public function returnMysqlQuery(){
		$q = '';
		if(func_num_args() === 0){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFileNameSmallAtIndex($i).'",
						"'.$this->getFileNameThumbAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'",
						"'.$this->getFilePathSmallAtIndex($i).'", "'.$this->getFilePathThumbAtIndex($i).'"
						),';
			}
		}
		else if(func_num_args() === 1){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.func_get_arg(0).'", 
						"'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFileNameSmallAtIndex($i).'",
						"'.$this->getFileNameThumbAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'",
						"'.$this->getFilePathSmallAtIndex($i).'", "'.$this->getFilePathThumbAtIndex($i).'"
						),';
			}
		}
		else if(func_num_args() === 2){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.func_get_arg(0).'", "'.func_get_arg(1).'", 
						"'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFileNameSmallAtIndex($i).'",
						"'.$this->getFileNameThumbAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'",
						"'.$this->getFilePathSmallAtIndex($i).'", "'.$this->getFilePathThumbAtIndex($i).'"
						),';
			}
		}
		
		return substr($q, 0, -1);
	}
	
	/*
	 * return the number of errors that occured during upload
	 */
	public function getNumberOfErrorsDuringUpload(){
		return $this->numberOfErrors;
	}
	public function returnUserReadableUploadErrorMessages(){
		return $this->userReadableUploadErrorMessages;
	}
	public function returnUserReadableUploadErrorCodes(){
		return $this->userReadableUploadErrorCodes;
	}
	public function returnAllUploadErrorMessages(){
		return $this->allUploadErrorMessages;
	}
	public function returnAllUploadErrorCodes(){
		return $this->allUploadErrorCodes;
	}
	
	/*
	 * Creates the uploading directory and all children directories
	 *  if they do not exist
	 */
	public function createUploadingDirectory($input){
		$filePathDirectories = explode('/', $input);
		$filePathDirectoriesCount = count($filePathDirectories) - 2;
		$lastNestedFolder = $filePathDirectories[$filePathDirectoriesCount];		

		if(strtolower($lastNestedFolder) !== 'full'){
			$input .= 'full/';
		}
		if(!file_exists($input)){
			
			try {
				mkdir($input.'1/', 0777, true);
				throw new Exception('Failed to make directory.', '2001');
				return;
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
			}
			try {
				mkdir(str_replace('full/', 'small/1/', $input), 0777, true);
				throw new Exception('Failed to make directory.', '2001');
				return;
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
			}
			try {
				mkdir(str_replace('full/', 'thumbnail/1/', $input), 0777, true);
				throw new Exception('Failed to make directory.', '2001');
				return;
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
			}
			
		}
		return $input;
	}

	/*
	 * Image uploader magic is done here!
	 * 
	 * we take an image and then call the above functions to verify
	 * the file is indeed an image and it is not too large to be uploaded
	 * 
	 */
	public function uploadImage($file){		
		$startingFilePath = $this->getOriginalFilePath();
		
		try {
			if(!is_array($file['name'])){
				throw new Exception('Input name must be an array ex. &lt;input type="file" name="image[]"/&gt;', '3001');
			}
		}
		catch (Exception $e){
			echo 'ERROR '. $e->getCode() .': '. $e->getMessage();
			exit();
		}
		
		for($i = 0; $i < $this->getNumberOfAllowedImagesToUpload(); $i++){
			$continueWithUpload = true;

			try {
				if(!$this->isImage($file['type'][$i])){
					throw new Exception($file['name'][$i] . ' is not an image so it was not uploaded.',  1001);
				}
			}
			catch(Exception $e) {
				$this->numberOfErrors += 1;			
				$this->userReadableUploadErrorMessages[] = $e->getMessage();
				$this->userReadableUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$continueWithUpload = false;
			}
			
			try {
				if(!$this->imageSmallEnoughForUpload($file['size'][$i])){
					throw new Exception($file['name'][$i] . ' is too large.', 1002);
				}
				
			}
			catch(Exception $e) {
				$this->numberOfErrors += 1;
				$this->userReadableUploadErrorMessages[] = $e->getMessage();
				$this->userReadableUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$continueWithUpload = false;
			}
			
			try {
				if(!$this->imageIsNotEmpty($file['size'][$i])){
					throw new Exception($file['name'] .' is empty.', '1003');
				}
			}
			catch(Exception $e){
				$continueWithUpload = false;
			}
			
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
					$imageType = 'gif';
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

					mkdir($newSubDirectory."/", 0777);												// We then create the 3 new sub directories in their respective
					mkdir($directorySmall."/", 0777);												// Parent directory and make the read/write
					mkdir($directoryThumb."/", 0777);

					$directoryFull = $newSubDirectory."/";
				}
				else {																				// If there are not 100 files in that folder yet we continue
					$directoryFull .= $sub_directories_count."/";
					$directorySmall = str_replace("full", "small", $directoryFull);					// /* To create a directory for the small images and thumb images
					$directoryThumb = str_replace("full", "thumbnail", $directoryFull);				//    I used the str_replace() function */
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
					echo $e->getMessage();
				}
				try {
					$this->setFileNameThumb($thumbImageWidth."x".$thumbImageHeight."_".$fullFileName);
				}
				catch (Exception $e){
					echo $e->getMessage();
				}

				// Send the Images to the correct place
				if($imageType === $this::GIF && self::$allowGIFImages){
					try {
						if(!imagegif($tmp, $directoryFull.$this->getFileNameFullAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e) {
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
					
					try {
						if(!imagegif($tmp, $directorySmall.$this->getFileNameSmallAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
					try {
						if(!imagegif($tmp, $directoryThumb.$this->getFileNameSmallAtIndex($u))){
							throw new Exception('Could not create "imagegif". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1003);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
				}
				elseif($imageType === $this::JPG || $imageType === $this::PNG){
					try {
						if(!imagejpeg($tmp,$directoryFull.$this->getFileNameFullAtIndex($u), $this->getFullImageQuality())){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
					
					try {
						if(!imagejpeg($tmp1,$directorySmall.$this->getFileNameSmallAtIndex($u), $this->getSmallImageQuality())){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
					
					try {
						if(!imagejpeg($tmp2,$directoryThumb.$this->getFileNameThumbAtIndex($u), $this->getThumbImageQuality())){
							throw new Exception('Could not create "imagejpeg". 
												Filepath: '.$this->getFilePathFullAtIndex($u). ' or 
												Filename: '.$this->getFileNameFullAtIndex($u).' does not exist.', 1004);
						}
					}
					catch(Exception $e){
						$this->allUploadErrorMessages[] = $e->getMessage();
						$this->allUploadErrorCodes[] = $e->getCode();
						echo $e->getMessage();
					}
				}
				// Destroy temporary images
				imagedestroy($src);
				imagedestroy($tmp);
				imagedestroy($tmp1);
				
				$this->numberOfSuccessfulUploads = $this->getNumberOfSuccessfulUploads() + 1;

			} // end if(....);
			
		} // for() loop;

	} // end imageUploader
	
	
	
} // end class

?>
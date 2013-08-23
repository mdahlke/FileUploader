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
require ('FileUploader.class.php');
require ('interfaces/iFileUpload.php');

class UploadImage extends FileUploader implements iFileUpload {
	protected $fileNameFull			= array();
	protected $fileNameSmall		= array();
	protected $fileNameThumb		= array();
	protected $filePathFull			= array();
	protected $filePathSmall		= array();
	protected $filePathThumb		= array();
	protected $inParentDirectory	= false;
	protected $smallImageWidth		= 500;
	protected $smallImageHeight		= 500;
	protected $thumbImageWidth		= 200;
	protected $thumbImageHeight		= 200;
	protected $fullImageQuality		= 90;
	protected $smallImageQuality	= 75;
	protected $thumbImageQuality	= 65;
	
	protected $fileTypeForUpload	= 'image';

	// Static variables
	protected static $allowGIFImages = true;
	
	// Constants
	const JPG = 'jpg';
	const PNG = 'png';
	const GIF = 'gif';

	/**
	 * Getters and Setters
	 */	
	public function getOriginalFilePath(){
		return $this->originalFilePath;
	}
	public function setOriginalFilePath($input){
		if($this->inParentDirectory){
			$input = '../'.$input;
		}
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}	
		$subFolders = explode('/', $input);
		$subFoldersCount = count($subFolders) - 2;
		$lastSubFolder = $subFolders[$subFoldersCount];
		
		if(strtolower($lastSubFolder) !== 'full'){
			$input .= 'full/';
		}		
		if(!file_exists($input)){
			$input = $this->createUploadingDirectory($input);
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
	protected function setFilePathFull($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}
		$this->filePathFull[] = $input;
	}

	public function getFilePathSmall(){
		return $this->filePathSmall;
	}
	protected function setFilePathSmall($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}
		$this->filePathSmall[] = $input;
	}

	public function getFilePathThumb(){
		return $this->filePathThumb;
	}
	protected function setFilePathThumb($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}
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
		$this->smallImageHeight = $input;
	}
	
	public function getThumbImageWidth(){
		return $this->thumbImageWidth;
	}
	public function setThumbImageWidth($input){
		$this->thumbImageWidth = $input;
	}
	
	public function getThumbImageHeight(){
		return $this->thumbImageHeight;
	}
	public function setThumbImageHeight($input){
		$this->thumbImageHeight = $input;
	}
	
	public function getFullImageQuality($imageType){
		if($imageType == $this::PNG){
			return substr($this->fullImageQuality, 0, 1);
		}
		return $this->fullImageQuality;
	}
	public function setFullImageQuality($input){
		$this->fullImageQuality = $input;
	}
	
	public function getSmallImageQuality($imageType){
		if($imageType == $this::PNG){
			return substr($this->smallImageQuality, 0, 1);
		}
		return $this->smallImageQuality;
	}
	public function setSmallImageQuality($input){
		$this->smallImageQuality = $input;
	}
	
	public function getThumbImageQuality($imageType){
		if($imageType == $this::PNG){
			return substr($this->thumbImageQuality, 0, 1);
		}
		return $this->thumbImageQuality;
	}
	public function setThumbImageQuality($input){
		$this->thumbImageQuality = $input;
	}

	/**
	 * Set the desired height and width of the small images
	 * in two functions instead of using the setters
	 * 
	 * @param int $w Desired width of small image
	 * @param int $h Desired height of small image
	 */
	public function setSmallImageWidthAndHeight($w, $h){
		$this->setSmallImageWidth($w);
		$this->setSmallImageHeight($h);
	}
	/**
	 * Set the desired height and width of the thumb images
	 * in two functions instead of using the setters
	 * 
	 * @param int $w Desired width of thumb image
	 * @param int $h Desired height of thumb image
	 */
	public function setThumbImageWidthAndHeight($w, $h){
		$this->setThumbImageWidth($w);
		$this->setThumbImageHeight($h);
	}
	
	/**
	 * 
	 * @param int $index Desired fileNameFull index to return
	 * @return string returns fileNameFull at desired index
	 */
	public function getFileNameFullAtIndex($index){
		return $this->fileNameFull[$index];
	}
	/**
	 * 
	 * @param int $index Desired fileNameSmall index to return
	 * @return string returns fileNameSmall at desired index
	 */
	public function getFileNameSmallAtIndex($index){
		return $this->fileNameSmall[$index];
	}
	/**
	 * 
	 * @param int $index Desired fileNameThumb index to return
	 * @return string returns fileNameThumb at desired index
	 */
	public function getFileNameThumbAtIndex($index){
		return $this->fileNameThumb[$index];
	}
	
	/**
	 * @return string returns all file names of images
	 */
	public function echoAllFileNames(){
		echo "<pre>";
		print_r($this->fileNameFull);
		print_r($this->fileNameSmall);
		print_r($this->fileNameThumb);
		echo "</pre>";
	}
	
	/**
	 * @param int $index desired index of filePathfull
	 * @return string FilePathFull at index number
	 */
	public function getFilePathFullAtIndex($index){
		return $this->filePathFull[$index];
	}
	/**
	 * @param int $index desired index of filePathSmall
	 * @return string FilePathSmall at index number
	 */
	public function getFilePathSmallAtIndex($index){
		return $this->filePathSmall[$index];
	}
	/**
	 * @param int $index desired index of filepathThumb
	 * @return string FilePathThumb at index number
	 */
	public function getFilePathThumbAtIndex($index){
		return $this->filePathThumb[$index];
	}
	/**
	 * @return string return all filePaths
	 */
	public function echoAllPathNames(){
		echo "<pre>";
		print_r($this->filePathFull);
		print_r($this->filePathSmall);
		print_r($this->filePathThumb);
		echo "</pre>";
	}
	/**
	 * Creates the uploading directory and all children directories
	 *  if they do not exist
	 * 
	 * @param string $input File path for the start of the uploading directory
	 * 
	 * @return string returns the full folder file path for the uploading directory
	 */
	protected function createUploadingDirectory($input){
		$filePathDirectories = explode('/', $input);
		$filePathDirectoriesCount = count($filePathDirectories) - 2;
		$lastNestedFolder = $filePathDirectories[$filePathDirectoriesCount];		

		if(strtolower($lastNestedFolder) !== 'full'){
			$input .= 'full/';
		}
		if(!file_exists($input)){
			
			try {
				if(!mkdir($input.'1/', 0777, true)){
					throw new Exception('Failed to make directory.', '2001');
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if(!mkdir(str_replace('full/', 'small/1/', $input), 0777, true)){
					throw new Exception('Failed to make directory.', '2001');
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if(!mkdir(str_replace('full/', 'thumbnail/1/', $input), 0777, true)){
					throw new Exception('Failed to make directory.', '2001');
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			
		}
		return $input;
	}
	
	/**
	 * Retain the image transparency for gif and png images
	 * 
	 * @param string $image image resource
	 */
	protected function keepImageTransparency($image){
			imagealphablending($image, false);
			
			try {
				if(!imagesavealpha($image, true)){
					throw new Exception('Image alpha could not be saved', 1005);
				}
			}
			catch(Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
	}
	
	/**
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
	 * 
	 * @return string returns a MySQL formatted query
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
	
	/**
	 * @return string returns a MySQL query for an image uploaded from a URL
	 */
	public function returnURLMySQLQuery(){
		$q = "";
		if(func_num_args() === 0){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'"),';
			}
		}
		else if(func_num_args() === 1){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.func_get_arg(0).'", "'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'"),';
			}
		}
		else if(func_num_args() === 2){
			for($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++){
				$q .= '("'.func_get_arg(0).'", "'.func_get_arg(1).'", "'.$this->getFileNameFullAtIndex($i).'", "'.$this->getFilePathFullAtIndex($i).'"),';
			}
		}
		
		return substr($q, 0, -1);
	}
	
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
<?php

/*

$fileName = array(
	cover = array(
		height = 2600,
		width = 1980,
		quality = 85
	),
	full = array(
		height = 1920,
		width = 1080,
		quality = 85
	),
	small = array(
		height = 500,
		width = 500,
		quality = 75
	),
	thumb = array(
		height = 250,
		width = 250,
		quality = 75
	),
	icon = array(
		height = 50,
		width = 50,
		quality = 100
	),
	
)

*/
/**
 * Description of ImageUploader
 *
 * @author Michael Dahlke <Michael.Dahlke at RummageCity.com>
 */
require ( __DIR__ . '/FileUploader.class.php');
require ( __DIR__ . '/../interfaces/iFileUpload.php');


/*
 * 
 * 
 * Implement " exif_imagetype() " to check if image
 * 
 * 
 */



class ImageUploader extends FileUploader implements iFileUpload {

	protected $_file;
	protected $_type;
	protected $_mime;
	protected $_width;	
	protected $_height;	
	protected $_smallWidth;
	protected $_smallHeight;
	protected $_thumbWidth;
	protected $_thumbHeight;
	protected $_tmp;
	protected $_tmp1;
	protected $_tmp2;
	protected $originalFilePath = 'upload/Images';
	protected $fileNameOriginal = array();
	protected $fileNameLarge = array();
	protected $fileNameSmall = array();
	protected $fileNameThumb = array();
	protected $filePathOriginal = array();
	protected $filePathLarge = array();
	protected $filePathSmall = array();
	protected $filePathThumb = array();
	protected $fileTypeForUpload = 'image';
	protected $smallImageWidth = 500;
	protected $smallImageHeight = 500;
	protected $thumbImageWidth = 200;
	protected $thumbImageHeight = 200;
	protected $fullImageQuality = 90;
	protected $smallImageQuality = 75;
	protected $thumbImageQuality = 65;
	// Static variables
	protected static $allowGIFImages = true;

	public function __construct($file) {
		$this->_file = $file;
	}
	// Constants

	const JPG = 'jpg';
	const PNG = 'png';
	const GIF = 'gif';

	/**
	 * Getters and Setters
	 */
	public function getOriginalFilePath() {
		return $this->originalFilePath;
	}

	public function setOriginalFilePath($input) {
		if ($this->inParentDirectory) {
			$input = '../' . $input;
		}
		if (substr($input, -1) !== '/') {
			$input .= '/';
		}
		$subFolders = explode('/', $input);
		$subFoldersCount = count($subFolders) - 2;
		$lastSubFolder = $subFolders[$subFoldersCount];

		if (strtolower($lastSubFolder) !== 'full') {
			$input .= 'full/';
		}
		if (!file_exists($input)) {
			$input = $this->createUploadingDirectory($input);
		}

		$this->originalFilePath = $input;
	}

	public function getFileNameFull() {
		return $this->fileNameFull;
	}

	public function setFileNameFull($input) {
		$this->fileNameFull[] = $input;
	}

	public function getFileNameSmall() {
		return $this->fileNameSmall;
	}

	public function setFileNameSmall($input) {
		$this->fileNameSmall[] = $input;
	}

	public function getFileNameThumb() {
		return $this->fileNameThumb;
	}

	public function setFileNameThumb($input) {
		$this->fileNameThumb[] = $input;
	}

	public function getFilePathFull() {
		return $this->filePathFull;
	}

	protected function setFilePathFull($input) {
		if (substr($input, -1) !== '/') {
			$input .= '/';
		}
		$this->filePathFull[] = $input;
	}

	public function getFilePathSmall() {
		return $this->filePathSmall;
	}

	protected function setFilePathSmall($input) {
		if (substr($input, -1) !== '/') {
			$input .= '/';
		}
		$this->filePathSmall[] = $input;
	}

	public function getFilePathThumb() {
		return $this->filePathThumb;
	}

	protected function setFilePathThumb($input) {
		if (substr($input, -1) !== '/') {
			$input .= '/';
		}
		$this->filePathThumb[] = $input;
	}

	public function getSmallImageWidth() {
		return $this->smallImageWidth;
	}

	public function setSmallImageWidth($input) {
		$this->smallImageWidth = $input;
	}

	public function getSmallImageHeight() {
		return $this->smallImageHeight;
	}

	public function setSmallImageHeight($input) {
		$this->smallImageHeight = $input;
	}

	public function getThumbImageWidth() {
		return $this->thumbImageWidth;
	}

	public function setThumbImageWidth($input) {
		$this->thumbImageWidth = $input;
	}

	public function getThumbImageHeight() {
		return $this->thumbImageHeight;
	}

	public function setThumbImageHeight($input) {
		$this->thumbImageHeight = $input;
	}

	public function getFullImageQuality($imageType) {
		if ($imageType == $this::PNG) {
			return substr($this->fullImageQuality, 0, 1);
		}
		return $this->fullImageQuality;
	}

	public function setFullImageQuality($input) {
		$this->fullImageQuality = $input;
	}

	public function getSmallImageQuality($imageType) {
		if ($imageType == $this::PNG) {
			return substr($this->smallImageQuality, 0, 1);
		}
		return $this->smallImageQuality;
	}

	public function setSmallImageQuality($input) {
		$this->smallImageQuality = $input;
	}

	public function getThumbImageQuality($imageType) {
		if ($imageType == $this::PNG) {
			return substr($this->thumbImageQuality, 0, 1);
		}
		return $this->thumbImageQuality;
	}

	public function setThumbImageQuality($input) {
		$this->thumbImageQuality = $input;
	}

	/**
	 * Set the desired height and width of the small images
	 * in two functions instead of using the setters
	 * 
	 * @param int $w Desired width of small image
	 * @param int $h Desired height of small image
	 */
	public function setSmallImageWidthAndHeight($w, $h) {
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
	public function setThumbImageWidthAndHeight($w, $h) {
		$this->setThumbImageWidth($w);
		$this->setThumbImageHeight($h);
	}

	/**
	 * 
	 * @param int $index Desired fileNameFull index to return
	 * @return string returns fileNameFull at desired index
	 */
	public function getFileNameFullAtIndex($index) {
		return $this->fileNameFull[$index];
	}

	/**
	 * 
	 * @param int $index Desired fileNameSmall index to return
	 * @return string returns fileNameSmall at desired index
	 */
	public function getFileNameSmallAtIndex($index) {
		return $this->fileNameSmall[$index];
	}

	/**
	 * 
	 * @param int $index Desired fileNameThumb index to return
	 * @return string returns fileNameThumb at desired index
	 */
	public function getFileNameThumbAtIndex($index) {
		return $this->fileNameThumb[$index];
	}

	/**
	 * @return string returns all file names of images
	 */
	public function echoAllFileNames() {
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
	public function getFilePathFullAtIndex($index) {
		return $this->filePathFull[$index];
	}

	/**
	 * @param int $index desired index of filePathSmall
	 * @return string FilePathSmall at index number
	 */
	public function getFilePathSmallAtIndex($index) {
		return $this->filePathSmall[$index];
	}

	/**
	 * @param int $index desired index of filepathThumb
	 * @return string FilePathThumb at index number
	 */
	public function getFilePathThumbAtIndex($index) {
		return $this->filePathThumb[$index];
	}

	/**
	 * @return string return all filePaths
	 */
	public function echoAllPathNames() {
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
	protected function createUploadingDirectory($input) {
		$filePathDirectories = explode('/', $input);
		$filePathDirectoriesCount = count($filePathDirectories) - 2;
		$lastNestedFolder = $filePathDirectories[$filePathDirectoriesCount];

		if (strtolower($lastNestedFolder) !== 'original') {
			$input .= 'original/';
		}
		if (!file_exists($input)) {

			try {
				if (!mkdir($input . '1/', 0777, true)) {
					throw new Exception('Failed to make directory.', '2001');
				}
			} catch (Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if (!mkdir(str_replace('original/', 'large/1/', $input), 0777, true)) {
					throw new Exception('Failed to make directory.', '2001');
				}
			} catch (Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if (!mkdir(str_replace('original/', 'small/1/', $input), 0777, true)) {
					throw new Exception('Failed to make directory.', '2001');
				}
			} catch (Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if (!mkdir(str_replace('original/', 'thumbnail/1/', $input), 0777, true)) {
					throw new Exception('Failed to make directory.', '2001');
				}
			} catch (Exception $e) {
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
	protected function keepImageTransparency($image) {
		imagealphablending($image, false);

		try {
			if (!imagesavealpha($image, true)) {
				throw new Exception('Image alpha could not be saved', 1005);
			}
		} catch (Exception $e) {
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
	 * 	fileNameFull, fileNameSmall, fileNameThumb,
	 * 	filePathFull, filePathSmall, filePathThumb
	 * )
	 * 
	 * These functions have been "overloaded" to allow for one or two more fields
	 * 		to be applied to the beginning of the query so it could be something
	 * 		like the following....
	 * 	(
	 * 	primaryKey, fileNameFull, fileNameSmall, fileNameThumb,
	 * 	filePathFull, filePathSmall, filePathThumb
	 * 	)
	 * or....
	 * 	(
	 * 	primaryKey, userID, fileNameFull, fileNameSmall, fileNameThumb,
	 * 	filePathFull, filePathSmall, filePathThumb
	 * 	)
	 * 
	 * @return string returns a MySQL formatted query
	 */
	public function returnMysqlQuery() {
		$q = '';
		if (func_num_args() === 0) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFileNameSmallAtIndex($i) . '",
						"' . $this->getFileNameThumbAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '",
						"' . $this->getFilePathSmallAtIndex($i) . '", "' . $this->getFilePathThumbAtIndex($i) . '"
						),';
			}
		} else if (func_num_args() === 1) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . func_get_arg(0) . '", 
						"' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFileNameSmallAtIndex($i) . '",
						"' . $this->getFileNameThumbAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '",
						"' . $this->getFilePathSmallAtIndex($i) . '", "' . $this->getFilePathThumbAtIndex($i) . '"
						),';
			}
		} else if (func_num_args() === 2) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . func_get_arg(0) . '", "' . func_get_arg(1) . '", 
						"' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFileNameSmallAtIndex($i) . '",
						"' . $this->getFileNameThumbAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '",
						"' . $this->getFilePathSmallAtIndex($i) . '", "' . $this->getFilePathThumbAtIndex($i) . '"
						),';
			}
		}

		return substr($q, 0, -1);
	}

	/**
	 * @return string returns a MySQL query for an image uploaded from a URL
	 */
	public function returnURLMySQLQuery() {
		$q = "";
		if (func_num_args() === 0) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '"),';
			}
		} else if (func_num_args() === 1) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . func_get_arg(0) . '", "' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '"),';
			}
		} else if (func_num_args() === 2) {
			for ($i = 0; $i < $this->getNumberOfSuccessfulUploads(); $i++) {
				$q .= '("' . func_get_arg(0) . '", "' . func_get_arg(1) . '", "' . $this->getFileNameFullAtIndex($i) . '", "' . $this->getFilePathFullAtIndex($i) . '"),';
			}
		}

		return substr($q, 0, -1);
	}
	
	public function resizeImage($index){
		$this->_type = 'jpg';
		
		if(is_array($this->_file)){
			$uploadedFile = $this->_file['tmp_name'][$index];
		}
		else {
			$uploadedFile = $this->_file;
		}
		$properties = getimagesize($uploadedFile);
				
		$this->_width = $properties[0];
		$this->_height = $properties[1];
		$this->_mime = $properties['mime'];

		// Check image type and then create jpeg of it
		if($this->_mime === 'image/jpg' || $this->_mime === 'image/jpeg' || $this->_mime === 'image/pjpeg') {
			$src = imagecreatefromjpeg($uploadedFile);
		}
		else if($this->_mime === 'image/png') {
			$this->_type = 'png';
			$src = imagecreatefrompng($uploadedFile);
		}
		else if($this->_mime === 'image/gif') {
			$this->_type = 'png';
			$src = imagecreatefromgif($uploadedFile);
		}		
		
		if($this->_width > $this->_height){
			$ratioOfDifference = $this->getSmallImageWidth() / $this->_width;

			$this->_smallWidth = $ratioOfDifference * $this->_width;
			$this->_smallHeight = $ratioOfDifference * $this->_height;
		}
		else {
			$ratioOfDifference = $this->getSmallImageHeight() / $this->_height;

			$this->_smallWidth = $ratioOfDifference * $this->_width;
			$this->_smallHeight = $ratioOfDifference * $this->_height;
		}

		if($this->_width > $this->_height){
			$ratioOfDifference = $this->getThumbImageWidth() / $this->_width;
			$this->_thumbWidth = $ratioOfDifference * $this->_width;
			$this->_thumbHeight = $ratioOfDifference * $this->_height;
		}
		else {
			$ratioOfDifference = $this->getThumbImageHeight() / $this->_height;

			$this->_thumbWidth = $ratioOfDifference * $this->_width;
			$this->_thumbHeight = $ratioOfDifference * $this->_height;
		}

		// Original
		$this->_tmp = imagecreatetruecolor($this->_width,$this->_height);

		// Small
		$this->_tmp1 = imagecreatetruecolor($this->_smallWidth,$this->_smallHeight);

		// Thumbnail
		$this->_tmp2 = imagecreatetruecolor($this->_thumbWidth,$this->_thumbHeight);

		$this->keepImageTransparency($this->_tmp);
		$this->keepImageTransparency($this->_tmp1);
		$this->keepImageTransparency($this->_tmp2);

		// Copy the images with the new $width & $height
		imagecopyresampled($this->_tmp,$src,0,0,0,0,$this->_width,$this->_height,$this->_width,$this->_height);
		imagecopyresampled($this->_tmp1,$src,0,0,0,0,$this->_smallWidth,$this->_smallHeight,$this->_width,$this->_height);
		imagecopyresampled($this->_tmp2,$src,0,0,0,0,$this->_thumbWidth,$this->_thumbHeight,$this->_width,$this->_height);

	}
		
	public function createImageDirectory($directoryFull, $index){
		$glob  = glob( realpath( $directoryFull ) . '/*' );					

		$sub_directories = array_filter( $glob, 'is_dir' );				
		$sub_directories_count = count($sub_directories);									// Counts the directories in the file path

		$sub_directory = $sub_directories_count;											// The count of the directories is going to be the name
																							// of our sub folder
		$filecount = count(glob($directoryFull.$sub_directory."*/*"));						// Count the number of files in the sub directory

		if($filecount >= $this->getNumberOfFilesPerDirectory()){							// When there are 100 files in that sub directory we create a new on
			$sub_directories_count++;														// We then add 1 to the old sub directory (it is a number)
			$newSubDirectory = $directoryFull.$sub_directories_count;						// The new sub directory will be the old directory + 1
			$directorySmall = str_replace("full/", "small/", $newSubDirectory);				// To create a directory for the small images and thumb images
			$directoryThumb = str_replace("full/", "thumbnail/", $newSubDirectory);			// I used the str_replace() function

			$this->createDirectory($newSubDirectory."/");									// We then create the 3 new sub directories in their respective
			$this->createDirectory($directorySmall."/");									// Parent directory and make the read/write
			$this->createDirectory($directoryThumb."/");

			$directoryFull = $newSubDirectory."/";
		}
		else {																				// If there are not 100 files in that folder yet we continue
			$directoryFull .= $sub_directories_count."/";
			$directorySmall = str_replace("full/", "small/", $directoryFull);				// /* To create a directory for the small images and thumb images
			$directoryThumb = str_replace("full/", "thumbnail/", $directoryFull);			//    I used the str_replace() function */
		}

		/* Here are the 3 new directories! */
		$this->setFilePathFull($directoryFull);
		$this->setFilePathSmall($directorySmall);
		$this->setFilePathThumb($directoryThumb);

		// Create a filename that won't be repeated
		$this->setFileNameFull($this->getPrependToFileName().md5(rand()).'.'.$this->_type);

		$fullFileName = $this->getFileNameFullAtIndex($index);
		
		
		try {
			$this->setFileNameSmall($this->_smallWidth."x".$this->_smallHeight."_".$fullFileName);
		}
		catch(Exception $e){
			$this->allUploadErrorMessages[] = $e->getMessage();
			$this->allUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorsLineNumber[] = $e->getLine();
			$this->allUploadErrorsFileName[] = $e->getFile();
		}
		try {
			$this->setFileNameThumb($this->_thumbWidth."x".$this->_thumbHeight."_".$fullFileName);
		}
		catch (Exception $e){
			$this->allUploadErrorMessages[] = $e->getMessage();
			$this->allUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorsLineNumber[] = $e->getLine();
			$this->allUploadErrorsFileName[] = $e->getFile();
		}

		
	}
	
	public function createImage($imageType, $tmp, $tmp1, $tmp2, $index = false){
		if($index === true){
			$index = 0;
		}
		
		if($imageType === $this::GIF && self::$allowGIFImages){

			try {
				if(!imagegif($tmp, $this->getFilePathFullAtIndex($index).$this->getFileNameFullAtIndex($index))){
					throw new Exception('Could not create "imagegif". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			try {
				if(!imagegif($tmp1, $this->getFilePathSmallAtIndex($index).$this->getFileNameSmallAtIndex($index))){
					throw new Exception('Could not create "imagegif". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if(!imagegif($tmp2, $this->getFilePathThumbAtIndex($index).$this->getFileNameThumbAtIndex($index))){
					throw new Exception('Could not create "imagegif". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
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
				if(!imagepng($tmp, $this->getFilePathFullAtIndex($index).$this->getFileNameFullAtIndex($index), $this->getFullImageQuality($imageType))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			try {
				if(!imagepng($tmp1, $this->getFilePathSmallAtIndex($index).$this->getFileNameSmallAtIndex($index), $this->getSmallImageQuality($imageType))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if(!imagepng($tmp2, $this->getFilePathThumbAtIndex($index).$this->getFileNameThumbAtIndex($index), $this->getThumbImageQuality($imageType))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
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
				if(!imagepng($tmp, $this->getFilePathFullAtIndex($index).$this->getFileNameFullAtIndex($index))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e) {
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			try {
				if(!imagepng($tmp1, $this->getFilePathSmallAtIndex($index).$this->getFileNameSmallAtIndex($index))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
			try {
				if(!imagepng($tmp2, $this->getFilePathThumbAtIndex($index).$this->getFileNameThumbAtIndex($index))){
					throw new Exception('Could not create "imagepng". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1003);
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
				if(!imagejpeg($tmp, $this->getFilePathFullAtIndex($index).$this->getFileNameFullAtIndex($index), $this->getFullImageQuality($imageType))){
					throw new Exception('Could not create "imagejpeg". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1004);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			try {
				if(!imagejpeg($tmp1, $this->getFilePathSmallAtIndex($index).$this->getFileNameSmallAtIndex($index), $this->getSmallImageQuality($imageType))){
					throw new Exception('Could not create "imagejpeg". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1004);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}

			try {
				if(!imagejpeg($tmp2, $this->getFilePathThumbAtIndex($index).$this->getFileNameThumbAtIndex($index), $this->getThumbImageQuality($imageType))){
					throw new Exception('Could not create "imagejpeg". 
										Filepath: '.$this->getFilePathFullAtIndex($index). ' or 
										Filename: '.$this->getFileNameFullAtIndex($index).' does not exist.', 1004);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
		}
		
	}
	

}

?>

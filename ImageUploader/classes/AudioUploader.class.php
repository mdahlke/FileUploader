<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AudioUpload
 *
 * @author Michael
 */

require 'FileUploader.class.php';
class AudioUploader extends FileUploader {
	
	private $fileName = array();
	private $filePath = array();
	private $fileType = array();
	private $originalFilePath = 'uploads';
	private $maxUploadSize = 419430400;
	private $maxNumberOfUploadsPerUpload = 20;
	//private $numberOfFilesPerDirectory = 10;
	
	// Read only variables
	protected $errorLogFileName					= 'uploaderErrorLog.txt';
	protected $numberOfSuccessfulUploads		= 0;
	protected $numberOfErrors					= 0;
	protected $userReadableUploadErrorMessages	= array();
	protected $userReadableUploadErrorCodes		= array();
	protected $allUploadErrorMessages			= array();
	protected $allUploadErrorCodes				= array();
	protected $allUploadErrorsLineNumber		= array();

	/**
	 * Getters and Setters
	 */
	public function getFileName(){
		return $this->fileName;
	}
	public function setFileName($input){
		$this->fileName[] = $input;
	}
	
	public function getFilePath(){
		return $this->filePath;
	}
	public function setFilePath($input){
		$this->filePath[] = $input;
	}
	
	public function getFileType(){
		return $this->fileType;
	}
	public function setFileType($input){
		$this->fileType[] = $input;
	}
	
	public function getFileNameAtIndex($index){
		return $this->fileName[$index];
	}
	public function getFilePathAtIndex($index){
		return $this->filePath[$index];
	}
	public function getFileTypeAtIndex($index){
		return $this->fileType[$index];
	}
	
	public function getOriginalFilePath(){
		return $this->originalFilePath;
	}
	public function setOriginalFilePath($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}	
		if(!file_exists('playlists/'.$input)){
			$folder = $this->createDirectory('playlists/'.$input);
		}
		else {
			$folder = 'playlists/'.$input;
		}

		$this->originalFilePath = $folder;

	}
	
	public function getMaxUploadSize(){
		return $this->maxUploadSize;
	}
	public function setMaxUploadSize($input){
		$this->maxUploadSize = $input;
	}
	
	public function getMaxNumberOfUploadsPerUpload(){
		return $this->maxNumberOfUploadsPerUpload;
	}
	public function setMaxNumberOfUploadsPerUpload($input){
		$this->maxNumberOfUploadsPerUpload = $input;
	}
	
	public function getNumberOfFilesPerDirectory(){
		return $this->numberOfFilesPerDirectory;
	}
	public function setNumberOfFilesPerDirectory($input){
		$this->numberOfFilesPerDirectory = $input;
	}
	
	public function getNumberOfSuccessfulUploads(){
		return $this->numberOfSuccessfulUploads;
	}

/**
 * *****************************************************************************
 *																			   *
 *						ERROR HANDLING										   *
 *																			   *
 * *****************************************************************************
 **/
	public function getErrorLogFilename(){
		return $this->errorLogFileName;
	}
	public function setErrorLogFilename($input){
		$this->errorLogFileName = $input;
	}
	/**
	 * @return int the number of errors that occured during upload
	 */
	public function getNumberOfErrorsDuringUpload(){
		return $this->numberOfErrors;
	}
	/**
	 * @return string error messages that are safe or relevant for a user
	 */
	public function returnUserReadableUploadErrorMessages(){
		return $this->userReadableUploadErrorMessages;
	}
	/**
	 * 
	 * @return string return error codes that are safe or relevant for a user
	 */
	public function returnUserReadableUploadErrorCodes(){
		return $this->userReadableUploadErrorCodes;
	}
	
	/**
	 * @return string return all error messages
	 */
	public function returnAllUploadErrorMessages(){
		return $this->allUploadErrorMessages;
	}
	/**
	 * @return string return all error codes
	 */
	public function returnAllUploadErrorCodes(){
		return $this->allUploadErrorCodes;
	}
	/**
	 * @return string return all error line numbers
	 */
	public function returnAllUploadErrorsLineNumber(){
		return $this->allUploadErrorsLineNumber;
	}
	
	/**
	 * @param int $index index of desired error message
	 * 
	 * @return string an error message at a specific index
	 */
	public function returnUploadErrorMessageAtIndex($index){
		return $this->allUploadErrorMessages[$index];
	} 
	/**
	 * @param int $index index of desired error code
	 * 
	 * @return string an error code at a specific index
	 */
	public function returnUploadErrorCodeAtIndex($index){
		return $this->allUploadErrorCodes[$index];
	} 
	/**
	 * @param int $index index of desired line number
	 * 
	 * @return string a line number at a specific index
	 */
	public function returnAllUploadErrorsLineNumberAtIndex($index){
		return $this->allUploadErrorsLineNumber[$index];
	} 
	
	/**
	 * @return string returns error messages in a nice format
	 */
	public function returnFormattedErrors(){
		$formattedErrors = '';
		
		for($i = 0; $i < $this->getNumberOfErrorsDuringUpload(); $i++){
			$formattedErrors .= "Error ".$this->returnUploadErrorCodeAtIndex($i).
								" on line ".$this->returnAllUploadErrorsLineNumberAtIndex($i).
								" :: ".$this->returnUploadErrorMessageAtIndex($i)."\r\n";
		}
		
		return $formattedErrors;
	}
	
	/**
	 * Write all upload errors to an error log
	 * 
	 */
	protected function writeAllErrorsToLog(){
		$errorLogHandle = $this->getErrorLogFilename();
		
		try {
			if($errorLogHandle === ""){
				throw new Exception('File does not exist.', '4001');
				return;
			}
			else if($errorHandleToLock = fopen($errorLogHandle, 'ab')){
				
				try {
					if(flock($errorHandleToLock, LOCK_EX)){
						
						$errorsToLog = $this->returnFormattedErrors();
						
						try {
							if(!fwrite($errorHandleToLock, $errorsToLog)){
								throw new Exception('Could not write to file.', '4004');
							}
						}
						catch(Exception $e){
							$this->allUploadErrorMessages[] = $e->getMessage();
							$this->allUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorsLineNumber[] = $e->getLine();
						}
						 
					}
					else {
						throw new Exception('File could not be locked.', '4003');
					}
				}
				catch(Exception $e){
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
				}
			}
			else {
				throw new Exception('File could not be opened.', '4002');
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
			}
		}
		catch(Exception $e) {
			$this->numberOfErrors += 1;
			die('ERROR '. $e->getCode() .' on line '. $e->getLine() .' :: '. $e->getMessage());
		}
	}
/**
 * *****************************************************************************
 *																			   *
 *						END ERROR HANDLING									   *
 *																			   *
 * *****************************************************************************
 **/

	/**
	 * count items in a folder
	 * 
	 * @param string $folder path of the folder you want the item count of
	 * 
	 * @return int number of items in the directory
	 */
	protected function countItemsInDirectory($folder){
		$glob  = glob( realpath( $folder ) . '/*' );					

		$sub_directories = array_filter( $glob, 'is_dir' );
		$filecount = count($sub_directories);

		return $filecount;
	}
	/**
	 * 
	 * @param string $folder folder name to be created
	 */
	protected function createDirectory($folder){
		if(!file_exists($folder)){
			try {
				if(!mkdir($folder."/", 0755, true)){
					throw new Exception("Could not make directory: ". $folder, 102);
				}
			}
			catch(Exception $e){
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
			}
		}

		return $folder;
	}
	


	/*
	 * Check the status of the directory
	 * ie - if there is room to add files or not
	 *	  - if there isn't room then we call createDirectory() which will create
	 *		a directory and set the filepath accordingly
	 * @param string $folder path to the folder to check
	 * 
	 */
	protected function checkDirectoryStatus($folder){
		if(!file_exists($folder)){
			$this->createDirectory($folder);
		}
		$this->setFilePath($folder);				

	}
	
	/**
	 * Check if the uploading file already exists
	 * 
	 * @param string $filename full filename of the the file to be checked
	 * 
	 * @return bool return true if file does not exist, false if it does
	 */
	public function checkIfFileExists($filename){
		try {
			if(!file_exists($filename)){
				throw new Exception("File already exists: ". $filename, 103);
			}
		}
		catch(Exception $e){
			$this->numberOfErrors += 1;
			$this->userReadableUploadErrorMessages[] = $e->getMessage();
			$this->userReadableUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorMessages[] = $e->getMessage();
			$this->allUploadErrorCodes[] = $e->getCode();
			$this->allUploadErrorsLineNumber[] = $e->getLine();
			
			return true;
		}
		
		return false;
	}
	/**
	 * Check to make sure the file is an image
	 * throw exception
	 * 
	 * @param string $fileType The file type of an image
	 * 
	 * @example 'image/jpeg'
	 * 
	 * @return boolean returns true/false
	 */
	protected function isAudio($fileType){
		if (($fileType != 'audio/mpeg'
			&& $fileType != 'audio/x-mpeg'
			&& $fileType != 'audio/mp3'
			&& $fileType != 'audio/x-mp3'
			&& $fileType != 'audio/mpeg3'
			&& $fileType != 'audio/x-mpeg3'
			&& $fileType != 'audio/mpg'
			&& $fileType != 'audio/x-mpg'
			&& $fileType != 'audio/x-mpegaudio'
			&& $fileType != 'audio/wav'
			&& $fileType != 'audio/ogg'
			&& $fileType != 'audio/mp4')){

			return false;
		}
		
		return true;
	}
	
	/**
	 * Check to make sure the file isn't over the maximum allowed filesize
	 * Max Allowed Filesize: 5MB
	 * throw exception
	 * 
	 * @param int $fileSize filesize of an image
	 * @return boolean returns true/false
	 */
	protected function audioSmallEnoughForUpload($fileSize){
		if($fileSize > $this->getMaxUploadSize()){			
			return false;
		}

		return true;		
	}
	
	/**
	 * Check that there is indeed an image present
	 * throw exception
	 * 
	 * @param int $fileSize filesize of an image
	 * @return boolean returns true/false
	 */
	protected function audioIsNotEmpty($fileSize){
		if($fileSize <= 0){
			return false;
		}
		
		return true;
	}

	/**
	 * Run the validation tests for the audio
	 * ex. check if it's indeed an audio
	 * check if under maximum size
	 * Perhaps down the road have a minium size (it is currently at zero)
	 * 
	 * @param string $fileSize file size of the image to be checked
	 * @param string $fileType file type of the image to be checked
	 * 
	 * @return boolean returns true if all validations pass or false if any fail
	 * 
	 */
	protected function validateAudio($fileName, $fileSize, $fileType){
		$valid = true;
		try {
			if(!$this->audioIsNotEmpty($fileSize)){
				throw new Exception('File is empty.', 1003);
			}
			else {
				try {
					if(!$this->audioSmallEnoughForUpload($fileSize)){
						throw new Exception($fileName . ' is too large.', 1002);
					}
					else {
						try {
							if(!$this->isAudio($fileType)){
								echo $fileType;
								throw new Exception($fileName . ' is not an audio file so it was not uploaded.',  1001);
							}
						}
						catch(Exception $e) {
							$this->numberOfErrors += 1;
							$this->userReadableUploadErrorMessages[] = $e->getMessage();
							$this->userReadableUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorMessages[] = $e->getMessage();
							$this->allUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorsLineNumber[] = $e->getLine();
							$valid = false;
						}	
					}
				}
				catch(Exception $e) {
					$this->numberOfErrors += 1;
					$this->userReadableUploadErrorMessages[] = $e->getMessage();
					$this->userReadableUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$valid = false;
				}
			}
		}
		catch(Exception $e){
			$valid = false;
		}
		
		return $valid;
	}
	
}

?>

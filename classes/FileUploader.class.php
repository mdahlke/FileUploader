<?php

/**
 * FileUploader is a parent class that allows for easy implementation for uploading
 * 	multiple image, audio, or document files at once.
 * This class may also upload a single image from a URL
 *
 * @author Michael Dahlke
 */
class FileUploader {

	// Variables
	protected $originalFilePath = 'upload/';
	protected $prependToFileName = "";
	protected $numberOfFilesPerDirectory = 100;
	protected $numberOfAllowedFilesToUpload = 1;
	protected $maxUploadSize = 5242880; // 5MB
	protected $fileType = array(); // this is not the subtype of the file (.mp3 or .jpg) this will be either (audio || image || doc ...etc)
	/** 
	 * type this will be set by the child classes to their respective file type (i.e. 'image' or 'audio')
	 * this is for the overall upload, where as $fileType is for each individual file
	 */
	protected $fileTypeForUpload;
	protected $errorLogFileName = 'FileUploaderErrorLog.txt';
	// Read only variables
	protected $numberOfSuccessfulUploads = 0;
	protected $numberOfErrors = 0;
	protected $userReadableUploadErrorMessages = array();
	protected $userReadableUploadErrorCodes = array();
	protected $allUploadErrorMessages = array();
	protected $allUploadErrorCodes = array();
	protected $allUploadErrorsLineNumber = array();
	protected $allUploadErrorsFileName = array();
	//Acceptable file types for different kinds of file uploads
	protected $allowedAudioTypes = array(
		'audio/mpeg', 'audio/x-mpeg', 'audio/mp3',
		'audio/x-mp3', 'audio/mpeg3', 'audio/x-mpeg3',
		'audio/mpg', 'audio/x-mpg', 'audio/x-mpegaudio',
		'audio/wav', 'audio/x-wav', 'audio/ogg',
		'audio/mp4', 'audio/mid', 'audio/x-aiff',
		'audio/rmi'
	);
	protected $allowedImageTypes = array(
		'image/png', 'image/jpeg',
		'image/jpg', 'image/pjpeg',
		'image/gif'
	);
	protected $allowedDocumentTypes = array(
		'text/cmd', 'text/css',
		'text/csv', 'text/html',
		'text/javascript', 'text/plain',
		'text/vcard', 'text/xml',
		'text/txt', 'application/octet-stream',
		'application/msword',
		'application/vnd.oasis.opendocument.text',
		'application/x-php', 'text/xml'
	);

	/*
	 * This was application specific for me personally
	 * But may be helpful to you as well which is why
	 * I left it in the class
	 */

	public function inParentDirectory($input = true) {
		$this->inParentDirectory = $input;
	}

	public function getPrependToFileName() {
		return $this->prependToFileName;
	}

	/**
	 * 
	 * @param string $input whatever you would like prepended to the filename (max: 20 characters)
	 */
	public function setPrependToFileName($input) {
		if (strlen($input) > 20) {
			$input = substr($input, 0, 20);
		}
		$this->prependToFileName = $input . '_';
	}

	public function getNumberOfAllowedFilesToUpload() {
		return $this->numberOfAllowedFilesToUpload;
	}

	public function setNumberOfAllowedFilesToUpload($input) {
		$this->numberOfAllowedFilesToUpload = $input;
	}

	public function getNumberOfFilesPerDirectory() {
		return $this->numberOfFilesPerDirectory;
	}

	public function setNumberOfFilesPerDirectory($input) {
		$this->numberOfFilesPerDirectory = $input;
	}

	public function getNumberOfSuccessfulUploads() {
		return $this->numberOfSuccessfulUploads;
	}

	public function getMaxUploadSize() {
		return $this->maxUploadSize;
	}

	public function setMaxUploadSize(int $input) {
		$this->maxUploadSize = $input;
	}

	public function getErrorLogFilename() {
		return $this->errorLogFileName;
	}

	public function setErrorLogFilename($input) {
		$this->errorLogFileName = $input;
	}

	public function getFileType() {
		return $this->fileType;
	}

	public function setFileType($input) {
		$this->fileType[] = $input;
	}

	protected function getFileTypeForUpload() {
		return $this->fileTypeForUpload;
	}

	protected function setFileTypeForUpload($input) {
		$this->fileTypeForUpload = $input;
	}

	/**
	 * Detect the file type of an uploading file
	 * NOTE: this does NOT validate this just says what type of file it is
	 * 
	 * @param string $file the file to be checked
	 * 
	 * $return void
	 */
	public function detectFileType($file) {
		$fileType = substr($file, 0, 5);
		if ($fileType == 'audio') {
			return $fileType;
		} else if ($fileType == 'image') {
			return $fileType;
		} else {
			return 'unknown';
		}
	}

	/**
	 * Check to make sure the file is an acceptable file for upload
	 * throw exception
	 * 
	 * @param string $fileType The file type of an image
	 * 
	 * @example 'image/jpeg'
	 * 
	 * @return boolean returns true/false
	 */
	protected function isAcceptableFileType($fileType) {
		//$fileCategory = $this->detectFileType($fileType);
		$fileCategory = $this->getFileTypeForUpload();

		if ($fileCategory == 'audio') {
			if (in_array($fileType, $this->allowedAudioTypes)) {
				return true;
			}
		} else if ($fileCategory == 'image') {
			if (in_array($fileType, $this->allowedImageTypes)) {
				return true;
			}
		} else if ($fileCategory == 'document') {
			if (in_array($fileType, $this->allowedDocumentTypes)) {
				return true;
			}
		} else {
			echo 'unknown filetype';
		}

		return false;
	}

	/**
	 * Check to make sure the file isn't over the maximum allowed filesize
	 * Max Allowed Filesize: 5MB
	 * throw exception
	 * 
	 * @param int $fileSize filesize of an image
	 * @return boolean returns true/false
	 */
	protected function fileSmallEnoughForUpload($fileSize) {
		if ($fileSize > $this->getMaxUploadSize()) {
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
	protected function fileIsNotEmpty($fileSize) {
		if ($fileSize <= 0) {
			throw new Exception('File is empty.', 1003);

			return false;
		}

		return true;
	}

	/**
	 * Run the validation tests for the image
	 * ex. check if it's indeed an image
	 * check if under maximum size
	 * Perhaps down the road have a minium size (it is currently at zero)
	 * 
	 * @param string $fileSize file size of the image to be checked
	 * @param string $fileType file type of the image to be checked
	 * 
	 * @return boolean returns true if all validations pass or false if any fail
	 * 
	 */
	protected function validateFile($fileName, $fileSize, $fileType) {
		$valid = true;
		try {
			if (!$this->fileIsNotEmpty($fileSize)) {
				throw new Exception($fileName . ' is empty.', 1003);
			} else {
				try {
					if (!$this->isAcceptableFileType($fileType)) {
						throw new Exception($fileName . ' is not an ' . $this->fileTypeForUpload . ' file so it was not uploaded.', 1001);
					} else {
						try {
							if (!$this->fileSmallEnoughForUpload($fileSize)) {
								throw new Exception($fileName . ' is too large.', 1002);
							}
						} catch (Exception $e) {
							$this->numberOfErrors += 1;
							$this->userReadableUploadErrorMessages[] = $e->getMessage();
							$this->userReadableUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorMessages[] = $e->getMessage();
							$this->allUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorsLineNumber[] = $e->getLine();
							$this->allUploadErrorsFileName[] = $e->getFile();
							$valid = false;
						}
					}
				} catch (Exception $e) {
					$this->numberOfErrors += 1;
					$this->userReadableUploadErrorMessages[] = $e->getMessage();
					$this->userReadableUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
					$valid = false;
				}
			}
		} catch (Exception $e) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * count items in a folder
	 * 
	 * @param string $folder path of the folder you want the item count of
	 * 
	 * @return int number of items in the directory
	 */
	protected function countItemsInDirectory($folder) {
		$glob = glob(realpath($folder) . '/*');

		$sub_directories = array_filter($glob, 'is_dir');
		$filecount = count($sub_directories);

		return $filecount;
	}

	/**
	 * 
	 * @param string $folder folder name to be created
	 */
	protected function createDirectory($folder) {
		if (!file_exists($folder)) {
			try {
				if (!mkdir($folder . "/", 0755, true)) {
					throw new Exception("Could not make directory: " . $folder, 102);
				}
			} catch (Exception $e) {
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
	 * 	  - if there isn't room then we call createDirectory() which will create
	 * 		a directory and set the filepath accordingly
	 * @param string $folder path to the folder to check
	 * 
	 */

	protected function checkDirectoryStatus($folder) {
		if (!file_exists($folder)) {
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
	public function checkIfFileExists($filename) {
		try {
			if (!file_exists($filename)) {
				throw new Exception("File already exists: " . $filename, 103);
			}
		} catch (Exception $e) {
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
	 * *****************************************************************************
	 * 																			   *
	 * 						ERROR HANDLING									*
	 * 																			   *
	 * *****************************************************************************
	 * */

	/**
	 * @return int the number of errors that occured during upload
	 */
	public function getNumberOfErrorsDuringUpload() {
		return $this->numberOfErrors;
	}

	/**
	 * @return string error messages that are safe or relevant for a user
	 */
	public function returnUserReadableUploadErrorMessages() {
		return $this->userReadableUploadErrorMessages;
	}

	/**
	 * 
	 * @return string return error codes that are safe or relevant for a user
	 */
	public function returnUserReadableUploadErrorCodes() {
		return $this->userReadableUploadErrorCodes;
	}

	/**
	 * @return string return all error messages
	 */
	public function returnAllUploadErrorMessages() {
		return $this->allUploadErrorMessages;
	}

	/**
	 * @return string return all error codes
	 */
	public function returnAllUploadErrorCodes() {
		return $this->allUploadErrorCodes;
	}

	/**
	 * @return string return all error line numbers
	 */
	public function returnAllUploadErrorsLineNumber() {
		return $this->allUploadErrorsLineNumber;
	}

	/**
	 * @return string return all error filenames
	 */
	public function returnAllUploadErrorsFileName() {
		return $this->allUploadErrorsFileName;
	}

	/**
	 * @param int $index index of desired error message
	 * 
	 * @return string an error message at a specific index
	 */
	public function returnUploadErrorMessageAtIndex($index) {
		return $this->allUploadErrorMessages[$index];
	}

	/**
	 * @param int $index index of desired error code
	 * 
	 * @return string an error code at a specific index
	 */
	public function returnUploadErrorCodeAtIndex($index) {
		return $this->allUploadErrorCodes[$index];
	}

	/**
	 * @param int $index index of desired line number
	 * 
	 * @return string a line number at a specific index
	 */
	public function returnUploadErrorLineNumberAtIndex($index) {
		return $this->allUploadErrorsLineNumber[$index];
	}

	/**
	 * @param int $index index of desired line number
	 * 
	 * @return string a filename at a specific index
	 */
	public function returnUploadErrorFileNameAtIndex($index) {
		return $this->allUploadErrorsFileName[$index];
	}

	/**
	 * @return string returns error messages in a nice format
	 */
	public function returnFormattedErrors() {
		$formattedErrors = '';

		for ($i = 0; $i < $this->getNumberOfErrorsDuringUpload(); $i++) {
			$formattedErrors .= "Error " . $this->returnUploadErrorCodeAtIndex($i) .
					" on line " . $this->returnUploadErrorLineNumberAtIndex($i) .
					" in " . $this->returnUploadErrorFileNameAtIndex($i) .
					" :: " . $this->returnUploadErrorMessageAtIndex($i) . "\r\n";
		}

		return $formattedErrors;
	}

	/**
	 * Write all upload errors to an error log
	 * 
	 */
	protected function writeAllErrorsToLog() {
		$errorLogHandle = $this->getErrorLogFilename();

		try {
			if ($errorLogHandle === "") {
				throw new Exception('File does not exist.', '4001');
				return;
			} else if ($errorHandleToLock = fopen($errorLogHandle, 'ab')) {

				try {
					if (flock($errorHandleToLock, LOCK_EX)) {

						$errorsToLog = $this->returnFormattedErrors();

						try {
							if (!fwrite($errorHandleToLock, $errorsToLog)) {
								throw new Exception('Could not write to file.', '4004');
							}
						} catch (Exception $e) {
							$this->allUploadErrorMessages[] = $e->getMessage();
							$this->allUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorsLineNumber[] = $e->getLine();
							$this->allUploadErrorsFileName[] = $e->getFile();
						}
					} else {
						throw new Exception('File could not be locked.', '4003');
					}
				} catch (Exception $e) {
					$this->allUploadErrorMessages[] = $e->getMessage();
					$this->allUploadErrorCodes[] = $e->getCode();
					$this->allUploadErrorsLineNumber[] = $e->getLine();
					$this->allUploadErrorsFileName[] = $e->getFile();
				}
			} else {
				throw new Exception('File could not be opened.', '4002');
				$this->allUploadErrorMessages[] = $e->getMessage();
				$this->allUploadErrorCodes[] = $e->getCode();
				$this->allUploadErrorsLineNumber[] = $e->getLine();
				$this->allUploadErrorsFileName[] = $e->getFile();
			}
		} catch (Exception $e) {
			$this->numberOfErrors += 1;
			/*
			 * We return this as fatal because the error log is crucial to reporting errors
			 */
			die('ERROR ' . $e->getCode() . ' on line ' . $e->getLine() . ' :: ' . $e->getMessage());
		}
	}

}

// end class
?>
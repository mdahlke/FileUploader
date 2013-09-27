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
require 'interfaces/iFileUpload.php';

class AudioUploader extends FileUploader implements iFileUpload {

	protected $originalFilePath = 'upload/audio';
	protected $fileName = array();
	protected $filePath = array();
	protected $fileType = array();
	protected $fileSubType = array();
	protected $fullFileType = array();
	protected $fileTypeForUpload = 'audio';
	protected $maxUploadSize = 419430400;
	protected $maxNumberOfUploadsPerUpload = 20;

	//private $numberOfFilesPerDirectory = 10;

	/**
	 * Sets the original filepath for the audio
	 * @param string $input the original file path
	 */
	public function setOriginalFilePath($input) {
		if (substr($input, -1) !== '/') {
			$input .= '/';
		}
		if (!file_exists($input)) {
			$folder = $this->createDirectory($input);
		} else {
			$folder = $input;
		}

		$this->originalFilePath = $folder;
	}

	public function getFileName() {
		return $this->fileName;
	}

	public function setFileName($input) {
		$this->fileName[] = $input;
	}

	public function getFilePath() {
		return $this->filePath;
	}

	public function setFilePath($input) {
		$this->filePath[] = $input;
	}

	public function getFileType() {
		return $this->fileType;
	}

	public function setFileType($input) {
		$this->fileType[] = $input;
	}

	public function getFileNameAtIndex($index) {
		return $this->fileName[$index];
	}

	public function getFilePathAtIndex($index) {
		return $this->filePath[$index];
	}

	public function getFileTypeAtIndex($index) {
		return $this->fileType[$index];
	}

	public function getOriginalFilePath() {
		return $this->originalFilePath;
	}

	public function getFullFileType() {
		return $this->fileSubType;
	}

	public function setFullFileType($input) {
		$this->fullFileType[] = $input;
	}

	public function getFileSubType() {
		return $this->fileSubType;
	}

	public function setFileSubType($input) {
		$this->fullFileType[] = $input;
	}

	public function getFullFileTypeAtIndex($index) {
		return $this->fullFileType[$index];
	}

	public function getMaxUploadSize() {
		return $this->maxUploadSize;
	}

	public function setMaxUploadSize($input) {
		$this->maxUploadSize = $input;
	}

	public function getMaxNumberOfUploadsPerUpload() {
		return $this->maxNumberOfUploadsPerUpload;
	}

	public function setMaxNumberOfUploadsPerUpload($input) {
		$this->maxNumberOfUploadsPerUpload = $input;
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
	 * Check to make sure the file is an image
	 * throw exception
	 * 
	 * @param string $fileType The file type of an image
	 * 
	 * @example 'image/jpeg'
	 * 
	 * @return boolean returns true/false
	 */
	protected function isAudio($fileType) {
		if (($fileType != 'audio/mpeg' && $fileType != 'audio/x-mpeg' && $fileType != 'audio/mp3' && $fileType != 'audio/x-mp3' && $fileType != 'audio/mpeg3' && $fileType != 'audio/x-mpeg3' && $fileType != 'audio/mpg' && $fileType != 'audio/x-mpg' && $fileType != 'audio/x-mpegaudio' && $fileType != 'audio/wav' && $fileType != 'audio/ogg' && $fileType != 'audio/mp4')) {

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
	protected function audioSmallEnoughForUpload($fileSize) {
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
	protected function audioIsNotEmpty($fileSize) {
		if ($fileSize <= 0) {
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
	protected function validateAudio($fileName, $fileSize, $fileType) {
		$valid = true;
		try {
			if (!$this->audioIsNotEmpty($fileSize)) {
				throw new Exception('File is empty.', 1003);
			} else {
				try {
					if (!$this->audioSmallEnoughForUpload($fileSize)) {
						throw new Exception($fileName . ' is too large.', 1002);
					} else {
						try {
							if (!$this->isAudio($fileType)) {
								echo $fileType;
								throw new Exception($fileName . ' is not an audio file so it was not uploaded.', 1001);
							}
						} catch (Exception $e) {
							$this->numberOfErrors += 1;
							$this->userReadableUploadErrorMessages[] = $e->getMessage();
							$this->userReadableUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorMessages[] = $e->getMessage();
							$this->allUploadErrorCodes[] = $e->getCode();
							$this->allUploadErrorsLineNumber[] = $e->getLine();
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
					$valid = false;
				}
			}
		} catch (Exception $e) {
			$valid = false;
		}

		return $valid;
	}

}

?>

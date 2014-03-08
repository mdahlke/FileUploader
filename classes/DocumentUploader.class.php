<?php

/**
 *  Document Uploader
 * 
 * @subpackage FileUploader.class.php
 * 
 * @author Michael Dahlke <madahlke27@gmail.com>
 * 
 */
require ( __DIR__ . '/FileUploader.class.php');
require ( __DIR__ . '/../interfaces/iFileUpload.php');

class DocumentUploader extends FileUploader implements iFileUpload {

	protected $originalFilePath = 'upload/documents/';
	protected $fileName = array();
	protected $filePath = array();
	protected $fileType = array();
	protected $fileSubType	= array();
	protected $fullFileType = array();
	protected $fileTypeForUpload = 'document';
	protected $maxUploadSize = 419430400;
	protected $maxNumberOfUploadsPerUpload = 20;

	//private $numberOfFilesPerDirectory = 10;

	/**
	 * Getters and Setters
	 */
	public function getOriginalFilePath() {
			return $this->originalFilePath;
	}
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

	public function getFileType() {
			return $this->fileType;
	}

	public function setFileType($input) {
			$this->fileType[] = $input;
	}

	public function getFileTypeAtIndex($index) {
			return $this->fileType[$index];
	}

	public function setFilePath($input){
		$this->filePath[] = $input;
	}
	
	public function getFullFileType(){
		return $this->fileSubType;
	}
	public function setFullFileType($input){
		$this->fullFileType[] = $input;
	}
	
	public function getFileSubType(){
		return $this->fileSubType;
	}
	public function setFileSubType($input){
		$this->fullFileType[] = $input;
	}
	
	public function getFileNameAtIndex($index){
		return $this->fileName[$index];
	}
	public function getFilePathAtIndex($index){
		return $this->filePath[$index];
	}
	public function getFullFileTypeAtIndex($index){
		return $this->fullFileType[$index];
	}
	
}

?>

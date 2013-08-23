<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UploadAudio
 *
 * @author Michael Dahlke <Michael.Dahlke at RummageCity.com>
 */

require ('FileUploader.class.php');
require ('interfaces/iFileUpload.php');

class UploadAudio extends FileUploader implements iFileUpload {
	private $fileName				= array();
	private $filePath				= array();
	private $fileSubType			= array();
	private $fullFileType			= array();
	protected $maxUploadSize		= 419430400;
	protected $fileTypeForUpload	= 'audio';
	/**
	 * Getters and Setters
	 */
	public function getOriginalFilePath(){
		return $this->originalFilePath;
	}
	/**
	 * Sets the original filepath for the audio
	 * @param string $input the original file path
	 */
	public function setOriginalFilePath($input){
		if(substr($input, -1) !== '/'){
			$input .= '/';
		}	
		if(!file_exists($input)){
			$folder = $this->createDirectory($input);
		}
		else {
			$folder = $input;
		}

		$this->originalFilePath = $folder;

	}
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
	public function getFileTypeAtIndex($index){
		return $this->fileType[$index];
	}
	public function getFullFileTypeAtIndex($index){
		return $this->fullFileType[$index];
	}
	
	public function upload($file){
		$destination = $this->getOriginalFilePath();

		for($i = 0; $i < $this->getNumberOfAllowedFilesToUpload(); $i++){
			$continueWithUpload = $this->validateFile($file['name'][$i], $file['size'][$i], $file['type'][$i]);
			
			if($continueWithUpload){
				
				$fullFilePath = $destination.basename($file['name'][$i]);
										
				if($continueWithUpload){
					$this->setFileName(basename($file['name'][$i]));
					
					$this->setFullFileType($file['type']);

					$this->setFileSubType($file['type'][$i]);
					
					$this->checkDirectoryStatus($destination);
					
					try {
						if(!move_uploaded_file($file['tmp_name'][$i], $fullFilePath) ) {
							throw new Exception('Couldn\'t upload song', 101);
						}
						else {
							$this->numberOfSuccessfulUploads++;
						}
					}
					catch(Exception $e){
						echo $e->getMessage();
					}
				}
				else {
					if(count($this->returnAllUploadErrorMessages()) > 0){
						$this->writeAllErrorsToLog();
					}
				}
			}
			else {
				if(count($this->returnAllUploadErrorMessages()) > 0){
					$this->writeAllErrorsToLog();
				}
			}
		} // End of for() loop
		
	} // End of upload() function
	
	
}// End of class

?>


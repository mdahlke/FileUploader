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

require ('AudioUploader.class.php');

class UploadAudio extends AudioUploader {

	
	/**
	 * Getters and Setters
	 */	
	public function upload($file){
		$destination = $this->getOriginalFilePath();

		for($i = 0; $i < $this->getNumberOfAllowedFilesToUpload(); $i++){
			$continueWithUpload = $this->validateFile($file['name'][$i], $file['size'][$i], $file['type'][$i]);
			
			if($continueWithUpload){
				
				$fullFilePath = $destination.basename($file['name'][$i]);

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

		} // End of for() loop
		if(count($this->returnAllUploadErrorMessages()) > 0){
			$this->writeAllErrorsToLog();
		}
		
	} // End of upload() function
	
	
}// End of class

?>


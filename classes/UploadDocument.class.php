<?php

/**
 * Description of UploadDocument
 *
 * @author Michael Dahlke
 */

require( __DIR__ . '/DocumentUploader.class.php');

class UploadDocument extends DocumentUploader {
	
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
							throw new Exception('Couldn\'t upload document', 101);
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
}

?>

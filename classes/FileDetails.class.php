<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileDetails
 *
 * @author michael
 */
class FileDetails {
	protected $currentDir;
	protected $file;

	public function getCurrentDir(){
		return $this->currentDir;
	}
	public function setCurrentDir($input){
		$this->currentDir = $input;
	}
	
	public function getFile(){
		return $this->file;
	}
	public function setFile($input){
		$this->file = \basename($input); // strip off directory information for security
	}
	
	public function printFileDetails(){
		date_default_timezone_set('America/Chicago');
		echo '<h1>Details of file: ' . $this->getFile() . '</h1>';
		$file = $this->getCurrentDir() . $this->getFile();

		echo '<h2>File data</h2>';
		echo 'File last accessed: ' . date('j F Y H:i', fileatime($file)) . '<br>';
		echo 'File last modified: ' . date('j F Y H:i', filemtime($file)) . '<br>';

		$user = posix_getpwuid(fileowner($file));
		echo 'File owner: ' . $user['name'] . '<br>';

		$group = posix_getgrgid(filegroup($file));
		echo 'File group: ' . $group['name'] . '<br>';

		echo 'File permissions: ' . decoct(fileperms($file)) . '<br>';

		echo 'File type: ' . filetype($file) . '<br>';

		echo 'File size: ' . filesize($file) . ' bytes<br>';


		echo '<h2>File tests</h2>';

		echo 'is_dir: ' . (is_dir($file) ? 'true' : 'false') . '<br>';
		echo 'is_executable: ' . (is_executable($file) ? 'true' : 'false') . '<br>';
		echo 'is_file: ' . (is_file($file) ? 'true' : 'false') . '<br>';
		echo 'is_link: ' . (is_link($file) ? 'true' : 'false') . '<br>';
		echo 'is_readable: ' . (is_readable($file) ? 'true' : 'false') . '<br>';
		echo 'is_writable: ' . (is_writable($file) ? 'true' : 'false') . '<br>';
	}
}

?>

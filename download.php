<?php
/**
* Downloads file from Google Drive
*
* @since 4/8/14
* @author James Mensch
**/
require_once('drive.php');

/**
* Requests the files from Drive
* @param string $id
* @param string $secret
* @param array $files[$dest_file => $source_file]
**/
class Download {
	private $drive;
	private $files;

	public function __construct($id, $secret, $files) {
		$this->drive = new Drive($id, $secret);
		$this->files = $files;
	}
	/**
	* Loops through the files array and downloads each
	**/
	public function downloadFiles() {
		foreach($this->files as $dest_file => $source_file) {
			$file_contents = $this->getFileContents($source_file);
			$this->writeFile($dest_file, $file_contents);
		}
	}
	/**
	* Gets the contents of the file
	* @param string $filename
	* @return string $file
	**/
	private function getFileContents($source_file) {
		return $this->drive->getFile($source_file);
	}
	/**
	* Writes $file to $dest_file
	* @param string $dest_file
	* @param string $file
	**/
	private function writeFile($dest_file, $file_contents) {
		if ($file_contents) {
			file_put_contents($dest_file, $file);
		}
	}
}
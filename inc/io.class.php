<?php


// character files IO wrapper
class IO
{
	private $charsave_path, $charinfo_path;

	public function __construct($charsave_path, $charinfo_path)
	{
		$this->charsave_path = $charsave_path . DIRECTORY_SEPARATOR;
		$this->charinfo_path = $charinfo_path . DIRECTORY_SEPARATOR;
	}
	
	public function readCharSave($charname)
	{
		return $this->_read($this->charsave_path . $charname);
	}
	public function saveCharSave($charname, $bytes)
	{
		return $this->_save($this->charsave_path . $charname, $bytes);
	}
	public function deleteCharSave($charname)
	{
		return $this->_delete($this->charsave_path . $charname);
	}
	

	public function readCharInfo($username, $charname)
	{
		return $this->_read($this->charinfo_path . $username . DIRECTORY_SEPARATOR . $charname);
	}
	public function saveCharInfo($username, $charname, $bytes)
	{
		return $this->_save($this->charinfo_path . $username . DIRECTORY_SEPARATOR . $charname, $bytes);
	}
	public function deleteCharInfo($username, $charname)
	{
		return $this->_delete($this->charinfo_path . $username . DIRECTORY_SEPARATOR . $charname);
	}
	
	// return all charinfo files from account as array
	public function readAllCharInfo($username)
	{
		$data = '';
		// iterate files in charinfo account directory
		if ($h = @opendir($this->charinfo_path . $username))
		{
			while ( false !== ($file = readdir($h)) )
				if ( !is_dir($file) )
					$data .= $this->readCharInfo($username, $file);
			
			return $data;
		}
		return false;
	}

	
	
	// read bytes from file
	private function _read($filename)
	{
		if ( !$h = @fopen($filename, 'rb') )
			return false; 

		$buffer = ''; 
		while ( !feof($h) )
			$buffer .= fread($h, 1024);

		fclose($h);
		return $buffer;
	}
	
	// save bytes to file
	private function _save($filename, $bytes)
	{
		if ( !$h = @fopen($filename,"wb") )
			return false;
			
		if ( !@fwrite($h, $bytes) )
			return false;
			
		fclose($h);
		return true;
	}
	
	private function _delete($filename)
	{
		unlink($filename);
		return true;
	}

}
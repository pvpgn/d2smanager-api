<?php

error_reporting(E_ALL);


require_once('config.php');
$config = new Config($config_data);
$db = new DB($config);

$account = $db->getAccount("HarpyGuard");
if ($account != null)
	echo json_encode($account);
else
	echo "Account doesn't exists";

	
$io = new IO($config);
$chars = $io->readAllCharInfo("harpywar");

print_r($chars);






// character files IO wrapper
class IO
{
	private $charsave_path, $charinfo_path;

	public function __construct($config)
	{
		$charsave_path = $config->charsave_path . DIRECTORY_SEPARATOR;
		$charinfo_path = $config->charinfo_path . DIRECTORY_SEPARATOR;
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
		$data = array();
		// iterate files in charinfo account directory
		if ($h = opendir($this->charinfo_path . $username))
		{
			while ( false !== ($file = readdir($h)) )
				$data[$file] = readCharInfo($username, $file);
			
			return $data;
		}
		return false;
	}
	
	// read bytes from file
	private function _read($filename)
	{
		if ( !$h = fopen($filename, 'rb') )
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
		if ( !$h = fopen($filename,"wb") )
			return false;
			
		if ( !fwrite($h, $bytes) )
			return false;
			
		fclose($h);
		return false;
	}
	
	private function _delete($filename)
	{
		unlink($filename);
	}

}
	

// config wrapper
class Config
{
	private $data;
	
	public function __construct($data)
	{
		$this->data = $data;
	}
	
	// return value from config array
	public function __get($name)
    {
        if ( @array_key_exists($name, $this->data) )
            return $this->data[$name];

		throw new Exception('Undefined config property: ' . $name);
    }
	
}

// database wrapper
class DB
{
	private $dbh;
	private $config;
	
	function __construct($config)
	{
		$this->config = $config;
		try
		{
			// create connection
			switch( $config->db_type )
			{
				case 'mysql':
					$this->dbh = new PDO("mysql:host={$config->db_host};dbname={$config->db_name}", $config->db_user, $config->db_pass); 
					break;
					
				case 'pgsql':
					$this->dbh = new PDO("pgsql:dbname={$config->db_name} host={$config->db_host}", $config->db_user, $config->db_pass); 
					break;
					
				case 'odbc':
					$this->dbh = new PDO("odbc:{$config->db_name}", $config->db_user, $config->db_pass); 
					break;
					
				case 'sqlite':
					$this->dbh = new PDO("sqlite:{$config->db_name}");
					break;
			}
		}
		catch(PDOException $e)
		{
			throw new Exception( $e->getMessage() );
		}
	}
	
	// get account data in assoc array
	public function getAccount($username)
	{
		$sth = $this->dbh->prepare("SELECT * FROM {$this->config->table_bnet} WHERE username = ?");  
		$sth->bindParam(1, strtolower($username) );  
		$sth->execute();
		
		$sth->setFetchMode(PDO::FETCH_ASSOC);  
		  
		return $sth->fetch();
	}
	
	
	function __destruct()
	{
		// close pdo connection
		$dbh = null;
	}
}



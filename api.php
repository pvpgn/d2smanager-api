<?php

$config_data = array
(

/* API OPTIONS */
	
	// you must provide this key in the service control panel
	'access_key' => 'CHANGE_IT',
	
	// ip allowed to use this api, put our service ip here
	'allow_ip' => '127.0.0.1',


/* PVPGN DATABASE OPTIONS */

	// database type: mysql, pgsql, odbc, sqlite
	//  http://www.php.net/manual/en/pdo.drivers.php
	'db_type' => 'mysql',
	
	// database host (not needed for odbc and sqlite)
	'db_host' => '127.0.0.1',
	
	// database login (not needed for sqlite)
	'db_user' => 'root', 
	
	// database password (not needed for sqlite)
	'db_pass' => '',
	
	// pvpgn database name
	'db_name' => 'bnet',
	
	// pvpgn "bnet" table name
	'table_bnet' => 'bnet',
	
	
/* D2GS TELNET OPTIONS */

	// telnet host
	'telnet_host' => '127.0.0.1',
	
	// telnet port, default 8888
	'telnet_port' => '8888',
	
	// telnet password
	'telnet_pass' => 'abcd123',
	
	
/* D2DBS OPTIONS 
  (script must have read+write permissions to the directories declared below) */

	// full path to var/charsave directory
	'charsave_path' => 'M:\SERVERS\D2GS\multu_realm_test\d2server_1\var\charsave',

	// full path to var/charinfo directory
	'charinfo_path' => 'M:\SERVERS\D2GS\multu_realm_test\d2server_1\var\charinfo',
	

);





// END CONFIG DATA















error_reporting(E_ALL);

$config = new Config($config_data);

// call example:
//  ?method=getAccount&name=harpywar

// wrap the whole thing in a try-catch block to catch any wayward exceptions!
try
{
	// get all of the parameters in the GET request
	$params = $_GET;
	
	// get the method and format it correctly so all the letters are not capitalized
	if ( !@array_key_exists('method', $params) )
			throw new Exception("Missing method name");
			
	$method = strtolower($params['method']);
	
	
	// create a new instance of the controller, and pass it the parameters from the request
	$controller = new Controller($config, $params);
	
	// check if the method exists in the controller; if not, throw an exception.
	if( method_exists($controller, $method) === false )
		throw new Exception('Method is invalid.');

	// method result
	$result = $controller->$method();
	
	// if response is binary data
	if ($controller->isBinary = true)
	{
		#header("Content-type: application/octet-stream; ");
		echo $result;
	}
	else
	{
		// execute the method
		$response['success'] = true;
		$response['data'] = $result;
		
		echo json_encode($response);
	}
		
}
catch( Exception $e )
{
	// catch any exceptions and report the problem
	$response = array();
	$response['success'] = false;
	$response['errmsg'] = $e->getMessage();
	
	echo json_encode($response);
}


exit();








class Controller
{
	// config object
	private $config;
	
	// parameters passed to a method
	private $params; 
	
	// io object
	private $io;
	
	public $isBinary = false;
	
	public function __construct($config, $params)
	{
		$this->config = $config;
		$this->params = $params;

		$this->io = new IO($config->charsave_path, $config->charinfo_path);
	}

	
/* ************ START API METHODS ************* */


	public function getaccount()
	{
		$account = $this->getDB()->getAccount( $this->getParam('account') );
		if ($account != null)
			return $account;
		else
			throw new Exception('Account does not exists');
	}
	
	
	public function getallcharinfo()
	{
		// binary result
		$this->isBinary = true;
		
		if ( $chars = $this->io->readAllCharInfo( $this->getParam('char') ) )
			return $chars;
		else
			throw new Exception('Can not read the character file');
	}
	
	// check if character remain in the game
	public function ischaringame()
	{
		// check character doesn't playing now
		if ( $this->getTelnet()->find_char( $this->getParam('char') ) )
			throw new Exception("Please, leave your character from the game");
		else
			return false;
	}
	
	
	
/* ************ END API METHODS ************* */

	
	
	
	
	
	private $_db;
	
	// get database instance (lazy initialization)
	private function getDB()
	{
		if ($this->_db === null)
			$this->_db = new DB($this->config->db_type, $this->config->db_name, $this->config->db_host, $this->config->db_user, $this->config->db_pass);
			
		return $this->_db;
	}

	
	private $_telnet;
	
	// get telnet instance (lazy initialization)
	private function getTelnet()
	{
		if ($this->_telnet === null)
		{
			$this->_telnet = new Telnet($this->config->host, $this->config->port);
			
			if ( !$this->_telnet->login($this->config->pass) )
				throw new Exception("Telnet password is invalid");
		}

		return $this->_telnet;
	}
	
	
	// return a paramemer by name
	private function getParam($name)
	{
		if ( !@array_key_exists($name, $this->params) )
			throw new Exception("Missing parameter '$name'");
		
		return $this->params[$name];
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
	}

}




// database wrapper
class DB
{
	private $dbh;
	private $table_bnet;
	
	function __construct($type, $name, $host = false, $user = false, $pass = false)
	{
		try
		{
			// create connection
			switch( $type )
			{
				case 'mysql':
					$this->dbh = new PDO("mysql:host={$host};dbname={$name}", $user, $pass); 
					break;
					
				case 'pgsql':
					$this->dbh = new PDO("pgsql:dbname={$name} host={$host}", $user, $pass); 
					break;
					
				case 'odbc':
					$this->dbh = new PDO("odbc:{$name}", $user, $pass); 
					break;
					
				case 'sqlite':
					$this->dbh = new PDO("sqlite:{$name}");
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
		$sth = $this->dbh->prepare("SELECT * FROM BNET WHERE username = ?");  
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



// D2GS telnet wrapper
class Telnet
{
	private $socket;
	
	function __construct($host, $port)
	{
		// create socket
		$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (!$this->socket)
			throw new Exception( "Error Creating Socket: ".socket_strerror(socket_last_error()) );
		
		// set timeout
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));
		
		// create connection
		if ( !@socket_connect($this->socket, $host, $port) )
		{
			socket_close($this->socket);
			throw new Exception( "Error Connecting Telnet: ".socket_strerror(socket_last_error()) );
		}
	}
	
	public function login($password)
	{
		// read welcome message
		while ( $out = @socket_read($this->socket, 1024) )
			if( preg_match('/Password:/i', $out) )
				break;

		// login
		$this->_cmd($password);
		while ( $out = @socket_read($this->socket, 1024) )
			if( preg_match('/D2GS>/i', $out) )
				return true;
			else if( preg_match('/Sorry!/i', $out) )
				return false;
	}
	
	// find character played in game
	//  return true if found, otherwise false
	public function find_char($char_name)
	{
		$this->_cmd('char ' . $char_name);
		while ( $out = @socket_read($this->socket, 1024) )
			if( preg_match('/char not found/i', $out) )
				return false;
				
		return true;
	}
	
	// kick character from game to chat
	public function kick_char($char_name)
	{
		$this->_cmd('kick ' . $char_name);
	}
	
	private function _cmd($command)
	{
		socket_write($this->socket, $command . "\n", strlen($command) + 1);
	}
	
	
	function __destruct()
	{
		if ($this->socket)
			socket_close($this->socket);
	}
	
}




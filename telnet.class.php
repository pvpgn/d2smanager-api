<?php
error_reporting(E_ALL);
echo "123";
print_r(PDO::getAvailableDrivers());
/*
$char_name = "HarpyWar";

try
{
	$d2gs = new Telnet('localhost', 8888);
	if ( !$d2gs->login('abcd123') )
		throw new Exception("Password is invalid");

	// check the character doesn't playing now
	if ( $d2gs->find_char($char_name) )
		throw new Exception("Please, leave your character from the game");
	else
	{
		echo "char not found";
		// do something with character file
	}
}
catch(Exception $e)
{
	echo $e->getMessage();
}
*/



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







	
	

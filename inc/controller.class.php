<?php

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

	/*
	* Return account info
	* Params: user
	*/
	public function getaccount()
	{
		$account = $this->getDB()->getAccount( $this->getParam('user') );
		if ($account != null)
			return $account;
		else
			throw new Exception('Account does not exists');
	}
	
	/*
	* Return character charsave file
	* Params: char
	*/
	public function getcharsave()
	{
		$this->checkcharingame();
		$this->isBinary = true; // binary result
		
		if ( $char = $this->io->readCharSave( $this->getParam('char') ) )
			return $char;
		else
			throw new Exception('Can not read the character');
	}
	
	/*
	* Save remote character into a file
	* Params: char, url
	*/
	public function savecharsave()
	{
		$this->checkcharingame();
		$this->isBinary = true; // binary result
		
		// decode url and download character binary data
		$url = base64_decode( $this->getParam('url') );
		$bytes = file_get_contents($url);
		
		if ( $char = $this->io->saveCharSave( $this->getParam('char'), $bytes ) )
			return $char;
		else
			throw new Exception('Can not read the character');
	}
	
	/*
	* Delete character file
	* Params: char, url
	*/
	public function deletecharsave()
	{
		$this->checkcharingame();
		
		if ( $char = $this->io->deleteCharSave( $this->getParam('char') ) )
			return $char;
		else
			throw new Exception('Can not delete the character');
	}
	
	
	/*
	* Return character charinfo file
	* Params: char
	*/
	public function getcharinfo()
	{
		$this->checkcharingame();
		$this->isBinary = true; // binary result
		
		if ( $char = $this->io->readCharInfo( $this->getParam('user'), $this->getParam('char') ) )
			return $char;
		else
			throw new Exception('Can not read the character');
	}
	
	/*
	* Save remote characterinfo into a file
	* Params: char, url
	*/
	public function savecharinfo()
	{
		$this->checkcharingame();
		$this->isBinary = true; // binary result
		
		// decode url and download character binary data
		$url = base64_decode( $this->getParam('url') );
		$bytes = file_get_contents($url);
		
		if ( $char = $this->io->saveCharInfo( $this->getParam('user'), $this->getParam('char'), $bytes ) )
			return $char;
		else
			throw new Exception('Can not read the character info');
	}
	
	/*
	* Delete character file
	* Params: char, url
	*/
	public function deletecharinfo()
	{
		$this->checkcharingame();
		
		if ( $char = $this->io->deleteCharInfo( $this->getParam('user'), $this->getParam('char') ) )
			return $char;
		else
			throw new Exception('Can not delete the character');
	}
	
	/*
	* Return all account charinfo files splitted into a binary stream
	* Params: user
	*/
	public function getallcharinfo()
	{
		$this->isBinary = true; // binary result
		
		if ( $chars = $this->io->readAllCharInfo( $this->getParam('user') ) )
			return $chars;
		else
			throw new Exception('Can not read characters');
	}
	


	
	
	/*
	* Check if character remain in the game
	* Params: char
	*/
	public function checkcharingame()
	{
		// check character doesn't playing now
		if ( $this->getTelnet()->find_char( $this->getParam('char') ) )
			throw new Exception("Please, leave your character from the game");
		else
			return false;
	}
	
	
	/*
	* Test api works properly on this server
	* Params: url, char, user (char and user must be unique, to create and delete their character files)
	*/
	public function test()
	{
		// check access for remote read
		$url = base64_decode( $this->getParam('url') );
		if ( !@file_get_contents( $this->getParam('url') ) )
			throw new Exception("Can not download data from remote url (using file_get_contents)");
		
		// check database configuration
		$this->getaccount();
		
		
		
		$charinfo_dir = $this->config->charinfo_path . $this->getParam('user');
		// try create charinfo an account directory if it not exists
		if ( !file_exists($charinfo_dir) )
			mkdir($charinfo_dir);
			
		// check telnet connection (inner check)
		// check write access in charsave directory
		$this->savecharsave();
		
		// check write access in charinfo directory
		$this->savecharinfo();
		
		// delete just created character files and charinfo account directory
		$this->deletecharsave();
		$this->deletecharinfo();
		rmdir($charinfo_dir);
		
		
		return true;
	}
	
	
	
	
	
/* ************ END API METHODS ************* */

	
	
	
	
	
	private $_db;
	
	// get database instance (lazy initialization)
	private function getDB()
	{
		if ($this->_db === null)
			$this->_db = new DB($this->config->db_type, $this->config->db_name, $this->config->db_host, $this->config->db_user, $this->config->db_pass, $this->config->table_prefix);
			
		return $this->_db;
	}

	
	private $_telnet;
	
	// get telnet instance (lazy initialization)
	private function getTelnet()
	{
		if ($this->_telnet === null)
		{
			$this->_telnet = new Telnet($this->config->telnet_host, $this->config->telnet_port);
			
			if ( !$this->_telnet->login($this->config->telnet_pass) )
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


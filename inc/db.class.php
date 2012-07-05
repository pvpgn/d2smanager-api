<?php


// database wrapper
class DB
{
	private $dbh;
	private $table_bnet;
	private $prefix;
	
	function __construct($type, $name, $host = false, $user = false, $pass = false, $table_prefix = false)
	{
		$this->prefix = $table_prefix;
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
		$sth = $this->dbh->prepare('SELECT * FROM ' . $this->prefix . 'BNET WHERE username = ?');  
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

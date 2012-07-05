<?php

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

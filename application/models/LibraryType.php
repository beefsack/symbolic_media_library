<?php

abstract class Model_LibraryType
{
	protected $_name = null;
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function generateLibrary($source, $destination)
	{
		throw new Exception('Not implemented yet');
	}
}
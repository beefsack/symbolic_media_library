<?php

abstract class Model_LibraryType
{
	protected $_name = null;
	
	public function getName()
	{
		return $this->_name;
	}
}
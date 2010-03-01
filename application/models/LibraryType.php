<?php

abstract class Model_LibraryType
{
	
	protected $_name;
	protected $_database;
	protected $_databasePath;
	protected $_source;
	protected $_destination;
	
	const DATABASE_NAME = 'sml.xml';
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function generateLibrary()
	{
		// Check properties
		if ($this->_source === null || $this->_destination === null) {
			throw new Exception('Source or destination not yet set');
		}
		
		// Initialise database
		if (($this->_databasePath = realpath($this->_destination.'/'.self::DATABASE_NAME))) {
			if (($this->_database = simplexml_load_file($this->_databasePath)) === false) {
				$this->_database = new SimpleXMLElement('<library></library>');
			}
		} else {
			$this->_database = new SimpleXMLElement('<library></library>');
		}
		
		// Populate database
		$this->_parseSource($this->_source);
		
		throw new Exception($this->_database->asXML());
		
		throw new Exception('Not implemented yet');
	}
	
	/**
	 * Sets the source directory
	 * @param string $source
	 * @return Model_LibraryType
	 */
	public function setSource($source)
	{
		if (!$source) {
			throw new Exception('No source specified');
		}
		if (($this->_source = realpath($source)) === false) {
			throw new Exception('Unable to find file '.$source);
		}
		if (!is_dir($this->_source)) {
			throw new Exception('Source '.$this->_source.' is not a directory');
		}
		return $this;
	}
	
	/**
	 * Sets the destination directory
	 * @param string $destination
	 * @return Model_LibraryType
	 */
	public function setDestination($destination)
	{
		if (!$destination) {
			throw new Exception('No destination specified');
		}
		if (($this->_destination = realpath($destination)) === false) {
			throw new Exception('Unable to find file '.$destination);
		}
		if (!is_dir($this->_destination)) {
			throw new Exception('Destination '.$this->_destination.' is not a directory');
		}
		return $this;
	}
	
	/**
	 * Recurses the source and fills the database.  Does not follow symbolic links.
	 * @param string $source
	 */
	protected function _parseSource($source)
	{
		if (is_dir($source) && !is_link($source) && ($dir = opendir($source)) !== false) {
			$pathinfo = pathinfo($source);
			if (!$this->_database->xpath('//item[@id="'.str_replace('"', '&quot;', $pathinfo['basename']).'"]') && $data = $this->_getData($pathinfo['basename'])) {
				$item = $this->_database->addChild('item');
				$item->addAttribute('id', $pathinfo['basename']);
				$this->_setData($item, $data);
			}
			// Parse children
			while (($file = readdir($dir)) !== false) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				$this->_parseSource($source.'/'.$file);
			}
			closedir($dir);
		}
	}

	/**
	 * Recurses an array to set data to simplexml elements
	 * @param $element
	 * @param $data
	 * @param $parent
	 */
	protected function _setData(SimpleXMLElement $element, array $data, $parent = null)
	{
		foreach ($data as $key => &$value) {
			if (is_int($key)) {
				if ($parent !== null) {
					if (is_array($value)) {
						$child = $element->addChild($parent);
						$this->_setData($child, $value, $parent);
					} else {
						$element->addChild($parent, $value);
					}
				}
			} else {
				if (is_array($value)) {
					if (!is_int(reset(array_keys($value)))) {
						$child = $element->addChild($key);
					} else {
						$child = $element;
					}
					$this->_setData($child, $value, $key);
				} else {
					$element->addChild($key, $value);
				}
			}
		}
	}
	
	/**
	 * Gets related data from a directory.  An empty array will mean the item won't be added to the library
	 * @param string $directory
	 * @return array
	 */
	abstract protected function _getData($directory);
	
}
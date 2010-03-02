<?php

abstract class Model_LibraryType
{
	
	protected $_name;
	protected $_database;
	protected $_databasePath;
	protected $_source;
	protected $_destination;
	protected $_structure;
	protected $_pluginBase;
	
	const DATABASE_NAME = 'sml.xml';
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function generateLibrary()
	{
		set_time_limit(0); // This script may take a long time
		
		// Check properties
		if ($this->_source === null || $this->_destination === null) {
			throw new Exception('Source or destination not yet set');
		}
		
		// Check plugin base is set
		
		// Initialise database
		if (($this->_databasePath = realpath($this->_destination).'/'.self::DATABASE_NAME)) {
			if (($this->_database = simplexml_load_file($this->_databasePath)) === false) {
				$this->_database = new SimpleXMLElement('<library></library>');
			}
		} else {
			$this->_database = new SimpleXMLElement('<library></library>');
		}
		
		// Populate database
		$this->_parseSource($this->_source);
		
		// Save database
		if (($handle = fopen($this->_databasePath, 'w')) === false) {
			throw new Exception('Unable to open database file at '.$this->_databasePath);
		}
		if (fwrite($handle, $this->_database->asXML()) === false) {
			fclose($handle);
			throw new Exception('Unable to write to database file at '.$this->_databasePath);		
		}
		fclose($handle);
		
		// Build structure from database
		$this->_buildStructure();
		
		// Create links from structure
		$this->_createLinks($this->_destination, $this->_structure);
	}
	
	protected function _createLinks($directory, array $structure)
	{
		if (($directory = realpath($directory)) === false) {
			throw new Exception('Could not find directory');
		}
		if (!is_dir($directory)) {
			throw new Exception($directory.' is not a directory for generating links');
		}
		foreach ($structure as $key => &$value) {
			if (is_array($value)) {
				// Create dir if needed and keep recursing
				if (file_exists($directory.'/'.$key)) {
					if (!is_dir($directory.'/'.$key)) {
						continue;
					}
				} else {
					mkdir($directory.'/'.$key);
				}
				$this->_createLinks($directory.'/'.$key, $value);
			} else {
				// Create symlink
				if (file_exists($value)) {
					if (file_exists($directory.'/'.$key)) {
						if (is_link($directory.'/'.$key)) {
							unlink($directory.'/'.$key);
						} else {
							continue;
						}
					}
					if (!symlink($value, $directory.'/'.$key)) {
						throw new Exception('Unable to make symlink at '.$directory.'/'.$key.' to '.$value);
					}
				}
			}
		}
	}
	
	protected function _buildStructure()
	{
		if (!class_exists($this->_pluginBase)) {
			throw new Exception('Plugin base class does not exist');
		}
		// Fetch and run plugins
		$classes = Model_ClassList::getClasses(array('parent' => $this->_pluginBase));
		$this->_structure = array();
		foreach ($classes as $class) {
			$plugin = new $class;
			$this->_structure = array_merge_recursive($plugin->getStructure($this->_database), $this->_structure);
		}
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
				$data['path'] = $source;
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
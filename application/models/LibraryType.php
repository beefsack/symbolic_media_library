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
	protected $_logger;
	protected $_logPath;
	protected $_relativeDestinationToSource;
	protected $_commonPath;
	protected $_replaceRules = array(
		'/:/' => '-',
		'/\//' => '-',
	);
	
	const DATABASE_NAME = 'sml.xml';
	const LOG_NAME = 'sml.log';
	
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
		
		// Initialise logger
		$this->_logPath = realpath($this->_destination).'/'.self::LOG_NAME;
		$writer = new Zend_Log_Writer_Stream($this->_logPath, 'w');
		$this->_logger = new Zend_Log($writer);
		$this->_logger->info('Log initialised, commencing library generation.');
		
		// Initialise database
		$this->_logger->info('Initialising database.');
		if (($this->_databasePath = realpath($this->_destination).'/'.self::DATABASE_NAME)) {
			$this->_logger->info('Found a database at '.$this->_databasePath.', loading.');
			if (($this->_database = simplexml_load_file($this->_databasePath)) === false) {
				$this->_logger->info('Unable to load database, creating a new one.');
				$this->_database = new SimpleXMLElement('<library></library>');
			}
		} else {
			$this->_logger->info('Creating a new database.');
			$this->_database = new SimpleXMLElement('<library></library>');
		}
		
		// Validate database
		$this->_logger->info('Validating database links.');
		$this->_validateDatabase();
		
		// Populate database
		$this->_logger->info('Populating database.');
		$this->_parseSource($this->_source);
		
		// Save database
		$this->_logger->info('Saving database to '.$this->_databasePath.'.');
		if (($handle = fopen($this->_databasePath, 'w')) === false) {
			throw new Exception('Unable to open database file at '.$this->_databasePath);
		}
		if (fwrite($handle, $this->_database->asXML()) === false) {
			fclose($handle);
			throw new Exception('Unable to write to database file at '.$this->_databasePath);		
		}
		fclose($handle);
		
		// Build structure from database
		$this->_logger->info('Building directory structure from database.');
		$this->_buildStructure();
		
		// Detect relative path
		$this->_logger->info('Detecting relative path.');
		$this->_getRelativePathValues();
		
		// Create links from structure
		$this->_logger->info('Creating symbolic links from structure.');
		$this->_createLinks($this->_destination, $this->_structure);
		
		$this->_logger->info('Library generation successful.');
	}
	
	protected function _createLinks($directory, array $structure, $depth = 0)
	{
		if (($directory = realpath($directory)) === false) {
			throw new Exception('Could not find directory');
		}
		if (!is_dir($directory)) {
			throw new Exception($directory.' is not a directory for generating links');
		}
		foreach ($structure as $key => &$value) {
			// Clean the key
			$cleanKey = $this->_replace($key);
			if (is_array($value)) {
				// Create dir if needed and keep recursing
				if (file_exists($directory.'/'.$cleanKey)) {
					if (!is_dir($directory.'/'.$cleanKey)) {
						continue;
					}
				} else {
					mkdir($directory.'/'.$cleanKey);
				}
				$this->_createLinks($directory.'/'.$cleanKey, $value, $depth + 1);
			} else {
				// Create symlink
				$this->_logger->info('Creating symlink at '.$directory.'/'.$cleanKey.' to '.$this->_source.'/'.$value.'.');
				if (file_exists($this->_source.'/'.$value)) {
					if (file_exists($directory.'/'.$cleanKey)) {
						if (is_link($directory.'/'.$cleanKey)) {
							unlink($directory.'/'.$cleanKey);
						} else {
							continue;
						}
					}
					$target = str_repeat('../', $depth).$this->_relativeDestinationToSource.'/'.$value;
					exec('cd '.escapeshellarg($directory).'; ln -s '.escapeshellarg($target).' '.escapeshellarg($cleanKey), $output, $return_var);
					if ($return_var > 0) {
						throw new Exception('Unable to make symlink at '.$directory.'/'.$cleanKey.' to '.$target);
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
			$this->_logger->info('Running plugin '.$class.'.');
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
		$this->_logger->info('Parsing '.$source.'.');
		if (is_dir($source) && !is_link($source) && ($dir = opendir($source)) !== false) {
			$pathinfo = pathinfo($source);
			if (!$this->_database->xpath('//item[@id="'.str_replace('"', '&quot;', $pathinfo['basename']).'"]') && $data = $this->_getData($pathinfo['basename'])) {
				$this->_logger->info('Fetched data for '.$pathinfo['basename'].'.');
				$data['path'] = str_replace($this->_source, '', $source);
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
	
	protected function _xmlEscape($strin)
	{
		$strin = (string) $strin;
		$strout = null;
		for ($i = 0; $i < strlen($strin); $i++) {
//			$ord = ord($strin[$i]);
//			if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
//				$strout .= "&amp;#{$ord};";
//			} else {
				switch ($strin[$i]) {
					case '<':
						$strout .= '&lt;';
						break;
					case '>':
						$strout .= '&gt;';
						break;
					case '&':
						$strout .= '&amp;';
						break;
					case '"':
						$strout .= '&quot;';
						break;
					default:
						$strout .= $strin[$i];
				}
//			}
		}
		return $strout;
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
						$element->addChild($parent, $this->_xmlEscape($value));
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
					$element->addChild($key, $this->_xmlEscape($value));
				}
			}
		}
	}
	
	protected function _validateDatabase()
	{
		$remove = array();
		foreach ($this->_database as $item) {
			if (!realpath($this->_source.'/'.(string) $item->path)) {
				$remove[] = $item;
			}
		}
		foreach ($remove as $item) {
			$this->_logger->info('Path '.$item->path.' for item '.$item['id'].' not found, removing from database.');
			$dom = dom_import_simplexml($item);
			$dom->parentNode->removeChild($dom);
		}
	}
	
	/**
	 * Gets related data from a directory.  An empty array will mean the item won't be added to the library
	 * @param string $directory
	 * @return array
	 */
	abstract protected function _getData($directory);
	
	static public function getLibraryTypes()
	{
		$types = Model_ClassList::getClasses(array(
			'parent' => 'Model_LibraryType',
		));
		$typeList = array();
		foreach ($types as $type) {
			$obj = new $type;
			$typeList[$type] = $obj->getName();
		}
		return $typeList;
	}
	
	protected function _getRelativePathValues()
	{
		
		// Build directory arrays
		
		$sourceArray = $this->_getPathArray($this->_source);
		$destinationArray = $this->_getPathArray($this->_destination);
		
		// Find common depth
		
		$commonDepth = 0;
		while ($commonDepth < count($sourceArray)) {
			if ($sourceArray[$commonDepth] != $destinationArray[$commonDepth]) {
				break;
			}
			$commonDepth++;
		}
		
		// Set values
		
		$this->_commonPath = '/'.implode('/', array_slice($sourceArray, 0, $commonDepth));
		$this->_relativeDestinationToSource = str_repeat('../', count($destinationArray) - $commonDepth).'/'.implode('/', array_slice($sourceArray, $commonDepth));
		
	}
	
	protected function _getPathArray($path)
	{
		if ($path == '/') {
			return array();
		} else {
			return explode('/', preg_replace('/^\//', '', $path));
		}
	}
	
	protected function _replace($string)
	{
		foreach ($this->_replaceRules as $rule => $replacement) {
			$string = preg_replace($rule, $replacement, $string);
		}
		return $string;
	}
	
}

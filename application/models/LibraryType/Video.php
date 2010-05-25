<?php

class Model_LibraryType_Video extends Model_LibraryType
{
	protected $_name = 'Video Library';
	protected $_pluginBase = Model_LibraryPlugin_Video;
	
	protected function _getData($directory)
	{
		// Check if the directory is 7 numbers (imdb ID format)
		if (!preg_match('/\d{7}/', $directory)) {
			return array();
		}
		
		// Fetch data
		$imdb = new Model_Parser_Imdb(array(
			'id' => $directory,
		));
		$data = $imdb->data();
		
		// Check data
		if (!$data['title']) {
			return array();
		}
		
		// Clean encodings out of data
		
		return $this->_decodeValues($data);
	}
	
	protected function _decodeValues($data)
	{
		if (is_array($data)) {
			$returnArray = array();
			foreach ($data as $key => &$value) {
				$returnArray[html_entity_decode($key, ENT_COMPAT, 'UTF-8')] = $this->_decodeValues($value);
			}
			return $returnArray;
		} else {
			return html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		}
	}
}

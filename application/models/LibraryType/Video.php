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
		
		return $data;
	}
}

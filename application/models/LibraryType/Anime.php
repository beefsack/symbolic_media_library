<?php

class Model_LibraryType_Anime extends Model_LibraryType
{
	protected $_name = 'Anime Library';
	protected $_pluginBase = 'Model_LibraryPlugin_Anime';
	
	protected function _getData($directory)
	{
		if (!preg_match('/^\d+$/', $directory)) {
			return array();
		}
		
		$ann = new Model_Parser_Ann(array(
			'id' => $directory,
		));
		return $ann->data();
	}
}

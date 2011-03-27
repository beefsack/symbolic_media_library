<?php

class Model_LibraryType_Tv extends Model_LibraryType
{
	protected $_name = 'TV Library';
	protected $_pluginBase = 'Model_LibraryPlugin_Tv';
	
	protected function _getData($directory)
	{
		$tvdb = new Model_Parser_Tvdb(array(
			'id' => $directory,
		));
		return $tvdb->data();
	}
}

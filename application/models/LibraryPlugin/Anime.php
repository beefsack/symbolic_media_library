<?php 

abstract class Model_LibraryPlugin_Anime extends Model_LibraryPlugin
{
	
	protected function _parseTitle($title)
	{
//		// Move the/a to the end of the title
//		if (preg_match('/^(the|a)\s+(.*)/i', $title, $matches)) {
//			$title = $matches[2].', '.$matches[1];
//		}
		return $title;
	}
	
	protected function _buildTitle(SimpleXMLElement $data)
	{
		return $this->_parseTitle($data->titles->main);
	}
	
	
}
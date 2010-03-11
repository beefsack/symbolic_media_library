<?php 

abstract class Model_LibraryPlugin_Video extends Model_LibraryPlugin
{
	
	protected function parseTitle($title)
	{
		// Move the/a to the end of the title
		if (preg_match('/^(the|a)\s+(.*)/i', $title, $matches)) {
			$title = $matches[2].', '.$matches[1];
		}
		return $title;
	}
	
	protected function buildTitle(SimpleXMLElement $data)
	{
		return $this->parseTitle($data->title).' ('.$data->year.')';
	}
	
	
}
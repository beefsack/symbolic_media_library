<?php

class Model_LibraryPlugin_Anime_Rating extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		
		$structure = array();
		
		foreach ($data->item as $item) {
			$rating = (string) $item->stats->medianrating;
			if (!$rating) {
				$rating = 'Unrated';
			}
			$structure[$rating][$this->_buildTitle($item)] = (string) $item->path;
		}
		
		return array('By Rating' => $structure);
		
	}
}

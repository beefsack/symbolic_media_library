<?php

class Model_LibraryPlugin_Anime_Rank extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		
		$structure = array();
		
		foreach ($data->item as $item) {
			if ((int) $item->stats->rank) {
				$prepend = '['.sprintf('%04d', $item->stats->rank).']';
			} else {
				$prepend = '[Unranked]';
			}
			$structure[$prepend.' '.$this->_buildTitle($item)] = (string) $item->path;
		}
		
		return array('By Rank' => $structure);
		
	}
}
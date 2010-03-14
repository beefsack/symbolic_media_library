<?php

class Model_LibraryPlugin_Anime_Genre extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$genres = array();
		foreach ($data->item as $item) {
			foreach ($item->genres as $genre) {
				if ((string) $genre->name) {
					$genres[ucwords($genre->name)][$this->_buildTitle($item)] = (string) $item->path;
				}
			}
		}
		foreach ($genres as $genre => $item) {
			$structure[$genre] = $this->_structureByLetter($item);
		}
		return array('By Genre' => $genres);
	}
}
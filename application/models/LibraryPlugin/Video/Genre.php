<?php

class Model_LibraryPlugin_Video_Genre extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$genres = array();
		foreach ($data->item as $item) {
			foreach ($item->genres as $genre) {
				$genres[(string) $genre][$this->_buildTitle($item)] = (string) $item->path;
			}
		}
		foreach ($genres as $genre => $item) {
			$structure[$genre] = $this->_structureByLetter($item);
		}
		return array('By Genre' => $genres);
	}
}
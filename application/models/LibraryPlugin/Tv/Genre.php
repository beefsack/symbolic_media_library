<?php

class Model_LibraryPlugin_Tv_Genre extends Model_LibraryPlugin_Tv
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$genres = array();
		foreach ($data->item as $item) {
			foreach ($item->Genre as $genre) {
				$name = (string) $genre;
				if (!$name) continue;
				$genres[$name][$this->_buildTitle($item)] = (string) $item->path;
			}
		}
		foreach ($genres as $genre => $item) {
			$structure[$genre] = $this->_structureByLetter($item);
		}
		return array('By Genre' => $genres);
	}
}
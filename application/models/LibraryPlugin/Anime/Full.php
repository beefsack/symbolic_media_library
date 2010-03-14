<?php

class Model_LibraryPlugin_Anime_Full extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$structure[$this->_buildTitle($item)] = (string) $item->path;
		}
		return array('By Title' => $this->_structureByLetter($structure));
	}
}
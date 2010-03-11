<?php

class Model_LibraryPlugin_Video_Genre extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			foreach ($item->genres as $genre) {
				$structure['By Genre'][(string) $genre][$this->buildTitle($item)] = (string) $item->path;
			}
		}
		return $structure;
	}
}
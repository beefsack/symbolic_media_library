<?php

class Model_LibraryPlugin_Video_Year extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$structure['By Year'][(string) $item->year][$this->buildTitle($item)] = (string) $item->path;
		}
		return $structure;
	}
}
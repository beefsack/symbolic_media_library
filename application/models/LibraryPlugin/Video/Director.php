<?php

class Model_LibraryPlugin_Video_Director extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			foreach ($item->director as $director) {
				$structure['By Director'][(string)$director->name][$this->buildTitle($item)] = (string) $item->path;
			}
		}
		return $structure;
	}
}
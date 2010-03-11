<?php

class Model_LibraryPlugin_Video_Full extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$structure['By Title'][$this->buildTitle($item)] = (string) $item->path;
		}
		return $structure;
	}
}
<?php

class Model_LibraryPlugin_Video_Language extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$structure['By Language'][(string) $item->language][$item->title.' ('.$item->year.')'] = (string) $item->path;
		}
		return $structure;
	}
}
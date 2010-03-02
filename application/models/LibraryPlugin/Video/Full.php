<?php

class Model_LibraryPlugin_Video_Full extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$structure['By Title'][$item->title.' ('.$item->year.')'] = (string) $item->path;
		}
		return $structure;
	}
}
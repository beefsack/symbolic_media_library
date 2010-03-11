<?php

class Model_LibraryPlugin_Video_Language extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$languages = array();
		foreach ($data->item as $item) {
			$languages[(string) $item->language][$this->_buildTitle($item)] = (string) $item->path;
		}
		foreach ($languages as $language => $item) {
			$structure[$language] = $this->_structureByLetter($item);
		}
		return array('By Language' => $languages);
	}
}
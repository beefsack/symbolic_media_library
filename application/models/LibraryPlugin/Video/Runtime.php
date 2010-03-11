<?php

class Model_LibraryPlugin_Video_Runtime extends Model_LibraryPlugin_Video
{
	const INCREMENT = 30;
	
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			$bracket = ((floor(((int) $item->runtime) / self::INCREMENT)) * self::INCREMENT);
			$structure['By Runtime'][$bracket.' to '.($bracket + self::INCREMENT).' minutes'][$this->buildTitle($item)] = (string) $item->path;
		}
		return $structure;
	}
}
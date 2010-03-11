<?php

class Model_LibraryPlugin_Video_Full extends Model_LibraryPlugin_Video
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
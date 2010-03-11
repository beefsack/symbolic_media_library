<?php

class Model_LibraryPlugin_Video_Director extends Model_LibraryPlugin_Video
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		foreach ($data->item as $item) {
			foreach ($item->director as $director) {
				$structure[(string)$director->name][$this->_buildTitle($item)] = (string) $item->path;
			}
		}
		return array('By Director' => $this->_structureByLetter($structure));
	}
}
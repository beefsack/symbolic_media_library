<?php

class Model_LibraryPlugin_Anime_Theme extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$themes = array();
		foreach ($data->item as $item) {
			foreach ($item->themes as $theme) {
				if ((string) $theme->name) {
					$themes[ucwords($theme->name)][$this->_buildTitle($item)] = (string) $item->path;
				}
			}
		}
		foreach ($themes as $theme => $item) {
			$structure[$theme] = $this->_structureByLetter($item);
		}
		return array('By Theme' => $themes);
	}
}
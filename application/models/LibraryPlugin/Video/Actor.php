<?php

class Model_LibraryPlugin_Video_Actor extends Model_LibraryPlugin_Video
{
	const MAX_ACTORS = 100;
	
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		// Count appearances in library for actors
		$actors = array();
		foreach ($data->item as $item) {
			foreach ($item->cast as $cast) {
				$actors[(string) $cast->name]++;
			}
		}
		// order array, reduce to max size
		array_multisort($actors, SORT_DESC, array_keys($actors), SORT_STRING, $actors);
		$actors = array_slice($actors, 0, self::MAX_ACTORS);
		// create structure
		foreach ($data->item as $item) {
			foreach ($item->cast as $cast) {
				if (in_array((string) $cast->name, array_keys($actors))) {
					$structure['By Actor'][(string)$cast->name][$this->buildTitle($item)] = (string) $item->path;
				}
			}
		}
		return $structure;
	}
}
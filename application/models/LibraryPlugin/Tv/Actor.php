<?php

class Model_LibraryPlugin_Tv_Actor extends Model_LibraryPlugin_Tv
{
	const MAX_ACTORS = 100;
	
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$actorData = array();
		$prolific = array();
		// Count appearances in library for actors
		foreach ($data->item as $item) {
			foreach ($item->Actors as $actor) {
				$name = (string) $actor;
				if (!$name) continue;
				$actors[$name]++;
			}
		}
		// order array, reduce to max size
		array_multisort($actors, SORT_DESC, array_keys($actors), SORT_STRING, $actors);
		$actors = array_slice($actors, 0, self::MAX_ACTORS);
		// create structure
		foreach ($data->item as $item) {
			foreach ($item->Actors as $actor) {
				$name = (string) $actor;
				if (!$name) continue;
				$actorData[$name][$this->_buildTitle($item)] = (string) $item->path;
				if (in_array($name, array_keys($actors))) {
					$prolific[$name][$this->_buildTitle($item)] = (string) $item->path;
				}
			}
		}
//		$structure['By Actor'] = $this->_structureByLetter($actorData);
		$structure['By Actor']['_Prolific'] = $this->_structureByLetter($prolific);
		return $structure;
	}
}
<?php

class Model_LibraryPlugin_Anime_Family extends Model_LibraryPlugin_Anime
{
	public function getStructure(SimpleXMLElement $data)
	{
		$items = array();
		$families = array();
		$structure = array();
		// Discover relations and create families based on them
		foreach ($data->item as $item) {
			$id = (int) $item['id'];
			// Create array with details for building symlinks
			$items[$id] = array(
				'title' => $this->_buildTitle($item),
				'path' => (string) $item->path,
			);
			// Add to family groups
			$relatedGroups = array();
			foreach ($families as $key => $family) {
				// Check if any related items are in this group
				foreach ($item->related as $relation) {
					if ((string) $relation->type == 'anime' && in_array((int) $relation->id, $family)) {
						$relatedGroups[] = $key;
						continue 2;
					}
				}
			}
			if (count($relatedGroups > 1)) {
				// Merge the families together
				$newFamily = array();
				foreach ($relatedGroups as $relatedGroup) {
					$newFamily = array_merge($newFamily, $families[$relatedGroup]);
					unset($families[$relatedGroup]);
				}
				$newFamily[] = $id;
				$families[] = $newFamily;
			} elseif (count($relatedGroups == 1)) {
				$families[reset($relatedGroups)][] = $id;
			} else {
				$families[][] = $id;
			}
		}
		// Discover family names and build folders based on that
		foreach ($families as $family) {
			$familyNames = array();
			$familyItems = array();
			foreach ($family as $id) {
				$familyNames[] = $items[$id]['title'];
				$familyItems[$items[$id]['title']] = $items[$id]['path'];
			}
			if (count($family) > 3) {
				$familyName = Model_StringCombine::combineStrings($familyNames);
			}
			$structure[$familyName] = $familyItems;
		}
		return array('By Family' => $structure);
	}
}
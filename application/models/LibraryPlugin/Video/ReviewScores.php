<?php

class Model_LibraryPlugin_Video_ReviewScores extends Model_LibraryPlugin_Video
{
	const MIN_VOTES = 500;
	
	public function getStructure(SimpleXMLElement $data)
	{
		$structure = array();
		$minimum = array();
		foreach ($data->item as $item) {
			if ((int) $item->votes > self::MIN_VOTES) {
				$minimum[] = (string) $item['id'];
			}
		}
		foreach ($data->item as $item) {
			if (in_array((string) $item['id'], $minimum)) {
				$structure['By Review Score'][(string) floor($item->rating)][$item->title.' ('.$item->year.')'] = (string) $item->path;
				$structure['By Review Score']['All']['['.number_format((float) $item->rating, 1).'] '.$item->title.' ('.$item->year.')'] = (string) $item->path;
			}
		}
		return $structure;
	}
}
<?php

abstract class Model_LibraryPlugin
{
	abstract public function getStructure(SimpleXMLElement $data);
	
	protected function _structureByLetter(array $data, array $options = array())
	{
		$options = array_merge(array(
			'createAll' => true,
		), $options);
		$structure = array();
		foreach ($data as $key => &$item) {
			if (preg_match('/^[a-zA-Z]/', $key)) {
				$structure[$key[0]][$key] = $item;
			} elseif (preg_match('/^\d/', $key)) {
				$structure['0-9'][$key] = $item;
			} else {
				$structure['Other'][$key] = $item;
			}
			if ($options['createAll']) {
				$structure['_All'][$key] = $item;
			}
		}
		return $structure;
	}
}
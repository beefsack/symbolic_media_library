<?php

class Model_StringCombine
{
	const MINIMUM_KEEP = 0.51;
	
	static public function combineStrings(array $strings)
	{
		$count = count($strings);
		if ($count == 0) {
			throw new Exception('No strings passed to outliers');
		}
		if ($count == 1) {
			return reset($strings);
		}
		echo "<pre>";
		$strings = self::trimOutliers($strings);
		var_dump($strings);exit;
	}
	
	static public function trimOutliers(array $strings)
	{
		$count = count($strings);
		if ($count == 0) {
			throw new Exception('No strings passed to outliers');
		}
		if ($count == 1) {
			return reset($strings);
		}
		$totalDistance = array();
		foreach ($strings as $key => $string) {
			foreach ($strings as $subKey => $subString) {
				if ($key != $subKey) {
					$totalDistance[$key] += levenshtein($string, $subString);
				}
			}
		}
		$keep = ceil($count * self::MINIMUM_KEEP);
		array_multisort($totalDistance, SORT_NUMERIC, SORT_ASC, $strings);
		return array_slice($strings, 0, $keep);
	}
}
<?php

class Model_StringCombine
{
	const MINIMUM_KEEP = 0.51;
	const STRIP = '/[^a-zA-Z0-9 ]/';
	
	static public function combineStrings(array $strings)
	{
		$count = count($strings);
		if ($count == 0) {
			throw new Exception('No strings passed to outliers');
		}
		if ($count == 1) {
			return reset($strings);
		}
		$strings = self::trimOutliers($strings);
		
		// Search for matches for remaining strings
		
		$match = null;
		foreach ($strings as $string) {
			if ($match === null) {
				$match = $string;
				continue;
			}
			$match = self::findCommonString($match, $string);
			if ($match === false) {
				break;
			}
		}
		
		return $match;
		
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
	
	static public function findCommonString($string1, $string2)
	{
		
		// Force type
		
		$string1 = preg_replace(self::STRIP, '', $string1);
		$string2 = preg_replace(self::STRIP, '', $string2);
		
		// Use the shorter string as the pattern to match
		
		if (strlen($string1) > strlen($string2)) {
			$pattern = $string1;
			$text = $string2;
		} else {
			$pattern = $string2;
			$text = $string1;
		}
		
		// Search for a match
		
		$searchPieces = explode(' ', $pattern);
		
		while (count($searchPieces) > 0) {
			$subSearchPieces = $searchPieces;
			while (count($subSearchPieces) > 0) {
				$search = implode(' ', $subSearchPieces);
				if (stripos($text, $search) !== false) {
					return $search;
				}
				array_pop($subSearchPieces);
			}
			array_shift($searchPieces);
		}
		
		// Failed to match, return false
		
		return false;
		
	}
}
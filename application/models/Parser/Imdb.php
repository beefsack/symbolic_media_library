<?php

require_once('imdbphp2/imdb.class.php');

class Model_Parser_Imdb extends Model_Parser
{
	protected $_requiredOptions = array(
		'id'
	);
	
	protected function _fetchData()
	{
		$movie = new imdb($this->_options['id']);
		return $movie;
	}
	
	protected function _parseData($data)
	{
		return $data;
	}
	
}
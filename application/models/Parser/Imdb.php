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
		$this->_data = array();
		if ($data instanceof imdb) {
			$this->_data['cast'] = $data->cast();
			$this->_data['country'] = $data->country();
			$this->_data['director'] = $data->director();
			$this->_data['genres'] = $data->genres();
			$this->_data['language'] = $data->language();
			$this->_data['rating'] = (float) $data->rating();
			$this->_data['runtime'] = (int) $data->runtime();
			$this->_data['title'] = $data->title();
			$this->_data['votes'] = (int) str_replace(',', '', $data->votes());
			$this->_data['year'] = (int) $data->year();
		}
		return $this->_data;
	}
	
}
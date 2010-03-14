<?php

require_once('ANNScraper.php');

class Model_Parser_Ann extends Model_Parser
{
	protected $_requiredOptions = array(
		'id'
	);
	
	protected function _fetchData()
	{
		$scraper = new ANNScraper();
		return $scraper->fetchAnime($this->_options['id']);
	}
	
	protected function _parseData($data)
	{
		$this->_data = $data;
		return $this->_data;
	}
	
}
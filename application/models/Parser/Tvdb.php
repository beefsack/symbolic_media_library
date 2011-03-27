<?php

require_once('TheTVDBAdapter.php');

// API key DE7F2177E690663F

class Model_Parser_Tvdb extends Model_Parser
{
	protected $_requiredOptions = array(
		'id'
	);
	
	protected function _fetchData()
	{

		$tvdb = new \TheTVDB\Adapter();
		$series = $tvdb->setKey('DE7F2177E690663F')
				->getSeries($this->_options['id']);

		$data = $series->toArray();

		$data['Actors'] = $series->getActors();
		$data['Genre'] = $series->getGenres();

		return $data;

	}
	
	protected function _parseData($data)
	{
		$this->_data = $data;
		return $this->_data;
	}
	
}
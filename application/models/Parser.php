<?php

abstract class Model_Parser
{
	
	protected $_options = array();
	protected $_requiredOptions = array();
	protected $_data;
	
	public function __construct(array $options)
	{
		foreach ($this->_requiredOptions as $option) {
			if (!isset($options[$option])) {
				throw new Model_Parser_Exception_MissingOption('Missing required option: '.$option);
			}
		}
		$this->_options = $options;
	}
	
	abstract protected function _fetchData();
	
	abstract protected function _parseData($data);
	
	public function data()
	{
		if (!isset($this->_data)) {
			$this->_data = $this->_parseData($this->_fetchData());
		}
		return $this->_data;
	}
	
}

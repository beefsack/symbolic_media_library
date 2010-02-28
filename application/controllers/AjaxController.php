<?php

class AjaxController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}

	public function fetchdirectoryAction()
	{
		$path = str_replace('\\', '/', realpath($this->_getParam('path', dirname(__FILE__))));
		if (!$path) {
			$path = '/';
		}
		$directory = array(
    		'path' => $path,
		);
		$files = array();
		$fileslower = array();
		$directories = array();
		if ($dir = opendir($path)) {
			while (($file = readdir($dir)) !== false) {
				$files[] = array(
    				'file' => $file,
    				'directory' => is_dir($path.'/'.$file),
				);
				$fileslower[] = strtolower($file);
				$directories[] = (int) is_dir($path.'/'.$file);
			}
		}
		// Put directories up top, then sort by filename case insensitive
		array_multisort($directories, SORT_NUMERIC, SORT_DESC, $fileslower, SORT_STRING, $files);
		$directory['files'] = $files;
		echo Zend_Json::encode($directory);
	}

	public function createdirectoryAction()
	{
		try {
			$path = realpath($this->_getParam('path'));
			if ($path[strlen($path) - 1] != '/') {
				$path .= '/';
			}
			$name = trim($this->_getParam('name'));
			if (!$name) {
				throw new Exception('Must specify a name');
			}
			if (strpos($name, '/') !== false) {
				throw new Exception('Do not include slashes in the name');
			}
			if (!@dir($path)) {
				throw new Exception('Unable to open directory '.$path);
			}
			if (!@mkdir($path.$name)) {
				throw new Exception('Unable to create directory at '.$path.$name);
			}
		} catch (Exception $e) {
			echo Zend_Json::encode(array(
    			'error' => $e->getMessage(),
			));
			return;
		}
		echo Zend_Json::encode(array(
   			'result' => true,
		));
	}
	
	public function generatelibraryAction()
	{
		ob_start();
		try {
			$type = $this->_getParam('type');
			if (!class_exists($type)) {
				throw new Exception('Class '.$type.' can not be found');
			}
			$library = new $type;
			if (!($library instanceof Model_LibraryType)) {
				throw new Exception($type.' is not an instance of Model_LibraryType');
			}
			$library->generateLibrary($this->_getParam('source'), $this->_getParam('destination'));
		} catch (Exception $e) {
			ob_end_clean();
			echo Zend_Json::encode(array(
				'error' => $e->getMessage(),
			));
			return;
		}
		ob_end_clean();
		echo Zend_Json::encode(array(
			'result' => true,
		));
	}

}


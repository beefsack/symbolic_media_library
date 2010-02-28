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
		$directory = array(
    		'path' => $path,
		);
		$files = array();
		if ($dir = opendir($path)) {
			while (($file = readdir($dir)) !== false) {
				$files[] = array(
    				'file' => $file,
    				'directory' => is_dir($path.'/'.$file),
				);
			}
		}
		$directory['files'] = $files;
		echo Zend_Json::encode($directory);
	}

}


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

}


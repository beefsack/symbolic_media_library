<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$this->view->source = $this->_getParam('source');
    	$this->view->destination = $this->_getParam('destination');
    	$this->view->type = $this->_getParam('type');
    	$this->view->types = Model_LibraryType::getLibraryTypes();
    }


}


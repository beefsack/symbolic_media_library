<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $test = new Model_Parser_Imdb(array(
        	'id' => '0499549',
        ));
        var_dump($test->data());
    }


}


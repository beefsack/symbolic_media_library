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
        	'id' => '0240772',
        ));
        echo "<pre>";
        var_dump($test->data());
    }


}


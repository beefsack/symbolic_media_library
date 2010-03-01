<?php

abstract class Model_LibraryPlugin
{
	abstract public function getStructure(SimpleXMLElement $data);
}
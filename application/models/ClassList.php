<?php

class Model_ClassList
{
	static function getClasses(array $options = array())
	{
		$options = array_merge(array(
			'parent' => null,
			'recursive' => true,
			'directory' => null,
			'allowAbstract' => false,
		), $options);
		if ($options['directory'] === null) {
			$options['directory'] = realpath(dirname(__FILE__));
			if ($options['parent'] !== null) {
				$options['directory'] .= '/'.str_replace('_', '/', str_replace('Model_', '', $options['parent']));
			}
		}
		if (($path = realpath($options['directory'])) === false) {
			throw new Exception('Directory does not exist '.$options['directory']);
		}
		if (!is_dir($path)) {
			throw new Exception('Is not a directory');
		}
		if (($dir = opendir($path)) === false) {
			throw new Exception('Unable to open directory');
		}
		$classList = array();
		while (($file = readdir($dir)) !== false) {
			if (in_array($file, array('.', '..'))) {
				continue;
			}
			if (is_dir($path.'/'.$file)) {
				if ($options['recursive']) {
					array_merge($classList, self::getClasses(array_merge($options, array('directory' => $path.'/'.$file))));
				}
			} else {
				if (preg_match('/^'.preg_quote(realpath(dirname(__FILE__)), '/').'[\/\\\\]?(.*)\.php$/', $path.'/'.$file, $matches)) {
					$className = 'Model_'.preg_replace('/\//', '_', $matches[1]);
					if (!class_exists($className)) {
						continue;
					}
					$ref = new ReflectionClass($className);
					if ($ref->isAbstract() && !$options['allowAbstract']) {
						continue;
					}
					if ($options['parent'] !== null && !is_subclass_of($className, $options['parent'])) {
						continue;
					}
					$classList[] = $className;
				}
			}
		}
		return $classList;
	}
}
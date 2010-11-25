<?php

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath('./library'),
    get_include_path(),
)));

// Initialise autoloader and append App_ namespases
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('App_');

$generator = new App_CodeGenerator_PhpDoc_Class('Zend_Application_Bootstrap_Bootstrap');
$generator->setPropertiesFilter(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
echo $generator->generate();

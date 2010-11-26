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

$generator = new App_CodeGenerator_PhpDoc_File(array(
    'classes'  => array('Zend_Application_Bootstrap_Bootstrap', 'Zend_Application_Module_Autoloader'),
    'filename' => 'Zend_Application',
));
$generator->write();

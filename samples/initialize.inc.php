<?php
/**
 * WebCore 3.0 Samples Browser initialization script.
 * This is an example of an typical initialization script that is used in most WebCore applications.
 * @version 1.0.0
 * @author Mario Di Vece <mario@unosquare.com>
 */
error_reporting(E_ALL | E_STRICT);
define("APPLICATION_ROOT", "/");
define("DOCUMENT_ROOT", str_replace('//', '/', str_replace('\\', '/', realpath(dirname(__FILE__) . '/../..') . '/')));

require_once "../webcore.php";
Resources::load('app.resources');
Settings::load('app.settings');
HttpContext::initialize();
define('SAMPLES_PATH', str_replace('//', '/', str_replace('\\', '/', dirname(__FILE__)) . '/'));

if (!is_dir(SAMPLES_PATH))
{
    throw new SystemException(SystemException::EX_NOCONTEXT, 'The sample path constant is not pointing to a valid virtual folder: ' . SAMPLES_PATH);
    exit();
}
try
{
    DataContext::getInstance(); // just to check if the db exists  
}
catch (Exception $ex)
{
    $exString = "<pre>No Samples Database!\nYou need to configure a default database in the app.settings file, and execute the porivded .sql file in this directory\n"
        . "Line " . $ex->getLine() . " on file '" . $ex->getFile() . "'\n"
        . "Error " . $ex->getCode() . ": " . $ex->getMessage() . "\nStack Trace:\n"
        . $ex->getTraceAsString() . "</pre>";
    HttpResponse::write($exString);
    HttpResponse::end();
}

?>
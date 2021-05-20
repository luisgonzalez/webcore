<?php
require_once 'initialize.inc.php';

// Log de pruebas
// Ya hace selects solitos con driver PHP. Del tipo:
//  $data = $context->getAdapter('countries')->select();
// Ya puede usar WHERE de forma mutante
// $data = $context->getAdapter('states')->where('country_id = 1')->select();
// El Limit tambien parece funcionar bien
// Pruebas con Azure exitosas!
try
{
    $context = DataContext::getInstance();
    $data = $context->getAdapter('Answers')->where("QuestionId = 1")->select();
    var_dump($data);
} catch (SystemException $ex) { var_dump($ex); }
?>
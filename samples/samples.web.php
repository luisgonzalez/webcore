<?php

require_once '../webcore.php';
HttpContext::initialize();
if (HttpContext::getRequest()->getRequestVars()->keyExists('clear'))
{
    HttpContext::getSession()->clear();
    echo "Session cleared";
    exit();
}

$ixCol      = new IndexedCollection();
$cotizacion = new StdClass();

HttpContext::getSession()->registerPersistentObject('ixCol', $ixCol);
HttpContext::getSession()->registerPersistentObject('std', $cotizacion);

$cotizacion->x = 'hello ' . date('H:i:s');

$ixCol->addValue(HttpContext::getInfo()->getRequestStartTime());


echo '<pre>';
var_dump(HttpContext::getSession());
echo '</pre>';
?>
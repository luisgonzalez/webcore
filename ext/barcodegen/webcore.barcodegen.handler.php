<?php
require_once "../../webcore.php";
require_once "webcore.barcodegen.php";

HttpHandlerManager::registerHandler(new BarCodeGeneratorHttpHandler());
HttpHandlerManager::handleRequest();
?>
<?php
/**
 * @category Scaffolding: The FormWidget
 * @tutorial Automatic forms widgets based on the database.
 * @author Mario Di Vece <mario@unosquare.com>
 */

$dataContext    = DataContext::getInstance();
/**
 * @var MySqlMetadataHelper
 */
$metadataHelper = $dataContext->getMetadataHelper();
$tables         = $metadataHelper->getTables();
$tableName      = $tables->getValue(0)->getValue('tableName');

$formScaffolder = new FormWidgetScaffolder();
$formScaffolder->setSource($dataContext->getAdapter($tableName)->defaultEntity());

$code = $formScaffolder->generateCode();
eval($code);
$className = ucfirst($tableName) . 'FormWidget';
$sample    = new $className();
//$sample->getModel()->getChildren()->addControl(new TextBlock('code', '<pre>' . $code . '</pre>', false));
$sample->handleRequest();
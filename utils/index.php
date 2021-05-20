<?php
require_once "../webcore.php";
require_once "../ext/portlet/webcore.model.portlet.php";
require_once "../ext/portlet/webcore.view.portlet.php";
require_once "forms.portlet.php";
require_once "grids.portlet.php";
require_once "datacontext.portlet.php";
require_once "settings.portlet.php";
require_once "users.portlet.php";

HttpContext::initialize();

function onDatacontexts(&$sender, &$eventArgs)
{
    $sender->addPortlet(new DataContextPortlet('d'));
}

function onForms(&$sender, &$eventArgs)
{
    $sender->addPortlet(new FormsPortlet('f'));
}

function onGrids(&$sender, &$eventArgs)
{
    $sender->addPortlet(new GridsPortlet('g'));
}

function onUsers(&$sender, &$eventArgs)
{
    $sender->addPortlet(new UsersPortlet('u'));
}

function onSettings(&$sender, &$eventArgs)
{
    $sender->addPortlet(new SettingsPortlet('s'));
}

$toolbar = new Toolbar('tools', 'Toolbar');
$toolbar->getChildren()->addControl(new ToolbarLabel('info', 'Select a form:'));
$toolbar->getChildren()->addControl(new ToolbarButton('datacontexts', 'DataContext Generator', 'datacontexts'));
$toolbar->getChildren()->addControl(new ToolbarSplit('split1'));
$buttonMenu = new ToolbarButtonMenu('generators', 'Model Generator');
$buttonMenu->addItem(new ToolbarButtonMenuItem('forms', 'Forms Generator', 'forms'));
$buttonMenu->addItem(new ToolbarButtonMenuItem('grids', 'Grids Generator', 'grids'));
$toolbar->getChildren()->addControl($buttonMenu);
$toolbar->getChildren()->addControl(new ToolbarSplit('split3'));
$toolbar->getChildren()->addControl(new ToolbarButton('users', 'Users & Roles', 'users'));
$toolbar->getChildren()->addControl(new ToolbarSplit('split4'));
$toolbar->getChildren()->addControl(new ToolbarButton('settings', 'Settings', 'settings'));

$workspace = new Workspace("workspace", "Webcore 3 Utils");
$workspace->getChildren()->addControl($toolbar);
$workspace->addPortlet(new DataContextPortlet('d'));

$workspaceView = new HtmlWorkspaceView($workspace);

Controller::registerEventHandler('forms', 'onForms');
Controller::registerEventHandler('grids', 'onGrids');
Controller::registerEventHandler('users', 'onUsers');
Controller::registerEventHandler('settings', 'onSettings');
Controller::registerEventHandler('datacontexts', 'onDatacontexts');

if (Controller::isPostBack($workspace))
{
    Controller::handleEvents($workspace);
    
    $workspaceView->render();
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>WebCore 3 Utils</title>
        <?php HtmlViewManager::render(); ?>
        <style type="text/css">
        body { padding: 10px; }
        </style>
    </head>
    <body>
        <div>
            <?php $workspaceView->render(); ?>
        </div>
    </body>
</html>
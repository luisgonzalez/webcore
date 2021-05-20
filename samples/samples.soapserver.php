<?php
require_once 'initialize.inc.php';
require_once "ext/soap/webcore.soap.server.php";

class OrdersService extends EntitySoapBase
{
    public function __construct()
    {
        $entity = DataContext::getInstance()->getAdapter('orders')->defaultEntity();
        
        parent::__construct($entity);
    }
}

$server = new OrdersService();
$server->createWSDL();
?>
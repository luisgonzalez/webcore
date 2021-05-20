<?php
/**
 * @category Grids: The Repeater
 * @tutorial Use repeaters to output data in a uniform format.
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 */

class RepeaterSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $repeater = new DataRepeater('productsRepeater', 'Products');
        $repeater->addRepeaterField(new TextRepeaterField('code', 'Code', 'code'));
        $repeater->addRepeaterField(new TextRepeaterField('name', 'Name', 'name'));
        $repeater->addRepeaterField(new TextRepeaterField('price', 'Unit Price', 'price'));
        $repeater->addRepeaterField(new TextRepeaterField('category', 'Category', 'category'));
        $linkItem = new TextRepeaterField('id', 'View', 'id');
        $linkItem->setUrl('javascript: alert(\'Client Event: id = %s\');');
        $repeater->addRepeaterField($linkItem);
        $repeater->addRepeaterField(new CommandRepeaterField('id2', 'Open', 'open', 'id'));
        $repeater->setIsPaged(true);
        $repeater->setPageSize(5);
        
        $this->model = $repeater;
        $this->view  = new HtmlRepeaterView($repeater, 'nombre');
        
        Controller::registerEventHandler('open', array(
            __CLASS__,
            'onOpen'
        ));
        
    }
    
    public static function onOpen(&$model, &$event)
    {
        $message = 'Server Event: ' . $event->getName() . ' = ' . $event->getValue();
        $model->setMessage($message);
    }
    
    public function handleRequest()
    {
        $listUsuarios = DataContext::getInstance()->getAdapter('Products')->joinRelated('Categories')->addFunctionField("CONCAT(products.title, ' - ', products.name)", "name")->addField('products', 'id')->addField('products', 'price')->addField('products', 'code')->addField('categories', 'name', 'category')->orderBy('products.id');
        
        if (Controller::hasEvents($this->model))
            Controller::handleEvents($this->model);
        $this->model->dataBind($listUsuarios);
        if ($this->view->getIsAsynchronous() === true && Controller::isPostBack($this->model))
        {
            $this->view->render();
            HttpResponse::end();
        }
    }
    
    public static function createInstance()
    {
        return new RepeaterSampleWidget();
    }
}
$sample = new RepeaterSampleWidget();
$sample->handleRequest();
?>
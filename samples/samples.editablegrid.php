<?php
/**
 * @category Grids: The EditableDataRepeater
 * @tutorial Built-in control to do direct editing on the grid.
 * @author Mario Di Vece <mario@unosquare.com>
 */

/**
 * @package WebCore
 * @subpackage Samples
 */
class EditableGridSampleWidget extends WidgetBase
{
    /**
     * Creates a new instance of this class
     */
    public function __construct()
    {
        parent::__construct(__CLASS__);
        
        $repeater = new EditableDataRepeater('editableRepeater', 'The EditableDataRepeater', 'id');
        $combo    = new ComboBoxRepeaterField('role_id', 'Role', 'role_id');
        $roles    = DataContext::getInstance()->getAdapter('roles')->select();
        $combo->addOptions($roles, 'id', 'name');
        $repeater->addRepeaterField(new EmailRepeaterField('email', 'Email', 'email'));
        $repeater->addRepeaterField(new TextBoxRepeaterField('password', 'Password', 'password'));
        $repeater->addRepeaterField(new TextBoxRepeaterField('name_first', 'First Name', 'name_first'));
        $repeater->addRepeaterField(new TextBoxRepeaterField('name_last', 'Lastname', 'name_last'));
        $repeater->addRepeaterField($combo);
        $repeater->addRepeaterField(new CheckBoxRepeaterField('sys_enabled', 'Enabled', 'sys_enabled'));
        
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_ADD_ITEM, array(
            __CLASS__,
            'onAddItem'
        ));
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_DELETE_ITEM, array(
            __CLASS__,
            'onDeleteItem'
        ));
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_EDIT_ITEM, array(
            __CLASS__,
            'onEditItem'
        ));
        Controller::registerEventHandler(EditableDataRepeater::EVENTNAME_SAVE_ITEM, array(
            __CLASS__,
            'onSaveItem'
        ));
        
        $repeaterView = new HtmlRepeaterView($repeater);
        $repeaterView->setFrameWidth('inherit');
        
        $this->model = $repeater;
        $this->view  = $repeaterView;
        
    }
    
    /**
     * Creates the default instance of this class
     */
    public static function createInstance()
    {
        return new EditableGridSampleWidget();
    }
    
    /**
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onEditItem(&$model, &$event)
    {
        LogManager::debug('onEditItem:' . $model->getMode());
        $model->setEditKey($event->getValue());
        
    }
    
    /**
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onDeleteItem(&$model, &$event)
    {
        LogManager::debug('onDeleteItem:' . $model->getMode());
        $context    = DataContext::getInstance();
        $user       = $context->getAdapter('users')->single($event->getValue());
        $rolesToDel = $context->getAdapter('users_roles')->where('user_id = ' . $user->id)->select();
        
        try
        {
            $context->getConnection()->transactionBegin();
            $context->getAdapter('users_roles')->deleteAll($rolesToDel);
            $context->getAdapter('users')->delete($user);
            $context->getConnection()->transactionCommit();
            $model->setMessage("The user was deleted.");
        }
        catch (SystemException $ex)
        {
            $context->getConnection()->transactionRollback();
            $model->setErrorMessage("Could not delete user:\r\n" . $ex->getMessage());
        }
    }
    
    /**
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onSaveItem(&$model, &$event)
    {
        LogManager::debug('onSaveItem:' . $model->getMode());
        $context = DataContext::getInstance();
        $request = HttpRequest::getInstance()->getRequestVars();
        $editId  = $event->getValue();
        $model->dataBindForm($request);
        
        if ($model->validate())
        {
            $p = $context->getAdapter('users')->single($editId);
            $p->dataBind($request);
            $role          = $context->getAdapter('users_roles')->where('user_id = ' . $p->id)->selectOne();
            $role->role_id = $request->getValue('role_id');
            
            $context->getConnection()->transactionBegin();
            try
            {
                $context->getAdapter('users')->update($p);
                $context->getAdapter('users_roles')->update($role);
                $context->getConnection()->transactionCommit();
                $model->endEdit();
            }
            catch (SystemException $ex)
            {
                $context->getConnection()->transactionRollback();
                $model->setErrorMessage("Could not update user:\r\n" . $ex->getMessage());
            }
            
        }
        else
        {
            $model->setEditKey($editId);
        }
        
    }
    
    /**
     * @param EditableDataRepeater $model
     * @param ControllerEvent $event
     */
    public static function onAddItem(&$model, &$event)
    {
        LogManager::debug('onAddItem:' . $model->getMode());
        $context = DataContext::getInstance();
        $request = HttpRequest::getInstance()->getRequestVars();
        
        $model->dataBindForm($request);
        
        if ($model->validate())
        {
            $dt = date('Y-m-d H:i:s');
            
            $context->getConnection()->transactionBegin();
            
            $p = $context->getAdapter('users')->defaultEntity();
            $p->dataBind($request);
            $p->birthdate        = '1986-06-17';
            $p->sys_created_date = $dt;
            $p->sys_created_id   = 0;
            $p->sys_updated_date = $dt;
            $p->sys_updated_id   = 0;
            
            $r = $context->getAdapter('users_roles')->defaultEntity();
            $r->dataBind($request);
            
            try
            {
                $context->getAdapter('users')->insert($p);
                $r->user_id = $p->id;
                $context->getAdapter('users_roles')->insert($r);
                $context->getConnection()->transactionCommit();
                $model->setMessage("The user was created.");
            }
            catch (SystemException $ex)
            {
                $context->getConnection()->transactionRollback();
                $model->setErrorMessage($ex->getMessage());
            }
            
        }
    }
    
    
    public function handleRequest()
    {
        $data = DataContext::getInstance()->getAdapter('Users')->joinRelated('Users_roles')->innerJoin('roles', 'users_roles.role_id = roles.id')->addField('users', 'id')->addField('roles', 'id', 'role_id')->addField('users', 'email')->addField('users', 'password')->addField('users', 'name_first')->addField('users', 'name_last')->addField('users', 'sys_enabled');
        
        $handledEvents = 0;
        if (Controller::isPostBack($this->model))
        {
            $handledEvents = Controller::handleEvents($this->model);
            $this->model->dataBind($data);
            
            if ($this->getView()->getIsAsynchronous() == true)
            {
                $this->render();
                HttpResponse::end();
            }
        }
        else
        {
            $this->model->dataBind($data);
        }
        
    }
    
}

$sample = new EditableGridSampleWidget();
$sample->handleRequest();
?>
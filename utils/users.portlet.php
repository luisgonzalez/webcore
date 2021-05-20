<?php
class UsersPortlet extends PortletBase
{
    public function __construct($name)
    {
        parent::__construct($name);
        
        $this->model = new Form('users', 'Users and Roles');
        
        $this->view = new HtmlFormView($this->model);
        $this->view->setIsAsynchronous(true);
        
        $this->handleRequest();
    }
    
    public static function createInstance()
    {
        return new UsersPortlet('Instance');
    }
}
?>
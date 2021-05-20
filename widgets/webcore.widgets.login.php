<?php
/**
 * Login widget.
 * Uses common Controller and Form.
 *
 * @package WebCore
 * @subpackage Widgets
 */
class LoginWidget extends WidgetBase
{
    /**
     * Creates a new login instance using view class name
     *
     * @param string $viewClass
     * @param bool $async
     */
    public function __construct($viewClass = "HtmlFormView", $async = true)
    {
        parent::__construct("formLogin");
        
        $request = HttpContext::getRequest()->getRequestVars();
        
        if ($request->keyExists('logout'))
            FormsAuthentication::signOut();
        
        if (FormsAuthentication::getUser() !== null)
            FormsAuthentication::redirectFromLoginPage();
        
        $this->model = new Form("formLogin", Resources::getValue(Resources::SRK_LOGIN_FORM_CAPTION));
        $this->model->getChildren()->addControl(new Persistor('returnUrl', ''));
        $this->model->addField(new TextField("username", Resources::getValue(Resources::SRK_LOGIN_USER_CAPTION), "", true, Resources::getValue(Resources::SRK_LOGIN_USER_TOOLTIP)));
        $this->model->addField(new PasswordField("password", Resources::getValue(Resources::SRK_LOGIN_PASSWORD_CAPTION), "", true, Resources::getValue(Resources::SRK_LOGIN_PASSWORD_TOOLTIP)));
        $this->model->addButton(new Button("signIn", Resources::getValue(Resources::SRK_LOGIN_BUTTON_CAPTION), "signInButton_Click"));
        
        Controller::registerEventHandler("signInButton_Click", "LoginWidget::signInButton_Click");
        
        $this->model->dataBind($request);
        
        $this->view = new $viewClass($this->model);
        $this->view->setIsAsynchronous($async);
    }
    
    /**
     * This is callback is used as a postback
     *
     * @param Form $sender
     * @param ControllerEvent $eventArgs
     */
    public static function signInButton_Click(&$sender, &$eventArgs)
    {
        $request = HttpContext::getRequest()->getRequestVars();
        $sender->dataBind($request);
        
        if ($sender->validate())
        {
            $username  = $sender->getField("username")->getValue();
            $password  = $sender->getField("password")->getValue();
            $returnUrl = $sender->getChildren()->getControl('returnUrl', false)->getValue();
            
            try
            {
                if (Membership::getInstance()->validateUser($username, $password))
                {
                    FormsAuthentication::setUser($username);
                    if ($returnUrl != '')
                        $returnUrl = base64_decode($returnUrl);
                    FormsAuthentication::redirectFromLoginPage($returnUrl);
                }
                else
                {
                    $sender->setErrorMessage(Resources::getValue(Resources::SRK_MEMBERSHIP_ERR_LOGIN));
                }
            }
            catch (Exception $ex)
            {
                $sender->setErrorMessage($ex->getMessage());
            }
        }
    }
    
    /**
     * Creates a default instance of this class
     *
     * @return LoginWidget
     */
    public static function createInstance()
    {
        return new LoginWidget();
    }
}
?>
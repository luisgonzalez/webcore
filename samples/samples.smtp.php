<?php
/**
 * @category Web: Sending Email
 * @tutorial Use the WebCore.Mail classes to send emails
 * @author Mario Di Vece <mario@unosquare.com>
 */

class SmtpSendSampleWidget extends WidgetBase
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $form = new Form('sendForm', 'Send an email');
        
        $smtpSection = new FormSection('smtpSettings', 'SMTP Client Settings');
        $smtpSection->addField(new TextField('server', 'Server', 'mail.domain.com'));
        $smtpSection->addField(new IntegerField('port', 'Port', '25'));
        $smtpSection->addField(new TextField('username', 'Username', '', false));
        $smtpSection->addField(new PasswordField('password', 'Password', '', false));
        $form->addContainer($smtpSection);
        
        $mailSection = new FormSection('mailMessage', 'Mail Message');
        $mailSection->addField(new EmailField('from', 'From'));
        $mailSection->addField(new EmailField('to', 'To'));
        $mailSection->addField(new TextField('subject', 'Subject'));
        $mailSection->addField(new FileField('attachment', 'Attachment', '', 'uploadFile_Click', '1'));
        $mailSection->addField(new RichTextArea('message', 'Message'));
        $form->addContainer($mailSection);
        
        $form->addButton(new Button('sendButton', 'Send', 'sendButton_Click'));
        
        Controller::registerEventHandler('sendButton_Click', array(
            __CLASS__,
            'onSendButton_Click'
        ));
        Controller::registerEventHandler('uploadFile_Click', array(
            __CLASS__,
            'onUploadFile_Click'
        ));
        
        $this->model = $form;
        
        $this->view = new HtmlFormView($this->model);
        $this->view->setIsAsynchronous(true);
    }
    
    public static function createInstance()
    {
        return new SmtpSendSampleWidget();
    }
    
    /**
     * @param Form $sender
     * @param ControllerEvent $event
     */
    public static function onUploadFile_Click(&$sender, &$event)
    {
        /**
         * @var PostedFile
         */
        $file = HttpRequest::getInstance()->getPostedFiles()->getValue('attachment');
        
        if ($file->getErrorCode() !== UPLOAD_ERR_OK)
        {
            $sender->setErrorMessage($file->getErrorMessage());
            return;
        }
        
        $sender->dataBind(HttpRequest::getInstance()->getRequestVars());
        $sender->dataBind(HttpRequest::getInstance()->getPostedFiles());
    }
    
    /**
     * @param Form $sender
     * @param ControllerEvent $event
     */
    public static function onSendButton_Click(&$sender, &$event)
    {
        $request = HttpContext::getRequest()->getRequestVars();
        $sender->dataBind($request);
        
        if ($sender->validate())
        {
            $client = new SmtpClient();
            $client->setUsername($request->getValue('username'));
            $client->setPassword($request->getValue('password'));
            $client->setPort(intval($request->getValue('port')));
            $client->setServer($request->getValue('server'));
            
            $message = new MailMessage($request->getValue('from'), $request->getValue('username'), $request->getValue('subject'), '<html><body style="font-family: arial, sans-serif; font-size: 12px;">' . $request->getValue('message') . '</body></html>');
            
            $message->addRecipient($request->getValue('to'));
            
            if (HttpContext::getRequest()->getPostedFiles()->keyExists('attachment'))
            {
                /**
                 * @var PostedFile
                 */
                $postedFile = HttpContext::getRequest()->getPostedFiles()->getItem('attachment');
                $message->addAttachment($postedFile->getTempFileName(), $postedFile->getMimeType());
            }
            
            try
            {
                $responses = $client->send($message);
                $sender->setMessage("The message was sent:\r\n" . print_r($responses, true));
            }
            catch (SystemException $ex)
            {
                $sender->setErrorMessage("Unable to send message:\r\n" . $ex->getMessage());
            }
        }
    }
    
}

$sample  = new SmtpSendSampleWidget();
$handled = $sample->handleRequest();
?>
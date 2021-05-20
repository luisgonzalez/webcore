<?php
/**
 * Interface for Contacts Grabber
 *
 * @package WebCore
 * @subpackage ContactsGrabber
 */
interface IContactsGrabber extends IObject
{
    public function getContacts();
}

/**
 * Helper for grab contacts
 *
 * @package WebCore
 * @subpackage ContactsGrabber
 */
class ContactsGrabber extends HelperBase
{
    const SERVICE_HOTMAIL = 'hotmail';
    const SERVICE_GMAIL = 'gmail';
    
    /**
     * Gets contacts
     *
     * @param string $username
     * @param string $password
     * @param string $service
     * 
     * @return KeyedCollection
     */
    public static function getContacts($username, $password, $service)
    {
        /**
         * @var IContactsGrabber
         */
        $instance = null;
        
        switch ($service)
        {
            case ContactsGrabber::SERVICE_HOTMAIL:
                $instance = new HotmailContactsGrabber($username, $password);
                break;
            case ContactsGrabber::SERVICE_GMAIL:
                $gData = new GDataContactsClient($username, $password);
                
                return $gData->getContacts();
                break;
            default:
                throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = service');
        }
        
        $contacts = $instance->getContacts();
        
        return $contacts;
    }
}
?>
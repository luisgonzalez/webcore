<?php
require_once "contacts-grabber.php";

/**
 * Hotmail's contacts grabber
 *
 * @package WebCore
 * @subpackage ContactsGrabber
 * @author Jonathan Street <jonathan@torrentialwebdev.com>
 */
class HotmailContactsGrabber extends ObjectBase implements IContactsGrabber
{
    private $server = 'messenger.hotmail.com';
    private $port = 1863;
    private $user;
    private $password;
    private $count = 0;
    private $trID;
    private $authed = false;
    private $fp = null;
    
    private $email_input = array();
    private $email_processing = array();
    private $email_output = array();
    
    /**
     * Creates a new instance of this class
     *
     * @param string $user
     * @param string $password
     */
    public function __construct($user, $password)
    {
        $this->user     = $user;
        $this->password = urlencode($password);
        $this->trID     = 1;
    }
    
    /**
     * Connects to Hotmail server
     *
     */
    public function connect()
    {
        if (!$this->fp = @fsockopen($this->server, $this->port))
            throw new SystemException(SystemException::EX_INVALIDOPERATION, 'Could not connect to messenger service');
        
        $this->putData("VER " . $this->trID . " MSNP9 CVR0\r\n");
        
        while (!feof($this->fp))
        {
            $data = $this->getData();
            
            switch ($code = substr($data, 0, 3))
            {
                case 'VER':
                    $this->putData("CVR " . $this->trID . " 0x0409 win 4.10 i386 MSNMSGR 7.0.0816 MSMSGS " . $this->user . "\r\n");
                    break;
                case 'CVR':
                    $this->putData("USR " . $this->trID . " TWN I " . $this->user . "\r\n");
                    break;
                case 'XFR':
                    list(, , , $ip) = explode(' ', $data);
                    list($ip, $port) = explode(':', $ip);
                    
                    if ($this->fp = @fsockopen($ip, $port, $errno, $errstr, 2))
                    {
                        $this->trID = 1;
                        $this->putData("VER " . $this->trID . " MSNP9 CVR0\r\n");
                    }
                    else throw new SystemException(SystemException::EX_INVALIDOPERATION, 'Unable to connect to msn server (transfer)');
                    break;
                case 'USR':
                    if ($this->authed)
                    {
                        return true;
                    }
                    
                    list(, , , , $code) = explode(' ', trim($data));
                    $auth = $this->auth($code);
                    
                    if ($auth != false)
                    {
                        $this->putData("USR " . $this->trID . " TWN S $auth\r\n");
                        $this->authed = true;
                    }
                    else
                    {
                        throw new SystemException(SystemException::EX_INVALIDOPERATION, 'Auth failed');
                    }
                    break;
                default:
                    throw new SystemException(SystemException::EX_INVALIDOPERATION, $this->_get_error($code));
                    break;
            }
        }
    }
    
    /**
     * Retrieves a keyedcollection with contacts
     *
     * @return KeyedCollection
     */
    public function getContacts()
    {
        if ($this->connect())
        {
            $this->rx_data();
            
            $data = new KeyedCollection();
            
            foreach ($this->email_input as $item)
            {
                $dataItem = explode(" ", $item);
                $email    = $dataItem[1];
                $name     = urldecode(utf8_decode($dataItem[2]));
                
                $data->setValue($email, $name);
            }
            
            return $data;
        }
    }
    
    /**
     * Collects the raw data containing the email addresses
     *
     */
    private function rx_data()
    {
        $this->putData("SYN " . $this->trID . " 0\r\n");
        
        //Supplies the second MSG code which stops
        //the script from hanging as it waits for
        //more content
        $this->putData("CHG " . $this->trID . " NLN\r\n");
        
        $stream_info = stream_get_meta_data($this->fp);
        $email_total = 100;
        
        //the count check prevents the script hanging as it waits for more content
        while ((!feof($this->fp)) && (!$stream_info['timed_out']) && ($this->count <= 100) && (count($this->email_input) < $email_total))
        {
            $data        = $this->getData();
            $stream_info = stream_get_meta_data($this->fp);
            
            if ($data)
            {
                switch ($code = substr($data, 0, 3))
                {
                    case 'MSG':
                        //This prevents the script hanging as it waits for more content
                        $this->count++;
                        break;
                    case 'LST':
                        $this->email_input[] = $data;
                        break;
                    case 'SYN':
                        $syn_explode = explode(" ", $data);
                        $email_total = $syn_explode[3];
                        break;
                    case 'CHL':
                        $bits   = explode(' ', trim($data));
                        $return = md5($bits[2] . 'Q1P7W2E4J9R8U3S5');
                        $this->putData("QRY " . $this->trID . "msmsgs@msnmsgr.com 32\r\n$return");
                        break;
                        
                }
            }
        }
        
    }
    
    /**
     * Authentificates with token
     *
     */
    private function auth($auth_string)
    {
        $ch = curl_init('https://login.live.com/login2.srf');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Passport1.4 OrgVerb=GET,OrgURL=http%3A%2F%2Fmessenger%2Emsn%2Ecom,sign-in=' . $this->user . ',pwd=' . $this->password . ',' . $auth_string,
            'Host: login.passport.com'
        ));
        
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $header = curl_exec($ch);
        curl_close($ch);
        
        preg_match("/from-PP='(.*?)'/", $header, $out);
        
        return (isset($out[1])) ? $out[1] : false;
    }
    
    /**
     * Returns data from socket
     *
     * @return mixed
     */
    private function getData()
    {
        if ($data = @fgets($this->fp, 4096))
        {
            return $data;
        }
        
        return false;
    }
    
    /**
     * Puts data in socket
     *
     * @param mixed $data
     */
    private function putData($data)
    {
        fwrite($this->fp, $data);
        
        $this->trID++;
    }
    
    /**
     * Translates error code
     *
     * @param int $code
     * @return string
     */
    private function _get_error($code)
    {
        switch ($code)
        {
            case 201:
                return 'Error: 201 Invalid parameter';
                break;
            case 217:
                return 'Error: 217 Principal not on-line';
                break;
            case 500:
                return 'Error: 500 Internal server error';
                break;
            case 540:
                return 'Error: 540 Challenge response failed';
                break;
            case 601:
                return 'Error: 601 Server is unavailable';
                break;
            case 710:
                return 'Error: 710 Bad CVR parameters sent';
                break;
            case 713:
                return 'Error: 713 Calling too rapidly';
                break;
            case 731:
                return 'Error: 731 Not expected';
                break;
            case 800:
                return 'Error: 800 Changing too rapidly';
                break;
            case 910:
            case 921:
                return 'Error: 910/921 Server too busy';
                break;
            case 911:
                return 'Error: 911 Authentication failed';
                break;
            case 923:
                return 'Error: 923 Kids Passport without parental consent';
                break;
            case 928:
                return 'Error: 928 Bad ticket';
                break;
            default:
                return 'Error code ' . $code . ' not found';
                break;
        }
    }
}
?>
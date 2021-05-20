<?php
/**
 * ExcelDocumentSecurity
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelDocumentSecurity extends ObjectBase
{
    /**
     * LockRevision
     *
     * @var boolean
     */
    private $_lockRevision;
    
    /**
     * LockStructure
     *
     * @var boolean
     */
    private $_lockStructure;
    
    /**
     * LockWindows
     *
     * @var boolean
     */
    private $_lockWindows;
    
    /**
     * RevisionsPassword
     *
     * @var string
     */
    private $_revisionsPassword;
    
    /**
     * WorkbookPassword
     *
     * @var string
     */
    private $_workbookPassword;
    
    /**
     * Create a new ExcelDocumentSecurity
     */
    public function __construct()
    {
        // Initialise values
        $this->_lockRevision      = false;
        $this->_lockStructure     = false;
        $this->_lockWindows       = false;
        $this->_revisionsPassword = '';
        $this->_workbookPassword  = '';
    }
    
    /**
     * Is some sort of dcument security enabled?
     *
     * @return boolean
     */
    function isSecurityEnabled()
    {
        return $this->_lockRevision || $this->_lockStructure || $this->_lockWindows;
    }
    
    /**
     * Get LockRevision
     *
     * @return boolean
     */
    function getLockRevision()
    {
        return $this->_lockRevision;
    }
    
    /**
     * Set LockRevision
     *
     * @param boolean $pValue
     */
    function setLockRevision($pValue = false)
    {
        $this->_lockRevision = $pValue;
    }
    
    /**
     * Get LockStructure
     *
     * @return boolean
     */
    function getLockStructure()
    {
        return $this->_lockStructure;
    }
    
    /**
     * Set LockStructure
     *
     * @param boolean $pValue
     */
    function setLockStructure($pValue = false)
    {
        $this->_lockStructure = $pValue;
    }
    
    /**
     * Get LockWindows
     *
     * @return boolean
     */
    function getLockWindows()
    {
        return $this->_lockWindows;
    }
    
    /**
     * Set LockWindows
     *
     * @param boolean $pValue
     */
    function setLockWindows($pValue = false)
    {
        $this->_lockWindows = $pValue;
    }
    
    /**
     * Get RevisionsPassword (hashed)
     *
     * @return string
     */
    function getRevisionsPassword()
    {
        return $this->_revisionsPassword;
    }
    
    /**
     * Set RevisionsPassword
     *
     * @param string 	$pValue
     * @param boolean 	$pAlreadyHashed If the password has already been hashed, set this to true
     */
    function setRevisionsPassword($pValue = '', $pAlreadyHashed = false)
    {
        if (!$pAlreadyHashed)
        {
            $pValue = ExcelShared_PasswordHasher::hashPassword($pValue);
        }
        $this->_revisionsPassword = $pValue;
    }
    
    /**
     * Get WorkbookPassword (hashed)
     *
     * @return string
     */
    function getWorkbookPassword()
    {
        return $this->_workbookPassword;
    }
    
    /**
     * Set WorkbookPassword
     *
     * @param string 	$pValue
     * @param boolean 	$pAlreadyHashed If the password has already been hashed, set this to true
     */
    function setWorkbookPassword($pValue = '', $pAlreadyHashed = false)
    {
        if (!$pAlreadyHashed)
        {
            $pValue = ExcelShared_PasswordHasher::hashPassword($pValue);
        }
        $this->_workbookPassword = $pValue;
    }
}

/**
 * ExcelShared_PasswordHasher
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_PasswordHasher extends HelperBase
{
    /**
     * Create a password hash from a given string.
     *
     * This method is based on the algorithm provided by
     * Daniel Rentz of OpenOffice and the PEAR package
     * Spreadsheet_Excel_Writer by Xavier Noguer <xnoguer@rezebra.com>.
     *
     * @param 	string	$pPassword	Password to hash
     * @return 	string				Hashed password
     */
    public static function hashPassword($pPassword = '')
    {
        $password = 0x0000;
        $i        = 1; // char position
        
        // split the plain text password in its component characters
        $chars = preg_split('//', $pPassword, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $char)
        {
            $value        = ord($char) << $i; // shifted ASCII value
            $rotated_bits = $value >> 15; // rotated bits beyond bit 15
            $value &= 0x7fff; // first 15 bits
            $password ^= ($value | $rotated_bits);
            ++$i;
        }
        
        $password ^= strlen($pPassword);
        $password ^= 0xCE4B;
        
        return (strtoupper(dechex($password)));
    }
}

/**
 * ExcelDocumentProperties
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelDocumentProperties extends ObjectBase
{
    /**
     * Creator
     *
     * @var string
     */
    private $_creator;
    
    /**
     * LastModifiedBy
     *
     * @var string
     */
    private $_lastModifiedBy;
    
    /**
     * Created
     *
     * @var datetime
     */
    private $_created;
    
    /**
     * Modified
     *
     * @var datetime
     */
    private $_modified;
    
    /**
     * Title
     *
     * @var string
     */
    private $_title;
    
    /**
     * Description
     *
     * @var string
     */
    private $_description;
    
    /**
     * Subject
     *
     * @var string
     */
    private $_subject;
    
    /**
     * Keywords
     *
     * @var string
     */
    private $_keywords;
    
    /**
     * Category
     *
     * @var string
     */
    private $_category;
    
    /**
     * Create a new ExcelDocumentProperties
     */
    public function __construct()
    {
        // Initialise values
        $this->_creator        = 'Unknown Creator';
        $this->_lastModifiedBy = $this->_creator;
        $this->_created        = time();
        $this->_modified       = time();
        $this->_title          = "Untitled Spreadsheet";
        $this->_subject        = '';
        $this->_description    = '';
        $this->_keywords       = '';
        $this->_category       = '';
    }
    
    /**
     * Get Creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->_creator;
    }
    
    /**
     * Set Creator
     *
     * @param string $pValue
     */
    public function setCreator($pValue = '')
    {
        $this->_creator = $pValue;
    }
    
    /**
     * Get Last Modified By
     *
     * @return string
     */
    public function getLastModifiedBy()
    {
        return $this->_lastModifiedBy;
    }
    
    /**
     * Set Last Modified By
     *
     * @param string $pValue
     */
    public function setLastModifiedBy($pValue = '')
    {
        $this->_lastModifiedBy = $pValue;
    }
    
    /**
     * Get Created
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->_created;
    }
    
    /**
     * Set Created
     *
     * @param datetime $pValue
     */
    public function setCreated($pValue = null)
    {
        if (is_null($pValue))
        {
            $pValue = time();
        }
        $this->_created = $pValue;
    }
    
    /**
     * Get Modified
     *
     * @return datetime
     */
    public function getModified()
    {
        return $this->_modified;
    }
    
    /**
     * Set Modified
     *
     * @param datetime $pValue
     */
    public function setModified($pValue = null)
    {
        if (is_null($pValue))
        {
            $pValue = time();
        }
        $this->_modified = $pValue;
    }
    
    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    /**
     * Set Title
     *
     * @param string $pValue
     */
    public function setTitle($pValue = '')
    {
        $this->_title = $pValue;
    }
    
    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Set Description
     *
     * @param string $pValue
     */
    public function setDescription($pValue = '')
    {
        $this->_description = $pValue;
    }
    
    /**
     * Get Subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }
    
    /**
     * Set Subject
     *
     * @param string $pValue
     */
    public function setSubject($pValue = '')
    {
        $this->_subject = $pValue;
    }
    
    /**
     * Get Keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->_keywords;
    }
    
    /**
     * Set Keywords
     *
     * @param string $pValue
     */
    public function setKeywords($pValue = '')
    {
        $this->_keywords = $pValue;
    }
    
    /**
     * Get Category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->_category;
    }
    
    /**
     * Set Category
     *
     * @param string $pValue
     */
    public function setCategory($pValue = '')
    {
        $this->_category = $pValue;
    }
}
?>
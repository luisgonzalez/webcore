<?php
/**
 * @package WebCore
 * @subpackage Application
 * @version 1.0
 * 
 * Contains classes that facilitate the management of settings and resources for an application.
 *
 * @todo In PHP5.3 make a abstract static PersistentCollectionManager
 * 
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.php";
require_once "webcore.globalization.php";
require_once "webcore.security.php";
require_once "webcore.compression.php";
require_once "webcore.serialization.php";

/**
 * Defines the necessary methods for implementing a Manager for a persistent collection.
 * For example, Settings and Resources are persistent, statically accessible collections.
 * Avoid storing objects in these collections.
 *
 * @package WebCore
 * @subpackage Application
 */
interface IPersistentCollectionManager extends IObject
{
    /**
     * Load the persistent collection given the storeName.
     *
     * @param string $storeName The storename (or filename)
     */
    public static function load($storeName = '');
    /**
     * Saves the persistent collection given the storeName.
     *
     * @param string $storeName The storename (or filename)
     */
    public static function save($storeName = '');
    /**
     * Gets the store name from wich the collection was loaded.
     *
     * @return string
     */
    public static function getStoreName();
    /**
     * Sets a value for a given key.
     *
     * @param string $keyName
     * @param string $value
     */
    public static function setValue($keyName, $value);
    /**
     * Gets the value of a given key.
     *
     * @param string $keyName
     * @return string
     */
    public static function getValue($keyName);
    /**
     * Determines if the given key has a value.
     *
     * @param string $keyName
     * @return bool
     */
    public static function hasValue($keyName);
    /**
     * Gets all the keys in the collection as an array.
     *
     * @return array
     */
    public static function getKeys();
    /**
     * Gets the underlying collection by reference.
     *
     * @return CollectionBase
     */
    public static function &getCollection();
}

/**
 * Manager class to store and retrieve framework and applications settings.
 * 
 * @todo Add method to get settings section as keyed collection
 * @todo PLEASE REMOVE GLOBALIZATION STUFF FROM SETTINGS. THESE GO IN RESOURCES. Date and number formats have NOTHING to do with application logic.
 * @package WebCore
 * @subpackage Application
 */
class Settings extends ObjectBase implements IPersistentCollectionManager
{
    // Application Keys
    const SKEY_APPLICATION = "application";
    const KEY_APPLICATION_CULTURE = "applicationCulture";
    const KEY_APPLICATION_NAME = "applicationName";
    const KEY_APPLICATION_VERSION = "applicationVersion";
    const KEY_APPLICATION_COMPANY = "applicationCompany";
    const KEY_APPLICATION_TIMEZONE = "applicationTimezone";
    const KEY_APPLICATION_SESSIONHANDLER = "applicationSessionHandler";
    
    // Caching Keys
    const SKEY_CACHE = "caching";
    const KEY_CACHE_ENABLE = "enable";
    const KEY_CACHE_SERVERS = "servers";
    const KEY_CACHE_PORTS = "ports";
    
    // Data Keys
    const SKEY_DATA = "data";
    const KEY_DATA_PROVIDER = "connectionManager";
    const KEY_DATA_SERVER = "server";
    const KEY_DATA_USERNAME = "username";
    const KEY_DATA_PASSWORD = "password";
    const KEY_DATA_DATABASE = "database";
    const KEY_DATA_PORT = "port";
    const KEY_DATA_CONCURRENCY = "concurrencyMode";
    const KEY_DATA_DROPENABLE = "allowDropCommands";
    const KEY_DATA_DISABLEDEFERRED = "disableDeferredExecution";
    const KEY_DATA_LOGICALDELETEFIELD = "logicalDeleteField";
    
    // Logging Keys
    const SKEY_LOG = "logging";
    const KEY_LOG_PROVIDER = "logManagers";
    const KEY_LOG_FILE = "logFile";
    const KEY_LOG_LEVEL = "logLevel";
    const KEY_LOG_LOGENTITY = "logEntity";
    
    // Compression Keys
    const SKEY_COMPRESSION = "compression";
    const KEY_COMPRESSION_PROVIDER = "compressionProvider";
    const KEY_COMPRESSION_ENABLED = "enable";
    const KEY_COMPRESSION_STORE = "store";
    
    // Security Keys
    const SKEY_SECURITY = "security";
    const KEY_SECURITY_MEMBERSHIPPROVIDER = "membershipProvider";
    const KEY_SECURITY_AUTHPROVIDER = "authenticationProvider";
    const KEY_SECURITY_HASHALGORITHM = "hashAlgorithm";
    const KEY_SECURITY_USERENTITY = "userEntity";
    const KEY_SECURITY_USERENTITYKEY = "userEntityKey";
    const KEY_SECURITY_ROLEENTITY = "roleEntity";
    const KEY_SECURITY_ROLEENTITYKEY = "roleEntityKey";
    const KEY_SECURITY_USERROLEENTITY = "userRoleEntity";
    const KEY_SECURITY_USERROLEENTITYUSERKEY = "userRoleEntityUserKey";
    const KEY_SECURITY_LOGINPAGE = "loginPage";
    const KEY_SECURITY_WELCOMEPAGE = "welcomePage";
    const KEY_SECURITY_USERENTITYFIELD = "userEntityFields";
    const KEY_SECURITY_ROLEENTITYFIELD = "roleEntityFields";
    
    // Mail Keys
    const SKEY_MAIL = "mail";
    const KEY_MAIL_SERVER = "server";
    const KEY_MAIL_PORT = "port";
    const KEY_MAIL_USERNAME = "username";
    const KEY_MAIL_PASSWORD = "password";
    
    const DEFAULTFILENAME = 'app.settings';
    
    /**
     * Represents the internal collection
     *
     * @var KeyedCollectionWrapper
     */
    private static $settingsCollection = null;
    
    private static $storeName;
    
    /**
     * Loads the settings from from the given file.
     * If the file is not found, a defualt settings file is created
     *
     * @param string $storeName If not specified, the DEFAULTFILENAME constant is used instead
     */
    public static function load($storeName = '')
    {
        $data = '';
        if ($storeName === '')
        {
            self::$storeName = HttpContext::getDocumentRoot() . self::DEFAULTFILENAME;
        }
        else
        {
            self::$storeName = $storeName;
        }
        
        if (file_exists(self::$storeName) === false) self::createDefault(self::$storeName);
        $data = file_get_contents(self::$storeName);        
        self::$settingsCollection = XmlSerializer::deserialize($data, 'KeyedCollectionWrapper');
    }
    
    /**
     * Gets the store name from wich the collection was loaded.
     *
     * @return string
     */
    public static function getStoreName()
    {
        return self::$storeName;
    }
    
    /**
     * Saves the changes to the settings
     * If a store name is not specified, it will use the default.
     *
     * @param string $storeName The filename to save the setting file to.
     */
    public static function save($storeName = '')
    {
        if (self::$settingsCollection == null)
            self::load($storeName);
        
        if ($storeName === '')
            $fileName = HttpContext::getDocumentRoot() . self::DEFAULTFILENAME;
        else
            $fileName = $storeName;
        
        $data = XmlSerializer::serialize(self::$settingsCollection);
        file_put_contents($fileName, $data);
    }
    
    /**
     * Creates a default settings file
     *
     * @param string $fileName The filename the settings will be written to.
     */
    private static function createDefault($fileName)
    {
        $arr = array();
        
        // Application Settings
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_CULTURE]        = CultureInfo::CULTURE_ENUS;
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_COMPANY]        = "Company Name";
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_NAME]           = "WebCore 3.0 Application Name";
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_VERSION]        = "1.0.0";
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_TIMEZONE]       = "America/Mexico_City";
        $arr[self::SKEY_APPLICATION][self::KEY_APPLICATION_SESSIONHANDLER] = "";
        
        // Cache Settings
        $arr[self::SKEY_CACHE][self::KEY_CACHE_ENABLE]    = "0";
        $arr[self::SKEY_CACHE][self::KEY_CACHE_SERVERS][] = "127.0.0.1";
        $arr[self::SKEY_CACHE][self::KEY_CACHE_PORTS][]   = 11211;
        
        // Database Connection Settings
        $arr[self::SKEY_DATA][self::KEY_DATA_PROVIDER]           = "MySqlConnection";
        $arr[self::SKEY_DATA][self::KEY_DATA_SERVER]             = "localhost";
        $arr[self::SKEY_DATA][self::KEY_DATA_USERNAME]           = "root";
        $arr[self::SKEY_DATA][self::KEY_DATA_PASSWORD]           = "";
        $arr[self::SKEY_DATA][self::KEY_DATA_DATABASE]           = "";
        $arr[self::SKEY_DATA][self::KEY_DATA_PORT]               = 3306;
        $arr[self::SKEY_DATA][self::KEY_DATA_CONCURRENCY]        = "0";
        $arr[self::SKEY_DATA][self::KEY_DATA_DROPENABLE]         = "0";
        $arr[self::SKEY_DATA][self::KEY_DATA_DISABLEDEFERRED]    = "0";
        $arr[self::SKEY_DATA][self::KEY_DATA_LOGICALDELETEFIELD] = "sys_deleted_date";
        
        // Logging Default Settings
        $arr[self::SKEY_LOG][self::KEY_LOG_PROVIDER][] = LogManager::LOGGER_FILE;
        $arr[self::SKEY_LOG][self::KEY_LOG_FILE]       = "webcore.log";
        $arr[self::SKEY_LOG][self::KEY_LOG_LEVEL]      = LogManager::LOG_LEVEL_WARNING;
        $arr[self::SKEY_LOG][self::KEY_LOG_LOGENTITY]  = "Log";
        
        // Compression Default Settings
        $arr[self::SKEY_COMPRESSION][self::KEY_COMPRESSION_ENABLED]  = "0";
        $arr[self::SKEY_COMPRESSION][self::KEY_COMPRESSION_PROVIDER] = HttpCompressor::PROVIDER_RESOURCES;
        $arr[self::SKEY_COMPRESSION][self::KEY_COMPRESSION_STORE]    = HttpCompressor::STORE_CACHEMANAGER;
        
        // Membership Default Settings
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_HASHALGORITHM]         = HashProvider::ALGORITHM_MD5;
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_MEMBERSHIPPROVIDER]    = "DatabaseMembershipProvider";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_AUTHPROVIDER]          = "DatabaseAuthenticationProvider";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_USERENTITY]            = "User";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_USERENTITYKEY]         = "UserId";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_ROLEENTITY]            = "Role";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_ROLEENTITYFIELD]       = "roleId";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_USERROLEENTITY]        = "UserInRole";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_USERROLEENTITYUSERKEY] = "userId";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_LOGINPAGE]             = "login.php";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_USERENTITYFIELD]       = "user,password,enabled";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_ROLEENTITYFIELD]       = "rolename";
        $arr[self::SKEY_SECURITY][self::KEY_SECURITY_WELCOMEPAGE]           = "login.php";
        
        // Mail Default Settings
        $arr[self::SKEY_MAIL][self::KEY_MAIL_SERVER]   = "localhost";
        $arr[self::SKEY_MAIL][self::KEY_MAIL_PORT]     = "25";
        $arr[self::SKEY_MAIL][self::KEY_MAIL_USERNAME] = "";
        $arr[self::SKEY_MAIL][self::KEY_MAIL_PASSWORD] = "";
        
        // Globalization Default Settings
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_DAYSNAMES]     = array(
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        );
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_LONGDATE]      = 'd-m-Y';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_LONGDATETIME]  = 'd-m-Y h:m:s';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_LONGTIME]      = 'h:m:s';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_MONTHSNAMES]   = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_SHORTDATE]     = 'd-m-Y';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_SHORTDATETIME] = 'd-m-Y h:m';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_SHORTTIME]     = 'h:m';
        $arr[CultureInfo::DICT_DATETIME][DateTimeFormatInfo::DICT_DATETIME_FIRSTDAYWEEK]  = '0';
        
        $arr[CultureInfo::DICT_NUMBER][NumberFormatInfo::DICT_NUMBER_CURRENCYFORMAT] = '$#,###.##';
        $arr[CultureInfo::DICT_NUMBER][NumberFormatInfo::DICT_NUMBER_CURRENCYSYMBOL] = '$';
        $arr[CultureInfo::DICT_NUMBER][NumberFormatInfo::DICT_NUMBER_DECIMALDIGIT]   = '.';
        $arr[CultureInfo::DICT_NUMBER][NumberFormatInfo::DICT_NUMBER_FORMAT]         = '#,###.##';
        
        self::$settingsCollection = new KeyedCollectionWrapper($arr, false);
        self::save($fileName);
    }
    
    /**
     * Gets the underlying settings collection
     * @return KeyedCollectionWrapper
     */
    public static function &getCollection()
    {
        if (self::$settingsCollection == null)
            self::load();
        return self::$settingsCollection;
    }
    
    /**
     * Sets a value for a given key
     * @param string $keyName
     * @param mixed $value
     */
    public static function setValue($keyName, $value)
    {
        self::getCollection()->setValue($keyName, $value);
    }
    
    /**
     * Gets a value for the given key
     * @param string $keyName
     * @return mixed
     */
    public static function getValue($keyName)
    {
        if (self::hasValue($keyName))
            return self::getCollection()->getValue($keyName);
        
        throw new SystemException(SystemException::EX_KEYNOTFOUND);
    }
    
    /**
     * Gets a value within a section
     * @param string $sectionKey Typically, one of the SKEY-prefixed constants definded by this class
     * @param string $valueKey Typically, one of the KEY-prefixed contants matching the $sectionKey suffix
     * @return string
     */
    public static function getSectionValue($sectionKey, $valueKey)
    {
        if (self::hasValue($sectionKey))
        {
            $col = self::getCollection()->getItem($sectionKey);
            if (is_array($col))
            {
                if (key_exists($valueKey, $col))
                {
                    return $col[$valueKey];
                }
            }
        }
        
        throw new SystemException(SystemException::EX_KEYNOTFOUND, "The value key '$valueKey' within the '$sectionKey' section key was not found.");
    }
    
    /**
     * Sets a value within a section
     * @param string $sectionKey Typically, one of the SKEY-prefixed constants definded by this class
     * @param string $valueKey Typically, one of the KEY-prefixed contants matching the $sectionKey suffix
     * @param string $value
     */
    public static function setSectionValue($sectionKey, $valueKey, $value)
    {
        if (self::hasValue($sectionKey))
        {
            $col =& self::getCollection()->getItem($sectionKey);
            if (is_array($col))
            {
                $col[$valueKey] = $value;
                return;
            }
        }
        
        throw new SystemException(SystemException::EX_KEYNOTFOUND, "The value key '$valueKey' within the '$sectionKey' section key was not found.");
    }
    
    /**
     * Adds a section to the internal Settings collection. You need to call the save method if you want to persist the section
     * @param string $sectionKey
     */
    public static function addSection($sectionKey)
    {
        if (!self::hasValue($sectionKey))
        {
            self::getCollection()->setValue($sectionKey, array());
        }
    }
    
    /**
     * Determines if a key can be found within the unrelying collection
     * @return bool
     */
    public static function hasValue($keyName)
    {
        return self::getCollection()->keyExists($keyName);
    }
    
    /**
     * Gets all the key names in the collection
     * @return array
     */
    public static function getKeys()
    {
        return self::getCollection()->getKeys();
    }
}

/**
 * Manager class to store and retrieve extension collection.
 * 
 * @package WebCore
 * @subpackage Application
 */
interface IExtensionCollection extends IHelper
{
    public static function loadExtensionCollection();
}

/**
 * Provides all the culture-sensitive resources built into the framework.
 *
 * @package WebCore
 * @subpackage Application
 */
class Resources extends ObjectBase implements IPersistentCollectionManager
{
    protected static $currentCulture;
    
    const DEFAULTFILENAME = 'app.resources';
    
    // SRK stands for Static Resource Key
    const SRK_FIELD_REQUIRED = 'webcore.field.error.required';
    const SRK_FIELD_BADEMAIL = 'webcore.field.error.bademail';
    const SRK_FIELD_BADNUMBER = 'webcore.field.error.badnumber';
    const SRK_FIELD_BADPHONENUMBER = 'webcore.field.error.badphone';
    const SRK_FIELD_BADDATE = 'webcore.field.error.baddate';
    const SRK_FIELD_MAXIMUM = 'webcore.field.error.maximum';
    const SRK_FIELD_MINIMUM = 'webcore.field.error.minimum';
    const SRK_FIELD_NODECIMALS = 'webcore.field.error.nodecimals';
    const SRK_FIELD_DOWNLOAD = 'webcore.field.download';
    const SRK_FORM_ERRORTITLE = 'webcore.form.error.title';
    const SRK_FORM_ERRORGENERIC = 'webcore.form.error.text';
    const SRK_FORM_ERRORCONTINUE = 'webcore.form.error.continue';
    const SRK_FORM_INFOTITLE = 'webcore.form.info.title';
    const SRK_FORM_INFOCONTINUE = 'webcore.form.info.continue';
    const SRK_UPLOAD_ERR_OK = 'webcore.postedfile.error.ok';
    const SRK_UPLOAD_ERR_CANT_WRITE = 'webcore.postedfile.error.cantwrite';
    const SRK_UPLOAD_ERR_EXTENSION = 'webcore.postedfile.error.extension';
    const SRK_UPLOAD_ERR_FORM_SIZE = 'webcore.postedfile.error.formsize';
    const SRK_UPLOAD_ERR_INI_SIZE = 'webcore.postedfile.error.inisize';
    const SRK_UPLOAD_ERR_NO_FILE = 'webcore.postedfile.error.nofile';
    const SRK_UPLOAD_ERR_NO_TMP_DIR = 'webcore.postedfile.error.notmpdir';
    const SRK_UPLOAD_ERR_PARTIAL = 'webcore.postedfile.error.partial';
    const SRK_MEMBERSHIP_ERR_LOGIN = 'webcore.membership.error.login';
    const SRK_MONTH_NAMES = 'webcore.monthnames';
    const SRK_MONTH_SHORTNAMES = 'webcore.monthshortnames';
    const SRK_DAY_NAMES = 'webcore.daynames';
    const SRK_DAY_SHORTNAMES = 'webcore.dayshortnames';
    const SRK_LOGIN_FORM_CAPTION = 'webcore.login.form.caption';
    const SRK_LOGIN_USER_CAPTION = 'webcore.login.user.caption';
    const SRK_LOGIN_USER_TOOLTIP = 'webcore.login.user.tooltip';
    const SRK_LOGIN_PASSWORD_CAPTION = 'webcore.login.password.caption';
    const SRK_LOGIN_PASSWORD_TOOLTIP = 'webcore.login.password.tooltip';
    const SRK_LOGIN_BUTTON_CAPTION = 'webcore.login.button.caption';
    const SRK_GRID_PAGINATOR_FOOTER = 'webcore.grid.paginator.footer';
    const SRK_GRID_TOOLBAR_SEARCH = 'webcore.grid.toolbar.search';
    const SRK_GRID_TOOLBAR_FILTER = 'webcore.grid.toolbar.filter';
    const SRK_GRID_TOOLBAR_GROUP = 'webcore.grid.toolbar.group';
    const SRK_GRID_TOOLBAR_TOOLS = 'webcore.grid.toolbar.tools';
    const SRK_REPEATER_PAGER_FIRST = 'webcore.grid.pager.first';
    const SRK_REPEATER_PAGER_PREV = 'webcore.grid.pager.prev';
    const SRK_REPEATER_PAGER_NEXT = 'webcore.grid.pager.next';
    const SRK_REPEATER_PAGER_LAST = 'webcore.grid.pager.last';
    const SRK_GRID_PAGER_PAGE = 'webcore.grid.pager.page';
    const SRK_REPEATER_PAGER_RECORDS = 'webcore.grid.pager.records';
    const SRK_REPEATER_PAGER_NORECORDS = 'webcore.grid.pager.norecords';
    const SRK_GRID_PAGER_OFPAGE = 'webcore.grid.pager.ofpage';
    const SRK_GRID_COMMAND_VIEW = 'webcore.grid.command.view';
    const SRK_GRID_COMMAND_EDIT = 'webcore.grid.command.edit';
    const SRK_GRID_COMMAND_DELETE = 'webcore.grid.command.delete';
    const SRK_GRID_COMMAND_SELECT = 'webcore.grid.command.select';
    const SRK_GRID_NOFILTER = 'webcore.grid.filtering.nofilter';
    const SRK_GRID_NOSEARCH = 'webcore.grid.searching.nosearch';
    const SRK_GRID_ACTION_PRINT = 'webcore.grid.actions.print';
    const SRK_CAPTION_DATE = 'webcore.caption.date';
    const SRK_CAPTION_TIME = 'webcore.caption.time';
    const SRK_CAPTION_HOUR = 'webcore.caption.hour';
    const SRK_CAPTION_PASSWORD = 'webcore.caption.password';
    const SRK_CAPTION_EMAIL = 'webcore.caption.email';
    const SRK_CAPTION_ADD = 'webcore.caption.add';
    const SRK_CAPTION_DELETE = 'webcore.caption.delete';
    const SRK_CAPTION_SAVE = 'webcore.caption.save';
    const SRK_CAPTION_CANCEL = 'webcore.caption.cancel';
    const SRK_CAPTION_EDIT = 'webcore.caption.edit';
    const SRK_CONFIRM_DELETE = 'webcore.confirm.delete';
    const SRK_OPER_STARTSWITH = 'webcore.grid.searchdialog.startswith';
    const SRK_OPER_ENDSWITH = 'webcore.grid.searchdialog.endswith';
    const SRK_OPER_CONTAINS = 'webcore.grid.searchdialog.contains';
    const SRK_OPER_EQUALS = 'webcore.grid.searchdialog.equals';
    const SRK_OPER_BETWEEN = 'webcore.grid.searchdialog.between';
    const SRK_SEARCH_DIALOG_CAPTION = 'webcore.grid.searchdialog.caption';
    const SRK_SEARCH_DIALOG_MSG = 'webcore.grid.searchdialog.message';
    const SRK_SEARCH_DIALOG_OK = 'webcore.grid.searchdialog.okbutton';
    const SRK_SEARCH_DIALOG_CANCEL = 'webcore.grid.searchdialog.cancelbutton';
    const SRK_PRINTVIEW_PRINT = 'webcore.printview.printbutton';
    const SRK_PRINTVIEW_TITLE = 'webcore.printview.pagetitle';
    const SRK_PRINTVIEW_SUBTITLE = 'webcore.printview.subtitle';
    const SRK_PRINTVIEW_CSVEXPORT = 'webcore.printview.csvexportbutton';
    const SRK_PRINTVIEW_BIFFEXPORT = 'webcore.printview.biffexportbutton';
    const SRK_PRINTVIEW_OXMLEXPORT = 'webcore.printview.oxmlexportbutton';
    const SRK_PRINTVIEW_PDFEXPORT = 'webcore.printview.pdfexportbutton';
    const SRK_CALENDAR_NAV_PREVIOUS = 'webcore.calendar.navprevious';
    const SRK_CALENDAR_NAV_NEXT = 'webcore.calendar.navnext';
    const SRK_CALENDAR_NAV_TODAY = 'webcore.calendar.navtoday';
    const SRK_CALENDAR_VIEW_DAY = 'webcore.calendar.viewday';
    const SRK_CALENDAR_VIEW_WEEK = 'webcore.calendar.viewweek';
    const SRK_CALENDAR_VIEW_MONTH = 'webcore.calendar.viewmonth';
    const SRK_APPOINTMENT_TOOLTIP_EMPTY = 'webcore.appointment.tooltipempty';
    const SRK_MULTISELECTOR_AVAILABLE = 'webcore.multiselector.available';
    const SRK_MULTISELECTOR_SELECTED = 'webcore.multiselector.selected';
    
    private static $frameworkResources = array(
        CultureInfo::CULTURE_ENUS => array(
            self::SRK_FIELD_REQUIRED => 'This field is required.',
            self::SRK_FORM_ERRORTITLE => 'Error.',
            self::SRK_FORM_INFOTITLE => 'Information.',
            self::SRK_FORM_ERRORGENERIC => 'You must correct the errors in the marked fields in order to continue.',
            self::SRK_FORM_ERRORCONTINUE => 'OK',
            self::SRK_FORM_INFOCONTINUE => 'Continue',
            self::SRK_UPLOAD_ERR_OK => 'The file was uploaded successufuly.',
            self::SRK_UPLOAD_ERR_CANT_WRITE => 'Error writing the file to the server. Insufficient permissions.',
            self::SRK_UPLOAD_ERR_EXTENSION => 'The extension of the uploaded file is unacceptable.',
            self::SRK_UPLOAD_ERR_FORM_SIZE => 'The file you provided is larger than the allowed limit.',
            self::SRK_UPLOAD_ERR_INI_SIZE => 'The file you provided is larger than the allowed limit.',
            self::SRK_UPLOAD_ERR_NO_FILE => 'You did not provide a valid file to upload.',
            self::SRK_UPLOAD_ERR_NO_TMP_DIR => 'The server is having technical difficulties. Try again later.',
            self::SRK_UPLOAD_ERR_PARTIAL => 'The file was partially uploaded. Please try again.',
            self::SRK_FIELD_BADEMAIL => 'The email address is not in the correct format.',
            self::SRK_FIELD_BADNUMBER => 'The number format could not be recognized.',
            self::SRK_FIELD_BADDATE => 'The date is not recognized. Please specify the date in the format: YYYY-MM-DD',
            self::SRK_FIELD_MAXIMUM => 'The number is out of the allowed range. The maximum value is ',
            self::SRK_FIELD_MINIMUM => 'The number is out of the allowed range. The minimum value is ',
            self::SRK_FIELD_NODECIMALS => 'The number must be an integer. No decimals are allowed.',
            self::SRK_FIELD_BADPHONENUMBER => 'The phone number you provided is invalid.',
            self::SRK_MEMBERSHIP_ERR_LOGIN => 'Invalid username or password.',
            self::SRK_MONTH_NAMES => 'January,February,March,April,May,June,July,August,September,October,November,December',
            self::SRK_MONTH_SHORTNAMES => 'JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC',
            self::SRK_DAY_NAMES => 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            self::SRK_DAY_SHORTNAMES => 'Sun,Mon,Tue,Wed,Thr,Fri,Sat',
            self::SRK_LOGIN_FORM_CAPTION => 'Sign In',
            self::SRK_LOGIN_USER_CAPTION => 'Username',
            self::SRK_LOGIN_USER_TOOLTIP => 'Your username',
            self::SRK_LOGIN_PASSWORD_CAPTION => 'Password',
            self::SRK_LOGIN_PASSWORD_TOOLTIP => 'Your password',
            self::SRK_LOGIN_BUTTON_CAPTION => 'Sign In',
            self::SRK_GRID_PAGINATOR_FOOTER => 'Page <b>%s</b> of <b>%s</b>',
            self::SRK_FIELD_DOWNLOAD => 'Download file',
            self::SRK_GRID_TOOLBAR_TOOLS => 'Tools',
            self::SRK_GRID_TOOLBAR_SEARCH => 'Search',
            self::SRK_GRID_TOOLBAR_GROUP => 'Group',
            self::SRK_GRID_TOOLBAR_FILTER => 'Filter',
            self::SRK_REPEATER_PAGER_FIRST => 'First Page',
            self::SRK_REPEATER_PAGER_LAST => 'Last Page',
            self::SRK_REPEATER_PAGER_PREV => 'Previous Page',
            self::SRK_REPEATER_PAGER_NEXT => 'Next Page',
            self::SRK_GRID_PAGER_PAGE => 'Page ',
            self::SRK_REPEATER_PAGER_RECORDS => 'Displaying records <b>%s</b> to <b>%s</b> of <b>%s</b>',
            self::SRK_REPEATER_PAGER_NORECORDS => 'There are no records under this view.',
            self::SRK_GRID_PAGER_OFPAGE => ' of ',
            self::SRK_GRID_COMMAND_VIEW => 'View Details',
            self::SRK_GRID_COMMAND_EDIT => 'Edit Item',
            self::SRK_GRID_COMMAND_DELETE => 'Delete Item',
            self::SRK_GRID_COMMAND_SELECT => 'Select',
            self::SRK_GRID_NOFILTER => '(No quick filter)',
            self::SRK_GRID_NOSEARCH => '(No search filter)',
            self::SRK_GRID_ACTION_PRINT => 'Print Preview ...',
            self::SRK_CAPTION_DATE => 'date',
            self::SRK_CAPTION_TIME => 'time',
            self::SRK_CAPTION_HOUR => 'hour',
            self::SRK_CAPTION_PASSWORD => 'password',
            self::SRK_CAPTION_EMAIL => 'email',
            self::SRK_CAPTION_ADD => 'Add',
            self::SRK_CAPTION_DELETE => 'Delete',
            self::SRK_CAPTION_SAVE => 'Save',
            self::SRK_CAPTION_CANCEL => 'Cancel',
            self::SRK_CAPTION_EDIT => 'Edit',
            self::SRK_CONFIRM_DELETE => 'Are you sure you want to delete this entry permanently?',
            self::SRK_OPER_STARTSWITH => 'starts with',
            self::SRK_OPER_ENDSWITH => 'ends with',
            self::SRK_OPER_CONTAINS => 'contains',
            self::SRK_OPER_EQUALS => 'equals',
            self::SRK_OPER_BETWEEN => 'between',
            self::SRK_SEARCH_DIALOG_CAPTION => 'Search',
            self::SRK_SEARCH_DIALOG_MSG => 'Enter the search criteria below.',
            self::SRK_SEARCH_DIALOG_OK => 'OK',
            self::SRK_SEARCH_DIALOG_CANCEL => 'Cancel',
            self::SRK_PRINTVIEW_PRINT => 'Send to Printer',
            self::SRK_PRINTVIEW_TITLE => 'Print Preview - ',
            self::SRK_PRINTVIEW_SUBTITLE => 'Data as of ',
            self::SRK_PRINTVIEW_CSVEXPORT => 'Export as CSV',
            self::SRK_PRINTVIEW_BIFFEXPORT => 'Export as Excel 97-2003',
            self::SRK_PRINTVIEW_OXMLEXPORT => 'Export as Excel 2007',
            self::SRK_PRINTVIEW_PDFEXPORT => 'Export as PDF',
            self::SRK_CALENDAR_NAV_PREVIOUS => 'Previous',
            self::SRK_CALENDAR_NAV_NEXT => 'Next',
            self::SRK_CALENDAR_NAV_TODAY => 'Today',
            self::SRK_CALENDAR_VIEW_DAY => 'Day View',
            self::SRK_CALENDAR_VIEW_WEEK => 'Week View',
            self::SRK_CALENDAR_VIEW_MONTH => 'Month View',
            self::SRK_APPOINTMENT_TOOLTIP_EMPTY => 'Double click to expand...',
            self::SRK_MULTISELECTOR_AVAILABLE => 'Available',
            self::SRK_MULTISELECTOR_SELECTED => 'Selected'),

        CultureInfo::CULTURE_ESMX => array(
            self::SRK_FIELD_REQUIRED => 'Este campo es obligatorio.',
            self::SRK_FORM_ERRORTITLE => 'Error.',
            self::SRK_FORM_INFOTITLE => 'Información.',
            self::SRK_FORM_ERRORGENERIC => 'Existen errores en la captura. Corrija los campos marcados con error para continuar.',
            self::SRK_FORM_ERRORCONTINUE => 'Aceptar',
            self::SRK_FORM_INFOCONTINUE => 'Continuar',
            self::SRK_UPLOAD_ERR_OK => 'El archivo fue cargado existosamente.',
            self::SRK_UPLOAD_ERR_CANT_WRITE => 'Privilegios insuficientes al guardar el archivo en el servidor.',
            self::SRK_UPLOAD_ERR_EXTENSION => 'La extensión del archivo cargado es inaceptable.',
            self::SRK_UPLOAD_ERR_FORM_SIZE => 'El tamaño del archivo proporcionado excede el límite permitido.',
            self::SRK_UPLOAD_ERR_INI_SIZE => 'El tamaño del archivo proporcionado excede el límite permitido.',
            self::SRK_UPLOAD_ERR_NO_FILE => 'No proporcionó un archivo a cargar válido.',
            self::SRK_UPLOAD_ERR_NO_TMP_DIR => 'El servidor tiene dificultades técnicas por el momento. Intente más tarde.',
            self::SRK_UPLOAD_ERR_PARTIAL => 'El archivo fue cargado parcialmente. Intente de nuevo.',
            self::SRK_FIELD_BADEMAIL => 'Proporcione un correo electrónico en un formato válido',
            self::SRK_FIELD_BADNUMBER => 'El número que proporcionó se encuentra en un formato irreconocible.',
            self::SRK_FIELD_BADDATE => 'La fecha no es reconocida. Especifique la fecha en el formato: AAAA-MM-DD',
            self::SRK_FIELD_MAXIMUM => 'El número que proporcionó es muy grande. El máximo es ',
            self::SRK_FIELD_MINIMUM => 'El número que proporcionó es muy pequeño. El mínimo es ',
            self::SRK_FIELD_NODECIMALS => 'El número debe ser entero. No proporcione decimales.',
            self::SRK_FIELD_BADPHONENUMBER => 'El número telefónico que proporcionó es inválido.',
            self::SRK_MEMBERSHIP_ERR_LOGIN => 'Usuario o contraseña incorrecta.',
            self::SRK_MONTH_NAMES => 'Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre',
            self::SRK_MONTH_SHORTNAMES => 'ENE,FEB,MAR,ABR,MAY,JUN,JUL,AGO,SEP,OCT,NOV,DIC',
            self::SRK_DAY_NAMES => 'Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
            self::SRK_DAY_SHORTNAMES => 'Dom,Lun,Mar,Mie,Jue,Vie,Sab',
            self::SRK_LOGIN_FORM_CAPTION => 'Inicio de Sesión',
            self::SRK_LOGIN_USER_CAPTION => 'Nombre de Usuario',
            self::SRK_LOGIN_USER_TOOLTIP => 'Su nombre de usuario',
            self::SRK_LOGIN_PASSWORD_CAPTION => 'Contraseña',
            self::SRK_LOGIN_PASSWORD_TOOLTIP => 'Su contraseña',
            self::SRK_LOGIN_BUTTON_CAPTION => 'Entrar',
            self::SRK_GRID_PAGINATOR_FOOTER => 'P&aacute;gina <b>%s</b> de <b>%s</b>',
            self::SRK_FIELD_DOWNLOAD => 'Descargar archivo',
            self::SRK_GRID_TOOLBAR_TOOLS => 'Herramientas',
            self::SRK_GRID_TOOLBAR_SEARCH => 'Buscar',
            self::SRK_GRID_TOOLBAR_GROUP => 'Agrupar',
            self::SRK_GRID_TOOLBAR_FILTER => 'Filtrar',
            self::SRK_REPEATER_PAGER_FIRST => 'Primera Página',
            self::SRK_REPEATER_PAGER_LAST => 'Última Página',
            self::SRK_REPEATER_PAGER_PREV => 'Página Anterior',
            self::SRK_REPEATER_PAGER_NEXT => 'Página Siguiente',
            self::SRK_GRID_PAGER_PAGE => 'Página ',
            self::SRK_GRID_PAGER_OFPAGE => ' de ',
            self::SRK_GRID_COMMAND_VIEW => 'Ver Detalles',
            self::SRK_GRID_COMMAND_EDIT => 'Editar Registro',
            self::SRK_GRID_COMMAND_DELETE => 'Borrar Registro',
            self::SRK_GRID_COMMAND_SELECT => 'Seleccionar',
            self::SRK_GRID_NOFILTER => '(Sin filtro rápido)',
            self::SRK_GRID_NOSEARCH => '(Sin filtro de búsqueda)',
            self::SRK_GRID_ACTION_PRINT => 'Vista de Impresión ...',
            self::SRK_REPEATER_PAGER_RECORDS => 'Registros <b>%s</b> al <b>%s</b> de <b>%s</b>',
            self::SRK_REPEATER_PAGER_NORECORDS => 'No existen registros bajo esta vista.',
            self::SRK_CAPTION_DATE => 'fecha',
            self::SRK_CAPTION_TIME => 'tiempo',
            self::SRK_CAPTION_HOUR => 'hora',
            self::SRK_CAPTION_PASSWORD => 'contraseña',
            self::SRK_CAPTION_EMAIL => 'correo electrónico',
            self::SRK_CAPTION_ADD => 'Agregar',
            self::SRK_CAPTION_DELETE => 'Eliminar',
            self::SRK_CAPTION_SAVE => 'Guardar',
            self::SRK_CAPTION_CANCEL => 'Cancelar',
            self::SRK_CAPTION_EDIT => 'Editar',
            self::SRK_CONFIRM_DELETE => '¿Desea eliminar el registro permanentemente?',
            self::SRK_OPER_STARTSWITH => 'comienza con',
            self::SRK_OPER_ENDSWITH => 'termina con',
            self::SRK_OPER_CONTAINS => 'contiene',
            self::SRK_OPER_EQUALS => 'es igual a',
            self::SRK_OPER_BETWEEN => 'entre',
            self::SRK_SEARCH_DIALOG_CAPTION => 'Búsqueda',
            self::SRK_SEARCH_DIALOG_MSG => 'Introduzca los criterios de búsqueda.',
            self::SRK_SEARCH_DIALOG_OK => 'Aceptar',
            self::SRK_SEARCH_DIALOG_CANCEL => 'Cancelar',
            self::SRK_PRINTVIEW_PRINT => 'Enviar a Impresora',
            self::SRK_PRINTVIEW_TITLE => 'Vista de Impresión - ',
            self::SRK_PRINTVIEW_SUBTITLE => 'Fecha de Generación: ',
            self::SRK_PRINTVIEW_CSVEXPORT => 'Exportar como CSV',
            self::SRK_PRINTVIEW_BIFFEXPORT => 'Exportar a Excel 97-2003',
            self::SRK_PRINTVIEW_OXMLEXPORT => 'Exportar a Excel 2007',
            self::SRK_PRINTVIEW_PDFEXPORT => 'Exportar a PDF',
            self::SRK_CALENDAR_NAV_PREVIOUS => 'Anterior',
            self::SRK_CALENDAR_NAV_NEXT => 'Siguiente',
            self::SRK_CALENDAR_NAV_TODAY => 'Hoy',
            self::SRK_CALENDAR_VIEW_DAY => 'Ver Día',
            self::SRK_CALENDAR_VIEW_WEEK => 'Ver Semana',
            self::SRK_CALENDAR_VIEW_MONTH => 'Ver Mes',
            self::SRK_APPOINTMENT_TOOLTIP_EMPTY => 'Doble clic para expandir...',
            self::SRK_MULTISELECTOR_AVAILABLE => 'Disponibles',
            self::SRK_MULTISELECTOR_SELECTED => 'Seleccionados'));

    
    /**
     * Represents the internal collection
     * @var KeyedCollectionWrapper
     */
    private static $internalCollection = null;
    private static $storeName;
    
    /**
     * Sets current user culture
     *
     * @param string $value
     */
    public static function setUserCulture($value)
    {
        self::$currentCulture = $value;
    }
    
    /**
     * Gets the current application culture as specified by the Settings
     * 
     * @return string
     */
    public static function getCulture()
    {
        if (is_null(self::$currentCulture))
        {
            $appSettings = Settings::hasValue(Settings::SKEY_APPLICATION) ? Settings::getValue(Settings::SKEY_APPLICATION) : array();
            
            if (array_key_exists(Settings::KEY_APPLICATION_CULTURE, $appSettings))
            {
                $find = $appSettings[Settings::KEY_APPLICATION_CULTURE];
                if (self::getCollection()->keyExists($find))
                {
                    self::$currentCulture = $appSettings[Settings::KEY_APPLICATION_CULTURE];
                }
                else
                {
                    throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'The settings file specifies an application culture setting that is not supported by the resources file.');
                }
            }
            else
            {
                self::$currentCulture = CultureInfo::CULTURE_ENUS;
            }
        }
        
        return self::$currentCulture;
    }
    
    /**
     * Loads the settings from from the given file.
     * If the file is not found, a defualt settings file is created
     * @param string $storeName If not specified, the DEFAULTFILENAME constant is used instead
     */
    public static function load($storeName = '')
    {
        $data = '';
        if ($storeName === '')
        {
            self::$storeName = HttpContext::getDocumentRoot() . self::DEFAULTFILENAME;
        }
        else
        {
            self::$storeName = $storeName;
        }
        
        if (file_exists(self::$storeName) === false) self::createDefault(self::$storeName);
        $data = file_get_contents(self::$storeName);
        //$data = utf8_encode($data);
        self::$internalCollection = XmlSerializer::deserialize($data, 'KeyedCollectionWrapper');
        
    }
    
    /**
     * Gets the store name from wich the collection was loaded.
     *
     * @return string
     */
    public static function getStoreName()
    {
        return self::$storeName;
    }
    
    /**
     * Saves the changes to the settings
     * If a store name is not specified, it will use the default.
     * @param string $storeName The filename to save the setting file to.
     */
    public static function save($storeName = '')
    {
        if (self::$internalCollection == null)
            self::load($storeName);
        
        if ($storeName === '')
            $fileName = HttpContext::getDocumentRoot() . self::DEFAULTFILENAME;
        else
            $fileName = $storeName;
        
        $data = XmlSerializer::serialize(self::$internalCollection);
        $data = utf8_decode($data);
        file_put_contents($fileName, $data);
    }
    
    /**
     * Creates a default settings file
     * @param string $fileName The filename the settings will be written to.
     */
    private static function createDefault($fileName)
    {
        $defaultResources         = self::$frameworkResources;
        self::$internalCollection = new KeyedCollectionWrapper($defaultResources, false);
        self::save($fileName);
    }
    
    /**
     * Gets the underlying settings collection
     * @return KeyedCollectionWrapper
     */
    public static function &getCollection()
    {
        if (self::$internalCollection == null)
            self::load();
        
        return self::$internalCollection;
    }
    
    /**
     * sets a value for a given key
     * @param string $keyName
     * @param mixed $value
     * @param string $culture
     */
    public static function setValueByCulture($keyName, $value, $culture)
    {
        $arr =& self::getCollection()->getArrayReference();
        $arr[$culture][$keyName] = $value;
    }
    
    /**
     * sets a value for a given key
     * @param string $keyName
     * @param mixed $value
     */
    public static function setValue($keyName, $value)
    {
        $arr =& self::getCollection()->getArrayReference();
        $arr[self::getCulture()][$keyName] = $value;
    }
    
    /**
     * Gets a value for the given key
     * @return mixed
     */
    public static function getValue($keyName)
    {
        if (self::hasValue($keyName) == false)
            return '';
        $arr =& self::getCollection()->getArrayReference();
        return $arr[self::getCulture()][$keyName];
    }
    
    /**
     * Determines if a key can be found within the underlying collection
     * @return bool
     */
    public static function hasValue($keyName)
    {
        $arr =& self::getCollection()->getArrayReference();
        if (array_key_exists(self::getCulture(), $arr))
        {
            if (array_key_exists($keyName, $arr[self::getCulture()]))
                return true;
        }
        return false;
    }
    
    /**
     * Gets all the key names in the collection
     * @return array
     */
    public static function getKeys()
    {
        $arr =& self::getCollection()->getArrayReference();
        return array_keys($arr[self::getCulture()]);
    }
}
?>
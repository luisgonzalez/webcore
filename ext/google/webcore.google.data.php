<?php
/**
 * Represents Google Data Settings
 *
 * @package WebCore
 * @subpackage Google
 */
class GDataSettings extends HelperBase implements IExtensionCollection
{
    const SKEY_GDATA = 'gdata';
    const KEY_GDATA_USERNAME = 'username';
    const KEY_GDATA_PASSWORD = 'password';
    
    public static function loadExtensionCollection()
    {
        if (Settings::hasValue(GDataSettings::SKEY_GDATA) == false)
        {
            $defaultsValues                                    = array();
            $defaultsValues[GDataSettings::KEY_GDATA_USERNAME] = 'test@gmail.com';
            $defaultsValues[GDataSettings::KEY_GDATA_PASSWORD] = 'password';
            
            Settings::setValue(GDataSettings::SKEY_GDATA, $defaultsValues);
            Settings::save();
        }
    }
}

/**
 * Represents a Google Data API client
 *
 * @todo Change internal POST/GET for WebClient
 * @package WebCore
 * @subpackage Google
 */
class GDataClient extends ObjectBase
{
    private $authToken;
    
    const GOOGLE_SPREADSHEET = 'wise';
    const GOOGLE_CONTACTS = 'cp';
    const GOOGLE_ANALYTICS = 'analytics';
    
    /**
     * Creates a new instance of this class
     *
     * @param string $service
     * @param string $username
     * @param string $password
     */
    public function __construct($service, $username = '', $password = '')
    {
        if ($username == '')
        {
            GDataSettings::loadExtensionCollection();
            
            $settings = Settings::getValue(GDataSettings::SKEY_GDATA);
            $username = $settings[GDataSettings::KEY_GDATA_USERNAME];
            $password = $settings[GDataSettings::KEY_GDATA_PASSWORD];
        }
        
        $url                 = "https://www.google.com/accounts/ClientLogin";
        $data                = array();
        $data['accountType'] = 'GOOGLE';
        $data['Email']       = $username;
        $data['Passwd']      = $password;
        $data['service']     = $service;
        $data['source']      = 'WebCore3';
        
        $query  = http_build_query($data);
        $result = $this->doPost($url, $query);
        
        $this->parseToken($result);
    }
    
    /**
     * Does a cURL GET request
     *
     * @param string $url
     * @return string
     */
    protected function doGet($url)
    {
        $curl_handle = curl_init();
        
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
            'Authorization: GoogleLogin auth=' . $this->authToken
        ));
        
        $output = curl_exec($curl_handle);
        
        if ($output === false)
            return curl_error($curl_handle);
        
        curl_close($curl_handle);
        
        return trim($output);
    }
    
    /**
     * Does a socket POST request
     *
     * @param string $url
     * @return string
     */
    protected function doPost($url, $data)
    {
        $curl_handle = curl_init();
        
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        
        if (is_array($data))
        {
            curl_setopt($curl_handle, CURLOPT_POST, 1);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        }
        
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
            'Authorization: GoogleLogin auth=' . $this->authToken
        ));
        
        $output = curl_exec($curl_handle);
        
        if ($output === false)
            throw new Exception();
        
        curl_close($curl_handle);
        
        return trim($output);
    }
    
    /**
     * Parses header to get GData token
     *
     * @param string $data
     * @return string
     */
    private function parseToken($data)
    {
        $tokens = split("\n", $data);
        
        foreach ($tokens AS $current)
        {
            $artemp = split("=", $current);
            if ($artemp[0] == 'Auth')
            {
                $this->authToken = $artemp[1];
                return;
            }
        }
        
        throw new SystemException(SystemException::EX_INVALIDKEY);
    }
    
    /**
     * Returns GData token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->authToken;
    }
}

/**
 * Represents a Google Data Contacts client
 * 
 * @package WebCore
 * @subpackage Google
 */
class GDataContactsClient extends GDataClient
{
    /**
     * Creates a new instance of this class
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username = '', $password = '')
    {
        parent::__construct(GDataClient::GOOGLE_CONTACTS, $username, $password);
    }
    
    public function getContacts()
    {
        $url      = "http://www.google.com/m8/feeds/contacts/default/full?max-results=100&alt=json";
        $jsonData = $this->doGet($url);
        $jsonData = str_replace('$', '', $jsonData);
        
        $list = json_decode($jsonData, true);
        
        if (key_exists('feed', $list) === false)
            throw new SystemException(SystemException::EX_KEYNOTFOUND, 'Key = feed');
        
        $list     = $list['feed']['entry'];
        $contacts = new KeyedCollection();
        
        foreach ($list as $key => $name)
        {
            $user      = utf8_decode($name['title']['t']);
            $emailData = $name['gdemail'][0];
            $email     = $emailData['address'];
            
            $contacts->setValue($email, $user);
        }
        
        return $contacts;
    }
}

/**
 * Represents a Google Data Spreadsheet client
 * 
 * @package WebCore
 * @subpackage Google
 */
class GDataSpreadsheetClient extends GDataClient
{
    /**
     * Creates a new instance of this class
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username = '', $password = '')
    {
        parent::__construct(GDataClient::GOOGLE_SPREADSHEET, $username, $password);
    }
    
    /**
     * Gets spreadsheets from a JSON feed
     *
     * @return array
     */
    public function getSpreadsheets()
    {
        return $this->getEntries("http://spreadsheets.google.com/feeds/spreadsheets/private/full?alt=json");
    }
    
    /**
     * Gets entry cells
     *
     * @param string $url
     * @return array
     */
    public function getEntriesCells($url)
    {
        $jsonData = $this->doGet($url);
        $jsonData = str_replace('$', '', $jsonData);
        
        $list    = json_decode($jsonData, true);
        $entries = array();
        
        if (key_exists('feed', $list) === false)
            throw new SystemException(SystemException::EX_KEYNOTFOUND, 'Key = feed');
        
        $list = $list['feed']->entry;
        
        foreach ($list as $entry)
        {
            $key = explode("/", $entry->id->t);
            $key = $key[count($key) - 1];
            $key = explode("C", $key);
            
            $row    = $key[0];
            $column = $key[1];
            
            $entries[$row][$column] = $entry->content->t;
        }
        
        return $entries;
    }
    
    /**
     * Get entries for a GData source
     *
     * @param string $url
     * @return array
     */
    public function getEntries($url)
    {
        $jsonData = $this->doGet($url);
        $jsonData = str_replace('$', '', $jsonData);
        
        $list    = json_decode($jsonData, true);
        $entries = array();
        
        if (key_exists('feed', $list) === false)
            throw new SystemException(SystemException::EX_KEYNOTFOUND, 'Key = feed');
        
        $list = $list['feed']->entry;
        
        foreach ($list as $entry)
            $entries[$entry->id->t] = $entry->title->t;
        
        return $entries;
    }
    
    /**
     * Return spreadsheet GData key
     *
     * @param string $name
     * @return string
     */
    public function getSpreadsheetKey($name)
    {
        $entries = $this->getSpreadsheets();
        
        foreach ($entries as $key => $title)
        {
            if ($title == $name)
                return $key;
        }
    }
    
    /**
     * Gets worksheets from a URL
     *
     * @param string $url
     * @return array
     */
    public function getWorksheets($url)
    {
        $key = explode("/", $url);
        $key = $key[count($key) - 1];
        
        return $this->getEntries("http://spreadsheets.google.com/feeds/worksheets/$key/private/full?alt=json");
    }
    
    /**
     * Get cells from a spreadsheet
     *
     * @param string $spreadUrl
     * @param string $url
     * @return array
     */
    public function getCells($spreadUrl, $url)
    {
        $spreadKey = explode("/", $spreadUrl);
        $spreadKey = $spreadKey[count($spreadKey) - 1];
        
        $key = explode("/", $url);
        $key = $key[count($key) - 1];
        
        return $this->getEntriesCells("http://spreadsheets.google.com/feeds/cells/$spreadKey/$key/private/basic?alt=json");
    }
}

/**
 * Represents a Google Data Analytics client
 *
 * @todo Change arrays for IndexedCollections
 * @package WebCore
 * @subpackage Google
 */
class GDataAnalyticsClient extends GDataClient
{
    // METRICS DEFINITIONS
    const GA_METRIC_BOUNCES = 'ga:bounces'; // The total number of single-page visits to your site.
    const GA_METRIC_ENTRANCES = 'ga:entrances'; // The number of entrances to your site. 
    const GA_METRIC_EXITS = 'ga:exits'; // The number of exits from your site. 
    const GA_METRIC_NEW_VISITS = 'ga:newVisits'; // The number of visitors whose visit to your site was marked as a first-time visit.
    const GA_METRIC_PAGE_VIEWS = 'ga:pageviews'; // The total number of pageviews for your site when aggregated over the selected dimension. 
    const GA_METRIC_TIME_ON_PAGE = 'ga:timeOnPage'; // How long a visitor spent on a particular page or set of pages. 
    const GA_METRIC_TIME_ON_SITE = 'ga:timeOnSite'; // The total duration of visitor sessions over the selected dimension. 
    const GA_METRIC_VISITORS = 'ga:visitors'; // Total number of visitors to your site for the requested time period. 
    const GA_METRIC_VISITS = 'ga:visits'; // The total number of visits over the selected dimension
    
    // DIMENSIONS DEFINITIONS
    const GA_DIMENSION_REGION = 'ga:region'; // The region of site visitors, derived from IP addresses. In the U.S., a region is a state, such as New York.
    const GA_DIMENSION_DATE = 'ga:date'; // The date of the visit. An integer in the form YYYYMMDD.
    const GA_DIMENSION_MONTH = 'ga:month'; // The month of the visit. A two digit integer from 01 to 12.
    const GA_DIMENSION_YEAR = 'ga:year'; // The year of the visit. A four-digit year from 2005 to the current year. 
    const GA_DIMENSION_DAY = 'ga:day'; // The day of the month from 01 to 31.
    const GA_DIMENSION_HOUR = 'ga:hour'; // A two digit hour of the day ranging from 00-23. 
    const GA_DIMENSION_OS = 'ga:operatingSystem'; // The operating system used by your visitors. 
    const GA_DIMENSION_OS_VERSION = 'ga:operatingSystemVersion'; // The version of the operating system used by your visitors, such as XP for Windows or PPC for Macintosh.
    
    protected $profileId;
    protected $metrics;
    protected $dimensions;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $profileId
     * @param string $username
     * @param string $password
     */
    public function __construct($profileId, $username = '', $password = '')
    {
        parent::__construct(GDataClient::GOOGLE_ANALYTICS, $username, $password);
        
        $this->profileId  = $profileId;
        $this->metrics    = array();
        $this->dimensions = array();
    }
    
    /**
     * Adds a metric for the query
     *
     * @param string $metric. One of the GA_METRIC_ prefixed constants
     */
    public function addMetric($value)
    {
        if (array_search($value, $this->metrics) === false)
            $this->metrics[] = $value;
    }
    
    /**
     * Adds a metric for the query
     *
     * @param string $metric. One of the GA_DIMENSION prefixed constants
     */
    public function addDimension($value)
    {
        if (array_search($value, $this->dimensions) === false)
            $this->dimensions[] = $value;
    }
    
    /**
     * Gets the Data according to the provided metrics and dimensions.
     * If no metrics or dimensions are provided, an Invalid Method Call Exception is thrown
     *
     * @link http://code.google.com/apis/analytics/docs/gdata/gdataReferenceDimensionsMetrics.html Dimesions & Metrics
     *
     * @param string $startDate. Date in the format YYYY-MM-DD
     * @param string $endDate. Date in the format YYYY-MM-DD
     *
     * @return string
     */
    public function getData($startDate, $endDate, $returnSignificantValuesOnly = true)
    {
        if (count($this->metrics) == 0)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'There must be at least 1 metric');
        
        if (count($this->dimensions) == 0)
            throw new SystemException(SystemException::EX_INVALIDMETHODCALL, 'There must be at least 1 dimension');
        
        $url = "https://www.google.com/analytics/feeds/data?start-date=$startDate&end-date=$endDate&ids=ga:" . $this->profileId . "&prettyprint=true&alt=json";
        $url .= "&dimensions=" . implode(",", $this->dimensions);
        $url .= "&metrics=" . implode(",", $this->metrics);
        
        $jsonData = $this->doGet($url);
        $jsonData = str_replace('$', '', $jsonData);
        
        $jsonDecoded = json_decode($jsonData, true);
        
        if ($returnSignificantValuesOnly === false)
            return array(
                'entries' => $jsonDecoded,
                'aggregates' => null
            );
        else
            return array(
                'entries' => $jsonDecoded['feed']['entry'],
                'aggregates' => $jsonDecoded['feed']['dxpaggregates']
            );
    }
}
?>
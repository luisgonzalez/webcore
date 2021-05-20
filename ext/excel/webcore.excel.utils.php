<?php
/**
 * ExcelShared_Date
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_Date extends HelperBase
{
    /** constants */
    const CALENDAR_WINDOWS_1900 = 1900; //	Base date of 1st Jan 1900 = 1.0
    const CALENDAR_MAC_1904 = 1904; //	Base date of 2nd Jan 1904 = 1.0
    
    private static $ExcelBaseDate = self::CALENDAR_WINDOWS_1900;
    public static $dateTimeObjectType = 'DateTime';
    
    /**
     * Set the Excel calendar (Windows 1900 or Mac 1904)
     *
     * @param	 integer	$baseDate			Excel base date
     * @return	 boolean						Success or failure
     */
    public static function setExcelCalendar($baseDate)
    {
        if (($baseDate == self::CALENDAR_WINDOWS_1900) || ($baseDate == self::CALENDAR_MAC_1904))
        {
            self::$ExcelBaseDate = $baseDate;
            return True;
        }
        return False;
    } //	function setExcelCalendar()
    
    
    /**
     * Return the Excel calendar (Windows 1900 or Mac 1904)
     *
     * @return	 integer	$baseDate			Excel base date
     */
    public static function getExcelCalendar()
    {
        return self::$ExcelBaseDate;
    }
    
    /**
     * Convert a date from Excel to PHP
     *
     * @param	 long	 $dateValue		Excel date/time value
     * @return	 long					PHP serialized date/time
     */
    public static function ExcelToPHP($dateValue = 0)
    {
        if (self::$ExcelBaseDate == self::CALENDAR_WINDOWS_1900)
        {
            $myExcelBaseDate = 25569;
            //	Adjust for the spurious 29-Feb-1900 (Day 60)
            if ($dateValue < 60)
            {
                --$myExcelBaseDate;
            }
        }
        else
        {
            $myExcelBaseDate = 24107;
        }
        
        // Perform conversion
        if ($dateValue >= 1)
        {
            $utcDays     = $dateValue - $myExcelBaseDate;
            $returnValue = (integer) round($utcDays * 24 * 60 * 60);
        }
        else
        {
            $hours       = round($dateValue * 24);
            $mins        = round($dateValue * 24 * 60) - round($hours * 60);
            $secs        = round($dateValue * 24 * 60 * 60) - round($hours * 60 * 60) - round($mins * 60);
            $returnValue = (integer) mktime($hours, $mins, $secs);
        }
        
        // Return
        return $returnValue;
    } //	function ExcelToPHP()
    
    
    /**
     * Convert a date from Excel to a PHP Date/Time object
     *
     * @param	 long	 $dateValue		Excel date/time value
     * @return	 long					PHP date/time object
     */
    public static function ExcelToPHPObject($dateValue = 0)
    {
        $dateTime = self::ExcelToPHP($dateValue);
        $days     = floor($dateTime / 86400);
        $time     = round((($dateTime / 86400) - $days) * 86400);
        $hours    = round($time / 3600);
        $minutes  = round($time / 60) - ($hours * 60);
        $seconds  = round($time) - ($hours * 3600) - ($minutes * 60);
        $dateObj  = date_create('1-Jan-1970+' . $days . ' days');
        $dateObj->setTime($hours, $minutes, $seconds);
        return $dateObj;
    } //	function ExcelToPHPObject()
    
    
    /**
     * Convert a date from PHP to Excel
     *
     * @param	 mixed		$dateValue	PHP serialized date/time or date object
     * @return	 mixed					Excel date/time value
     *										or boolean False on failure
     */
    public static function PHPToExcel($dateValue = 0)
    {
        $saveTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $retValue = False;
        if ((is_object($dateValue)) && ($dateValue instanceof self::$dateTimeObjectType))
        {
            $retValue = self::FormattedPHPToExcel($dateValue->format('Y'), $dateValue->format('m'), $dateValue->format('d'), $dateValue->format('H'), $dateValue->format('i'), $dateValue->format('s'));
        }
        elseif (is_numeric($dateValue))
        {
            $retValue = self::FormattedPHPToExcel(date('Y', $dateValue), date('m', $dateValue), date('d', $dateValue), date('H', $dateValue), date('i', $dateValue), date('s', $dateValue));
        }
        date_default_timezone_set($saveTimeZone);
        
        return $retValue;
    } //	function PHPToExcel()
    
    
    /**
     * FormattedPHPToExcel
     *
     * @param	long	$year
     * @param	long	$month
     * @param	long	$day
     * @param	long	$hours
     * @param	long	$minutes
     * @param	long	$seconds
     * @return  long				Excel date/time value
     */
    public static function FormattedPHPToExcel($year, $month, $day, $hours = 0, $minutes = 0, $seconds = 0)
    {
        if (self::$ExcelBaseDate == self::CALENDAR_WINDOWS_1900)
        {
            //
            //	Fudge factor for the erroneous fact that the year 1900 is treated as a Leap Year in MS Excel
            //	This affects every date following 28th February 1900
            //
            $excel1900isLeapYear = True;
            if (($year == 1900) && ($month <= 2))
            {
                $excel1900isLeapYear = False;
            }
            $myExcelBaseDate = 2415020;
        }
        else
        {
            $myExcelBaseDate     = 2416481;
            $excel1900isLeapYear = False;
        }
        
        //	Julian base date Adjustment
        if ($month > 2)
        {
            $month = $month - 3;
        }
        else
        {
            $month = $month + 9;
            --$year;
        }
        
        //	Calculate the Julian Date, then subtract the Excel base date (JD 2415020 = 31-Dec-1899 Giving Excel Date of 0)
        $century   = substr($year, 0, 2);
        $decade    = substr($year, 2, 2);
        $excelDate = floor((146097 * $century) / 4) + floor((1461 * $decade) / 4) + floor((153 * $month + 2) / 5) + $day + 1721119 - $myExcelBaseDate + $excel1900isLeapYear;
        
        $excelTime = (($hours * 3600) + ($minutes * 60) + $seconds) / 86400;
        
        return (float) $excelDate + $excelTime;
    }
    
    
    /**
     * Is a given cell a date/time?
     *
     * @param	 ExcelCell	$pCell
     * @return	 boolean
     */
    public static function isDateTime($pCell)
    {
        return self::isDateTimeFormat($pCell->getParent()->getStyle($pCell->getCoordinate())->getNumberFormat());
    }
    
    
    /**
     * Is a given number format a date/time?
     *
     * @param	 ExcelStyleNumberFormat	$pFormat
     * @return	 boolean
     */
    public static function isDateTimeFormat(ExcelStyleNumberFormat $pFormat)
    {
        return self::isDateTimeFormatCode($pFormat->getFormatCode());
    } //	function isDateTimeFormat()
    
    
    private static $possibleDateFormatCharacters = 'ymdHis';
    
    /**
     * Is a given number format code a date/time?
     *
     * @param	 string	$pFormatCode
     * @return	 boolean
     */
    public static function isDateTimeFormatCode($pFormatCode = '')
    {
        // Switch on formatcode
        switch ($pFormatCode)
        {
            case ExcelStyleNumberFormat::FORMAT_DATE_YYYYMMDD:
            case ExcelStyleNumberFormat::FORMAT_DATE_YYYYMMDD2:
            case ExcelStyleNumberFormat::FORMAT_DATE_DDMMYYYY:
            case ExcelStyleNumberFormat::FORMAT_DATE_DMYSLASH:
            case ExcelStyleNumberFormat::FORMAT_DATE_DMYMINUS:
            case ExcelStyleNumberFormat::FORMAT_DATE_DMMINUS:
            case ExcelStyleNumberFormat::FORMAT_DATE_MYMINUS:
            case ExcelStyleNumberFormat::FORMAT_DATE_DATETIME:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME1:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME2:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME3:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME4:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME5:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME6:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME7:
            case ExcelStyleNumberFormat::FORMAT_DATE_TIME8:
            case ExcelStyleNumberFormat::FORMAT_DATE_YYYYMMDDSLASH:
            case ExcelStyleNumberFormat::FORMAT_DATE_XLSX14:
            case ExcelStyleNumberFormat::FORMAT_DATE_XLSX15:
            case ExcelStyleNumberFormat::FORMAT_DATE_XLSX16:
            case ExcelStyleNumberFormat::FORMAT_DATE_XLSX17:
            case ExcelStyleNumberFormat::FORMAT_DATE_XLSX22:
                return true;
        }
        
        // Try checking for any of the date formatting characters that don't appear within square braces
        if (preg_match('/(^|\])[^\[]*[' . self::$possibleDateFormatCharacters . ']/i', $pFormatCode))
        {
            return true;
        }
        
        // No date...
        return false;
    } //	function isDateTimeFormatCode()
}

/**
 * ExcelShared_String
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_String extends StringHelper
{
    /**
     * Control characters array
     *
     * @var string[]
     */
    private static $_controlCharacters = array();
    
    /**
     * Build control characters array
     */
    private static function _buildControlCharacters()
    {
        for ($i = 0; $i <= 19; ++$i)
        {
            if ($i != 9 && $i != 10 && $i != 13)
            {
                $find                            = '_x' . sprintf('%04s', strtoupper(dechex($i))) . '_';
                $replace                         = chr($i);
                self::$_controlCharacters[$find] = $replace;
            }
        }
    }
    
    /**
     * Convert from OpenXML escaped control character to PHP control character
     *
     * Excel 2007 team:
     * ----------------
     * That's correct, control characters are stored directly in the shared-strings table.
     * We do encode characters that cannot be represented in XML using the following escape sequence:
     * _xHHHH_ where H represents a hexadecimal character in the character's value...
     * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
     * element or in the shared string <t> element.
     *
     * @param 	string	$value	Value to unescape
     * @return 	string
     */
    public static function ControlCharacterOOXML2PHP($value = '')
    {
        if (empty(self::$_controlCharacters))
        {
            self::_buildControlCharacters();
        }
        
        return str_replace(array_keys(self::$_controlCharacters), array_values(self::$_controlCharacters), $value);
    }
    
    /**
     * Convert from PHP control character to OpenXML escaped control character
     *
     * Excel 2007 team:
     * ----------------
     * That's correct, control characters are stored directly in the shared-strings table.
     * We do encode characters that cannot be represented in XML using the following escape sequence:
     * _xHHHH_ where H represents a hexadecimal character in the character's value...
     * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
     * element or in the shared string <t> element.
     *
     * @param 	string	$value	Value to escape
     * @return 	string
     */
    public static function ControlCharacterPHP2OOXML($value = '')
    {
        if (empty(self::$_controlCharacters))
        {
            self::_buildControlCharacters();
        }
        
        return str_replace(array_values(self::$_controlCharacters), array_keys(self::$_controlCharacters), $value);
    }
    
    /**
     * Formats a numeric value as a string for output in various output writers
     *
     * @param mixed $value
     * @return string
     */
    public static function FormatNumber($value)
    {
        return number_format($value, 2, '.', '');
    }
    
    /**
     * Converts a UTF-8 string into BIFF8 Unicode string data (8-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
     * although this will give wrong results for non-ASCII strings.
     *
     * @param string $value UTF-8 encoded string
     * @return string
     */
    public static function UTF8toBIFF8UnicodeShort($value)
    {
        // character count
        $ln = self::countCharacters($value, 'UTF-8');
        
        // option flags
        $opt = (self::getIsMbstringEnabled() || self::getIsIconvEnabled()) ? 0x0001 : 0x0000;
        
        // characters
        $chars = self::convertEncoding($value, 'UTF-16LE', 'UTF-8');
        
        $data = pack('CC', $ln, $opt) . $chars;
        return $data;
    }
    
    /**
     * Converts a UTF-8 string into BIFF8 Unicode string data (16-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
     * although this will give wrong results for non-ASCII strings.
     *
     * @param string $value UTF-8 encoded string
     * @return string
     */
    public static function UTF8toBIFF8UnicodeLong($value)
    {
        // character count
        $ln = self::countCharacters($value, 'UTF-8');
        
        // option flags
        $opt = (self::getIsMbstringEnabled() || self::getIsIconvEnabled()) ? 0x0001 : 0x0000;
        
        // characters
        $chars = self::convertEncoding($value, 'UTF-16LE', 'UTF-8');
        
        $data = pack('vC', $ln, $opt) . $chars;
        return $data;
    }
}

/**
 * ExcelShared_Drawing
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_Drawing extends HelperBase
{
    /**
     * Convert pixels to EMU
     *
     * @param 	int $pValue	Value in pixels
     * @return 	int			Value in EMU
     */
    public static function pixelsToEMU($pValue = 0)
    {
        return round($pValue * 9525);
    }
    
    /**
     * Convert EMU to pixels
     *
     * @param 	int $pValue	Value in EMU
     * @return 	int			Value in pixels
     */
    public static function EMUToPixels($pValue = 0)
    {
        if ($pValue != 0)
        {
            return round($pValue / 9525);
        }
        else
        {
            return 0;
        }
    }
    
    /**
     * Convert pixels to cell dimension
     *
     * @param 	int $pValue	Value in pixels
     * @return 	int			Value in cell dimension
     */
    public static function pixelsToCellDimension($pValue = 0)
    {
        return $pValue / 12;
    }
    
    /**
     * Convert cell width to pixels
     *
     * @param 	int $pValue	Value in cell dimension
     * @return 	int			Value in pixels
     */
    public static function cellDimensionToPixels($pValue = 0)
    {
        if ($pValue != 0)
        {
            return $pValue * 12;
        }
        else
        {
            return 0;
        }
    }
    
    /**
     * Convert pixels to points
     *
     * @param 	int $pValue	Value in pixels
     * @return 	int			Value in points
     */
    public static function pixelsToPoints($pValue = 0)
    {
        return $pValue * 0.67777777;
    }
    
    /**
     * Convert points width to pixels
     *
     * @param 	int $pValue	Value in points
     * @return 	int			Value in pixels
     */
    public static function pointsToPixels($pValue = 0)
    {
        if ($pValue != 0)
        {
            return $pValue * 1.333333333;
        }
        else
        {
            return 0;
        }
    }
    
    /**
     * Convert degrees to angle
     *
     * @param 	int $pValue	Degrees
     * @return 	int			Angle
     */
    public static function degreesToAngle($pValue = 0)
    {
        return (int) round($pValue * 60000);
    }
    
    /**
     * Convert angle to degrees
     *
     * @param 	int $pValue	Angle
     * @return 	int			Degrees
     */
    public static function angleToDegrees($pValue = 0)
    {
        if ($pValue != 0)
        {
            return round($pValue / 60000);
        }
        else
        {
            return 0;
        }
    }
}

/**
 * ExcelShared_File
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_File extends HelperBase
{
    /**
     * Verify if a file exists
     *
     * @param 	string	$pFilename	Filename
     * @return bool
     */
    public static function file_exists($pFilename)
    {
        // Sick construction, but it seems that
        // file_exists returns strange values when
        // doing the original file_exists on ZIP archives...
        if (strtolower(substr($pFilename, 0, 3)) == 'zip')
        {
            // Open ZIP file and verify if the file exists
            $zipFile     = substr($pFilename, 6, strpos($pFilename, '#') - 6);
            $archiveFile = substr($pFilename, strpos($pFilename, '#') + 1);
            
            $zip = new ZipArchive();
            if ($zip->open($zipFile) === true)
            {
                $returnValue = ($zip->getFromName($archiveFile) !== false);
                $zip->close();
                return $returnValue;
            }
            else
            {
                return false;
            }
        }
        else
        {
            // Regular file_exists
            return file_exists($pFilename);
        }
    }
    
    /**
     * Returns canonicalized absolute pathname, also for ZIP archives
     *
     * @param string $pFilename
     * @return string
     */
    public static function realpath($pFilename)
    {
        // Returnvalue
        $returnValue = '';
        
        // Try using realpath()
        $returnValue = realpath($pFilename);
        
        // Found something?
        if ($returnValue == '' || is_null($returnValue))
        {
            $pathArray = split('/', $pFilename);
            while (in_array('..', $pathArray) && $pathArray[0] != '..')
            {
                for ($i = 0; $i < count($pathArray); ++$i)
                {
                    if ($pathArray[$i] == '..' && $i > 0)
                    {
                        unset($pathArray[$i]);
                        unset($pathArray[$i - 1]);
                        break;
                    }
                }
            }
            $returnValue = implode('/', $pathArray);
        }
        
        // Return
        return $returnValue;
    }
}

/**
 * ExcelSharedFont
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelSharedFont extends HelperBase
{
    /* Methods for resolving autosize value */
	const AUTOSIZE_METHOD_APPROX	= 'approx';
	const AUTOSIZE_METHOD_EXACT		= 'exact';

	/** Character set codes used by BIFF5-8 in Font records */
	const CHARSET_ANSI_LATIN				= 0x00;
	const CHARSET_SYSTEM_DEFAULT			= 0x01;
	const CHARSET_SYMBOL					= 0x02;
	const CHARSET_APPLE_ROMAN				= 0x4D;
	const CHARSET_ANSI_JAPANESE_SHIFTJIS	= 0x80;
	const CHARSET_ANSI_KOREAN_HANGUL		= 0x81;
	const CHARSET_ANSI_KOREAN_JOHAB			= 0x82;
	const CHARSET_ANSI_CHINESE_SIMIPLIFIED	= 0x86;
	const CHARSET_ANSI_CHINESE_TRADITIONAL	= 0x88;
	const CHARSET_ANSI_GREEK				= 0xA1;
	const CHARSET_ANSI_TURKISH				= 0xA2;
	const CHARSET_ANSI_VIETNAMESE			= 0xA3;
	const CHARSET_ANSI_HEBREW				= 0xB1;
	const CHARSET_ANSI_ARABIC				= 0xB2;
	const CHARSET_ANSI_BALTIC				= 0xBA;
	const CHARSET_ANSI_CYRILLIC				= 0xCC;
	const CHARSET_ANSI_THAI					= 0xDE;
	const CHARSET_ANSI_LATIN_II				= 0xEE;
	const CHARSET_OEM_LATIN_I				= 0xFF;
	
	//  XXX: Constants created!
	/** Font filenames */
	const ARIAL								= 'arial.ttf';
	const ARIAL_BOLD						= 'arialbd.ttf';
	const ARIAL_ITALIC						= 'ariali.ttf';
	const ARIAL_BOLD_ITALIC					= 'arialbi.ttf';

	const CALIBRI							= 'CALIBRI.TTF';
	const CALIBRI_BOLD						= 'CALIBRIB.TTF';
	const CALIBRI_ITALIC					= 'CALIBRII.TTF';
	const CALIBRI_BOLD_ITALIC				= 'CALIBRIZ.TTF';

	const COMIC_SANS_MS						= 'comic.ttf';
	const COMIC_SANS_MS_BOLD				= 'comicbd.ttf';

	const COURIER_NEW						= 'cour.ttf';
	const COURIER_NEW_BOLD					= 'courbd.ttf';
	const COURIER_NEW_ITALIC				= 'couri.ttf';
	const COURIER_NEW_BOLD_ITALIC			= 'courbi.ttf';

	const GEORGIA							= 'georgia.ttf';
	const GEORGIA_BOLD						= 'georgiab.ttf';
	const GEORGIA_ITALIC					= 'georgiai.ttf';
	const GEORGIA_BOLD_ITALIC				= 'georgiaz.ttf';

	const IMPACT							= 'impact.ttf';

	const LIBERATION_SANS					= 'LiberationSans-Regular.ttf';
	const LIBERATION_SANS_BOLD				= 'LiberationSans-Bold.ttf';
	const LIBERATION_SANS_ITALIC			= 'LiberationSans-Italic.ttf';
	const LIBERATION_SANS_BOLD_ITALIC		= 'LiberationSans-BoldItalic.ttf';

	const LUCIDA_CONSOLE					= 'lucon.ttf';
	const LUCIDA_SANS_UNICODE				= 'l_10646.ttf';

	const MICROSOFT_SANS_SERIF				= 'micross.ttf';

	const PALATINO_LINOTYPE					= 'pala.ttf';
	const PALATINO_LINOTYPE_BOLD			= 'palab.ttf';
	const PALATINO_LINOTYPE_ITALIC			= 'palai.ttf';
	const PALATINO_LINOTYPE_BOLD_ITALIC		= 'palabi.ttf';

	const SYMBOL							= 'symbol.ttf';

	const TAHOMA							= 'tahoma.ttf';
	const TAHOMA_BOLD						= 'tahomabd.ttf';

	const TIMES_NEW_ROMAN					= 'times.ttf';
	const TIMES_NEW_ROMAN_BOLD				= 'timesbd.ttf';
	const TIMES_NEW_ROMAN_ITALIC			= 'timesi.ttf';
	const TIMES_NEW_ROMAN_BOLD_ITALIC		= 'timesbi.ttf';

	const TREBUCHET_MS						= 'trebuc.ttf';
	const TREBUCHET_MS_BOLD					= 'trebucbd.ttf';
	const TREBUCHET_MS_ITALIC				= 'trebucit.ttf';
	const TREBUCHET_MS_BOLD_ITALIC			= 'trebucbi.ttf';

	const VERDANA							= 'verdana.ttf';
	const VERDANA_BOLD						= 'verdanab.ttf';
	const VERDANA_ITALIC					= 'verdanai.ttf';
	const VERDANA_BOLD_ITALIC				= 'verdanaz.ttf';

	/**
	 * AutoSize method
	 *
	 * @var string
	 */
	private static $autoSizeMethod = self::AUTOSIZE_METHOD_APPROX;

	/**
	 * Path to folder containing TrueType font .ttf files
	 *
	 * @var string
	 */
	private static $trueTypeFontPath = null;

	/**
	 * How wide is a default column for a given default font and size?
	 * Empirical data found by inspecting real Excel files and reading off the pixel width
	 * in Microsoft Office Excel 2007.
	 *
	 * @var array
	 */
	public static $defaultColumnWidths = array(
		'Arial' => array(
			 1 => array('px' => 24, 'width' => 12.00000000),
			 2 => array('px' => 24, 'width' => 12.00000000),
			 3 => array('px' => 32, 'width' => 10.66406250),
			 4 => array('px' => 32, 'width' => 10.66406250),
			 5 => array('px' => 40, 'width' => 10.00000000),
			 6 => array('px' => 48, 'width' =>  9.59765625),
			 7 => array('px' => 48, 'width' =>  9.59765625),
			 8 => array('px' => 56, 'width' =>  9.33203125),
			 9 => array('px' => 64, 'width' =>  9.14062500),
			10 => array('px' => 64, 'width' =>  9.14062500),
		),
		'Calibri' => array(
			 1 => array('px' => 24, 'width' => 12.00000000),
			 2 => array('px' => 24, 'width' => 12.00000000),
			 3 => array('px' => 32, 'width' => 10.66406250),
			 4 => array('px' => 32, 'width' => 10.66406250),
			 5 => array('px' => 40, 'width' => 10.00000000),
			 6 => array('px' => 48, 'width' =>  9.59765625),
			 7 => array('px' => 48, 'width' =>  9.59765625),
			 8 => array('px' => 56, 'width' =>  9.33203125),
			 9 => array('px' => 56, 'width' =>  9.33203125),
			10 => array('px' => 64, 'width' =>  9.14062500),
			11 => array('px' => 64, 'width' =>  9.14062500),
		),
		'Verdana' => array(
			 1 => array('px' => 24, 'width' => 12.00000000),
			 2 => array('px' => 24, 'width' => 12.00000000),
			 3 => array('px' => 32, 'width' => 10.66406250),
			 4 => array('px' => 32, 'width' => 10.66406250),
			 5 => array('px' => 40, 'width' => 10.00000000),
			 6 => array('px' => 48, 'width' =>  9.59765625),
			 7 => array('px' => 48, 'width' =>  9.59765625),
			 8 => array('px' => 64, 'width' =>  9.14062500),
			 9 => array('px' => 72, 'width' =>  9.00000000),
			10 => array('px' => 72, 'width' =>  9.00000000),
		),
	);

	/**
	 * Set autoSize method
	 *
	 * @param string $pValue
	 */
	public static function setAutoSizeMethod($pValue = 'approx')
	{
		self::$autoSizeMethod = $pValue;
	}

	/**
	 * Get autoSize method
	 *
	 * @return string
	 */
	public static function getAutoSizeMethod()
	{
		return self::$autoSizeMethod;
	}

	/**
	 * Set the path to the folder containing .ttf files. There should be a trailing slash.
	 * Typical locations on variout some platforms:
	 *	<ul>
	 *		<li>C:/Windows/Fonts/</li>
	 *		<li>/usr/share/fonts/truetype/</li>
	 *		<li>~/.fonts/</li>
	 *	</ul>
	 *
	 * @param string $pValue
	 */
	public static function setTrueTypeFontPath($pValue = '')
	{
		self::$trueTypeFontPath = $pValue;
	}
	
	/**
	 * Get the path to the folder containing .ttf files.
	 *
	 * @return string
	 */
	public static function getTrueTypeFontPath()
	{
		return self::$trueTypeFontPath;
	}
	
	/**
	 * Calculate an (approximate) OpenXML column width, based on font size and text contained
	 */
	public static function calculateColumnWidth($font, $columnText = '', $rotation = 0, $defaultFont = null) {

		// If it is rich text, use plain text
		if ($columnText instanceof ExcelRichText) {
			$columnText = $columnText->getPlainText();
		}

		// Only measure the part before the first newline character (is always "\n")
		if (strpos($columnText, "\n") !== false) {
			$columnText = substr($columnText, 0, strpos($columnText, "\n"));
		}

		// Try to get the exact text width in pixels
		try {
			// If autosize method is set to 'approx', use approximation
			if (self::$autoSizeMethod == self::AUTOSIZE_METHOD_APPROX) {
				throw new Exception('AutoSize method is set to approx');
			}

			// Width of text in pixels excl. padding
			$columnWidth = self::getTextWidthPixelsExact($columnText, $font, $rotation);

			// Excel adds some padding, use 1.07 of the width of an 'n' glyph
			$columnWidth += ceil(self::getTextWidthPixelsExact('0', $font, 0) * 1.07); // pixels incl. padding

		} catch (Exception $e) {
			// Width of text in pixels excl. padding, approximation
			$columnWidth = self::getTextWidthPixelsApprox($columnText, $font, $rotation);

			// Excel adds some padding, just use approx width of 'n' glyph
			$columnWidth += self::getTextWidthPixelsApprox('n', $font, 0);
		}

		// Convert from pixel width to column width
		$columnWidth = ExcelShared_Drawing::pixelsToCellDimension($columnWidth, $defaultFont);

		// Return
		return round($columnWidth, 6);
	}

	/**
	 * Get GD text width in pixels for a string of text in a certain font at a certain rotation angle
	 *
	 * @param string $text
	 * @param ExcelStyleFont
	 * @param int $rotation
	 * @return int
	 * @throws Exception
	 */
	public static function getTextWidthPixelsExact($text, ExcelStyleFont $font, $rotation = 0) {
		if (!function_exists('imagettfbbox')) {
			throw new Exception('GD library needs to be enabled');
		}

		// font size should really be supplied in pixels in GD2,
		// but since GD2 seems to assume 72dpi, pixels and points are the same
		$fontFile = self::getTrueTypeFontFileFromFont($font);
		$textBox = imagettfbbox($font->getSize(), $rotation, $fontFile, $text);

		// Get corners positions
		$lowerLeftCornerX  = $textBox[0];
		$lowerLeftCornerY  = $textBox[1];
		$lowerRightCornerX = $textBox[2];
		$lowerRightCornerY = $textBox[3];
		$upperRightCornerX = $textBox[4];
		$upperRightCornerY = $textBox[5];
		$upperLeftCornerX  = $textBox[6];
		$upperLeftCornerY  = $textBox[7];
		
		// Consider the rotation when calculating the width
		$textWidth = max($lowerRightCornerX - $upperLeftCornerX, $upperRightCornerX - $lowerLeftCornerX);

		return $textWidth;
	}

	/**
	 * Get approximate width in pixels for a string of text in a certain font at a certain rotation angle
	 *
	 * @param string $columnText
	 * @param ExcelStyleFont $font
	 * @param int $rotation
	 * @return int Text width in pixels (no padding added)
	 */
	public static function getTextWidthPixelsApprox($columnText, $font = null, $rotation = 0)
	{
		$fontName = $font->getName();
		$fontSize = $font->getSize();

		// Calculate column width in pixels. We assume fixed glyph width. Result varies with font name and size.
		switch ($fontName) {
			case 'Calibri':
				// value 8.26 was found via interpolation by inspecting real Excel files with Calibri 11 font.
				$columnWidth = (int) (8.26 * ExcelShared_String::CountCharacters($columnText));
				$columnWidth = $columnWidth * $fontSize / 11; // extrapolate from font size
				break;

			case 'Arial':
				// value 7 was found via interpolation by inspecting real Excel files with Arial 10 font.
				$columnWidth = (int) (7 * ExcelShared_String::CountCharacters($columnText));
				$columnWidth = $columnWidth * $fontSize / 10; // extrapolate from font size
				break;

			case 'Verdana':
				// value 8 was found via interpolation by inspecting real Excel files with Verdana 10 font.
				$columnWidth = (int) (8 * ExcelShared_String::CountCharacters($columnText));
				$columnWidth = $columnWidth * $fontSize / 10; // extrapolate from font size
				break;

			default:
				// just assume Calibri
				$columnWidth = (int) (8.26 * ExcelShared_String::CountCharacters($columnText));
				$columnWidth = $columnWidth * $fontSize / 11; // extrapolate from font size
				break;
		}

		// Calculate approximate rotated column width
		if ($rotation !== 0) {
			if ($rotation == -165) {
				// stacked text
				$columnWidth = 4; // approximation
			} else {
				// rotated text
				$columnWidth = $columnWidth * cos(deg2rad($rotation))
								+ $fontSize * abs(sin(deg2rad($rotation))) / 5; // approximation
			}
		}

		// pixel width is an integer
		$columnWidth = (int) $columnWidth;
		return $columnWidth;
	}

	/**
	 * Calculate an (approximate) pixel size, based on a font points size
	 *
	 * @param 	int		$fontSizeInPoints	Font size (in points)
	 * @return 	int		Font size (in pixels)
	 */
	public static function fontSizeToPixels($fontSizeInPoints = 11) {
		return (int) ((4 / 3) * $fontSizeInPoints);
	}
	
	/**
	 * Calculate an (approximate) pixel size, based on inch size
	 *
	 * @param 	int		$sizeInInch	Font size (in inch)
	 * @return 	int		Size (in pixels)
	 */
	public static function inchSizeToPixels($sizeInInch = 1) {
		return ($sizeInInch * 96);
	}
	
	/**
	 * Calculate an (approximate) pixel size, based on centimeter size
	 *
	 * @param 	int		$sizeInCm	Font size (in centimeters)
	 * @return 	int		Size (in pixels)
	 */
	public static function centimeterSizeToPixels($sizeInCm = 1) {
		return ($sizeInCm * 37.795275591);
	}

	/**
	 * Returns the font path given the font
	 *
	 * @param ExcelStyleFont
	 * @return string Path to TrueType font file
	 */
	public static function getTrueTypeFontFileFromFont($font) {
		if (!file_exists(self::$trueTypeFontPath) || !is_dir(self::$trueTypeFontPath)) {
			throw new Exception('Valid directory to TrueType Font files not specified');
		}

		$name		= $font->getName();
		$bold		= $font->getBold();
		$italic		= $font->getItalic();

		// Check if we can map font to true type font file
		switch ($name) {
			case 'Arial':
				$fontFile = (
					$bold ? ($italic ? self::ARIAL_BOLD_ITALIC : self::ARIAL_BOLD) 
						  : ($italic ? self::ARIAL_ITALIC : self::ARIAL)
				);
				break;

			case 'Calibri':
				$fontFile = (
					$bold ? ($italic ? self::CALIBRI_BOLD_ITALIC : self::CALIBRI_BOLD) 
						  : ($italic ? self::CALIBRI_ITALIC : self::CALIBRI)
				);
				break;

			case 'Courier New':
				$fontFile = (
					$bold ? ($italic ? self::COURIER_NEW_BOLD_ITALIC : self::COURIER_NEW_BOLD) 
						  : ($italic ? self::COURIER_NEW_ITALIC : self::COURIER_NEW)
				);
				break;

			case 'Comic Sans MS':
				$fontFile = (
					$bold ? self::COMIC_SANS_MS_BOLD : self::COMIC_SANS_MS 
				);
				break;

			case 'Georgia':
				$fontFile = (
					$bold ? ($italic ? self::GEORGIA_BOLD_ITALIC : self::GEORGIA_BOLD) 
						  : ($italic ? self::GEORGIA_ITALIC : self::GEORGIA)
				);
				break;

			case 'Impact':
				$fontFile = self::IMPACT;
				break;

			case 'Liberation Sans':
				$fontFile = (
					$bold ? ($italic ? self::LIBERATION_SANS_BOLD_ITALIC : self::LIBERATION_SANS_BOLD) 
						  : ($italic ? self::LIBERATION_SANS_ITALIC : self::LIBERATION_SANS)
				);
				break;

			case 'Lucida Console':
				$fontFile = self::LUCIDA_CONSOLE;
				break;

			case 'Lucida Sans Unicode':
				$fontFile = self::LUCIDA_SANS_UNICODE;
				break;

			case 'Microsoft Sans Serif':
				$fontFile = self::MICROSOFT_SANS_SERIF;
				break;

			case 'Palatino Linotype':
				$fontFile = (
					$bold ? ($italic ? self::PALATINO_LINOTYPE_BOLD_ITALIC : self::PALATINO_LINOTYPE_BOLD) 
						  : ($italic ? self::PALATINO_LINOTYPE_ITALIC : self::PALATINO_LINOTYPE)
				);
				break;

			case 'Symbol':
				$fontFile = self::SYMBOL;
				break;

			case 'Tahoma':
				$fontFile = (
					$bold ? self::TAHOMA_BOLD : self::TAHOMA 
				);
				break;

			case 'Times New Roman':
				$fontFile = (
					$bold ? ($italic ? self::TIMES_NEW_ROMAN_BOLD_ITALIC : self::TIMES_NEW_ROMAN_BOLD) 
						  : ($italic ? self::TIMES_NEW_ROMAN_ITALIC : self::TIMES_NEW_ROMAN)
				);
				break;

			case 'Trebuchet MS':
				$fontFile = (
					$bold ? ($italic ? self::TREBUCHET_MS_BOLD_ITALIC : self::TREBUCHET_MS_BOLD) 
						  : ($italic ? self::TREBUCHET_MS_ITALIC : self::TREBUCHET_MS)
				);
				break;

			case 'Verdana':
				$fontFile = (
					$bold ? ($italic ? self::VERDANA_BOLD_ITALIC : self::VERDANA_BOLD) 
						  : ($italic ? self::VERDANA_ITALIC : self::VERDANA)
				);
				break;

			default:
				throw new Exception('Unknown font name "'. $name .'". Cannot map to TrueType font file');
				break;
		}

		$fontFile = self::$trueTypeFontPath . $fontFile;

		// Check if file actually exists
		if (!file_exists($fontFile)) {
			throw New Exception('TrueType Font file not found');
		}

		return $fontFile;
	}

	/**
	 * Returns the associated charset for the font name.
	 *
	 * @param string $name Font name
	 * @return int Character set code
	 */
	public static function getCharsetFromFontName($name)
	{
		switch ($name) {
			// Add more cases. Check FONT records in real Excel files.
			case 'EucrosiaUPC':		return self::CHARSET_ANSI_THAI;
			case 'Wingdings':		return self::CHARSET_SYMBOL;
			case 'Wingdings 2':		return self::CHARSET_SYMBOL;
			case 'Wingdings 3':		return self::CHARSET_SYMBOL;
			default:				return self::CHARSET_ANSI_LATIN;
		}
	}

	/**
	 * Get the effective column width for columns without a column dimension or column with width -1
	 * For example, for Calibri 11 this is 9.140625 (64 px)
	 *
	 * @param ExcelStyleFont $font The workbooks default font
	 * @param boolean $pPixels true = return column width in pixels, false = return in OOXML units
	 * @return mixed Column width
	 */
	public static function getDefaultColumnWidthByFont($font, $pPixels = false)
	{
		if (isset(self::$defaultColumnWidths[$font->getName()][$font->getSize()])) {
			// Exact width can be determined
			$columnWidth = $pPixels ?
				self::$defaultColumnWidths[$font->getName()][$font->getSize()]['px']
					: self::$defaultColumnWidths[$font->getName()][$font->getSize()]['width'];

		} else {
			// We don't have data for this particular font and size, use approximation by
			// extrapolating from Calibri 11
			$columnWidth = $pPixels ?
				self::$defaultColumnWidths['Calibri'][11]['px']
					: self::$defaultColumnWidths['Calibri'][11]['width'];
			$columnWidth = $columnWidth * $font->getSize() / 11;

			// Round pixels to closest integer
			if ($pPixels) {
				$columnWidth = (int) round($columnWidth);
			}
		}

		return $columnWidth;
	}
	
	/**
	 * Get the effective row height for rows without a row dimension or rows with height -1
	 * For example, for Calibri 11 this is 15 points
	 *
	 * @param ExcelStyleFont $font The workbooks default font
	 * @return float Row height in points
	 */
	public static function getDefaultRowHeightByFont($font)
	{
		switch ($font->getName()) {
			case 'Arial':
				switch ($font->getSize()) {
					case 10:
						// inspection of Arial 10 workbook says 12.75pt ~17px
						$rowHeight = 12.75;
						break;

					case 9:
						// inspection of Arial 9 workbook says 12.00pt ~16px
						$rowHeight = 12;
						break;

					case 8:
						// inspection of Arial 8 workbook says 11.25pt ~15px
						$rowHeight = 11.25;
						break;

					case 7:
						// inspection of Arial 7 workbook says 9.00pt ~12px
						$rowHeight = 9;
						break;

					case 6:
					case 5:
						// inspection of Arial 5,6 workbook says 8.25pt ~11px
						$rowHeight = 8.25;
						break;

					case 4:
						// inspection of Arial 4 workbook says 6.75pt ~9px
						$rowHeight = 6.75;
						break;

					case 3:
						// inspection of Arial 3 workbook says 6.00pt ~8px
						$rowHeight = 6;
						break;

					case 2:
					case 1:
						// inspection of Arial 1,2 workbook says 5.25pt ~7px
						$rowHeight = 5.25;
						break;

					default:
						// use Arial 10 workbook as an approximation, extrapolation
						$rowHeight = 12.75 * $font->getSize() / 10;
						break;
				}
				break;

			case 'Calibri':
				switch ($font->getSize()) {
					case 11:
						// inspection of Calibri 11 workbook says 15.00pt ~20px
						$rowHeight = 15;
						break;

					case 10:
						// inspection of Calibri 10 workbook says 12.75pt ~17px
						$rowHeight = 12.75;
						break;

					case 9:
						// inspection of Calibri 9 workbook says 12.00pt ~16px
						$rowHeight = 12;
						break;

					case 8:
						// inspection of Calibri 8 workbook says 11.25pt ~15px
						$rowHeight = 11.25;
						break;

					case 7:
						// inspection of Calibri 7 workbook says 9.00pt ~12px
						$rowHeight = 9;
						break;

					case 6:
					case 5:
						// inspection of Calibri 5,6 workbook says 8.25pt ~11px
						$rowHeight = 8.25;
						break;

					case 4:
						// inspection of Calibri 4 workbook says 6.75pt ~9px
						$rowHeight = 6.75;
						break;

					case 3:
						// inspection of Calibri 3 workbook says 6.00pt ~8px
						$rowHeight = 6.00;
						break;

					case 2:
					case 1:
						// inspection of Calibri 1,2 workbook says 5.25pt ~7px
						$rowHeight = 5.25;
						break;

					default:
						// use Calibri 11 workbook as an approximation, extrapolation
						$rowHeight = 15 * $font->getSize() / 11;
						break;
				}
				break;

			case 'Verdana':
				switch ($font->getSize()) {
					case 10:
						// inspection of Verdana 10 workbook says 12.75pt ~17px
						$rowHeight = 12.75;
						break;

					case 9:
						// inspection of Verdana 9 workbook says 11.25pt ~15px
						$rowHeight = 11.25;
						break;

					case 8:
						// inspection of Verdana 8 workbook says 10.50pt ~14px
						$rowHeight = 10.50;
						break;

					case 7:
						// inspection of Verdana 7 workbook says 9.00pt ~12px
						$rowHeight = 9.00;
						break;

					case 6:
					case 5:
						// inspection of Verdana 5,6 workbook says 8.25pt ~11px
						$rowHeight = 8.25;
						break;

					case 4:
						// inspection of Verdana 4 workbook says 6.75pt ~9px
						$rowHeight = 6.75;
						break;

					case 3:
						// inspection of Verdana 3 workbook says 6.00pt ~8px
						$rowHeight = 6;
						break;

					case 2:
					case 1:
						// inspection of Verdana 1,2 workbook says 5.25pt ~7px
						$rowHeight = 5.25;
						break;

					default:
						// use Verdana 10 workbook as an approximation, extrapolation
						$rowHeight = 12.75 * $font->getSize() / 10;
						break;
				}
				break;

			default:
				// just use Calibri as an approximation
				$rowHeight = 15 * $font->getSize() / 11;
				break;
		}

		return $rowHeight;
	}
}

if (!defined('DATE_W3C'))
    define('DATE_W3C', 'Y-m-d\TH:i:sP');

/**
 * ExcelXmlWriter
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelXmlWriter extends ObjectBase
{
    /** Temporary storage method */
    const STORAGE_MEMORY = 1;
    const STORAGE_DISK = 2;
    
    /**
     * Internal XMLWriter
     *
     * @var XMLWriter
     */
    private $_xmlWriter;
    
    /**
     * Temporary filename
     *
     * @var string
     */
    private $_tempFileName = '';
    
    /**
     * Create a new ExcelXmlWriter instance
     *
     * @param int		$pTemporaryStorage			Temporary storage location
     * @param string	$pTemporaryStorageFolder	Temporary storage folder
     */
    public function __construct($pTemporaryStorage = self::STORAGE_MEMORY, $pTemporaryStorageFolder = './')
    {
        // Create internal XMLWriter
        $this->_xmlWriter = new XMLWriter();
        
        // Open temporary storage
        if ($pTemporaryStorage == self::STORAGE_MEMORY)
        {
            $this->_xmlWriter->openMemory();
        }
        else
        {
            // Create temporary filename
            $this->_tempFileName = @tempnam($pTemporaryStorageFolder, 'xml');
            
            // Open storage
            if ($this->_xmlWriter->openUri($this->_tempFileName) === false)
            {
                // Fallback to memory...
                $this->_xmlWriter->openMemory();
            }
        }
        
        // Set default values
        $this->_xmlWriter->setIndent(true);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        // Desctruct XMLWriter
        unset($this->_xmlWriter);
        
        // Unlink temporary files
        if ($this->_tempFileName != '')
        {
            @unlink($this->_tempFileName);
        }
    }
    
    /**
     * Get written data
     *
     * @return $data
     */
    public function getData()
    {
        if ($this->_tempFileName == '')
        {
            return $this->_xmlWriter->outputMemory(true);
        }
        else
        {
            $this->_xmlWriter->flush();
            return file_get_contents($this->_tempFileName);
        }
    }
    
    /**
     * Catch function calls (and pass them to internal XMLWriter)
     *
     * @param unknown_type $function
     * @param unknown_type $args
     */
    public function __call($function, $args)
    {
        try
        {
            @call_user_func_array(array(
                $this->_xmlWriter,
                $function
            ), $args);
        }
        catch (Exception $ex)
        {
            // Do nothing!
        }
    }
    
    /**
     * Fallback method for writeRaw, introduced in PHP 5.2
     *
     * @param string $text
     * @return string
     */
    public function writeRaw($text)
    {
        if (isset($this->_xmlWriter) && is_object($this->_xmlWriter) && (method_exists($this->_xmlWriter, 'writeRaw')))
        {
            return $this->_xmlWriter->writeRaw($text);
        }
        
        return $this->text($text);
    }
}

/**
 * ExcelShared_ZipStreamWrapper
 *
 * @package    WebCore
 * @subpackage Excel
 */
class ExcelShared_ZipStreamWrapper extends ObjectBase
{
    /**
     * Internal ZipAcrhive
     *
     * @var ZipAcrhive
     */
    private $_archive;
    private $_fileNameInArchive = '';
    private $_position = 0;
    private $_data = '';
    
    /**
     * Register wrapper
     */
    public static function register()
    {
        @stream_wrapper_unregister("zip");
        @stream_wrapper_register("zip", __CLASS__);
    }
    
    /**
     * Open stream
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // Check for mode
        if ($mode{0} != 'r')
            throw new Exception('Mode ' . $mode . ' is not supported. Only read mode is supported.');
        
        // Parse URL
        $url = @parse_url($path);
        
        // Fix URL
        if (!is_array($url))
        {
            $url['host'] = substr($path, strlen('zip://'));
            $url['path'] = '';
        }
        if (strpos($url['host'], '#') !== false)
        {
            if (!isset($url['fragment']))
            {
                $url['fragment'] = substr($url['host'], strpos($url['host'], '#') + 1) . $url['path'];
                $url['host']     = substr($url['host'], 0, strpos($url['host'], '#'));
                unset($url['path']);
            }
        }
        else
        {
            $url['host'] = $url['host'] . $url['path'];
            unset($url['path']);
        }
        
        // Open archive
        $this->_archive = new ZipArchive();
        $this->_archive->open($url['host']);
        
        $this->_fileNameInArchive = $url['fragment'];
        $this->_position          = 0;
        $this->_data              = $this->_archive->getFromName($this->_fileNameInArchive);
        
        return true;
    }
    
    /**
     * Stat stream
     */
    public function stream_stat()
    {
        return $this->_archive->statName($this->_fileNameInArchive);
    }
    
    /**
     * Read stream
     */
    function stream_read($count)
    {
        $ret = substr($this->_data, $this->_position, $count);
        $this->_position += strlen($ret);
        return $ret;
    }
    
    /**
     * Tell stream
     */
    public function stream_tell()
    {
        return $this->_position;
    }
    
    /**
     * EOF stream
     */
    public function stream_eof()
    {
        return $this->_position >= strlen($this->_data);
    }
    
    /**
     * Seek stream
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence)
        {
            case SEEK_SET:
                if ($offset < strlen($this->_data) && $offset >= 0)
                {
                    $this->_position = $offset;
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            
            case SEEK_CUR:
                if ($offset >= 0)
                {
                    $this->_position += $offset;
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            
            case SEEK_END:
                if (strlen($this->_data) + $offset >= 0)
                {
                    $this->_position = strlen($this->_data) + $offset;
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            
            default:
                return false;
        }
    }
}
?>
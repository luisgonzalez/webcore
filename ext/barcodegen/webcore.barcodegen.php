<?php
/**
 * Represents a barcode generator using code 128 standard
 *
 * @package WebCore
 * @subpackage BarCodeGen
 * @author Luis Gonzalez <luis.gonzalez@unosquare.com>
 */
class BarCodeGenerator extends ObjectBase
{
    const DEFAULT_BAR_WEIGHT = 1;
    const DEFAULT_BARCODE_HEIGHT = 20;
    const QUIETZONE_WIDTH = 10;
    const VERTICAL_PADDING = 5;
    
    const IMAGE_TYPE_GIF = 'gif';
    const IMAGE_TYPE_PNG = 'png';
    const IMAGE_TYPE_JPG = 'jpg';
    
    const TYPE_A = 'type_a';
    const TYPE_B = 'type_b';
    const TYPE_C = 'type_c';
    
    const FNC_1 = 'fnc_1';
    const FNC_2 = 'fnc_2';
    const FNC_3 = 'fnc_3';
    const FNC_4 = 'fnc_4';
    
    const SHIFT_A = 'shift_a';
    const SHIFT_B = 'shift_b';
    
    const CODE_A = 'code_a';
    const CODE_C = 'code_c';
    const CODE_B = 'code_b';
    
    const START_A = 103;
    const START_B = 104;
    const START_C = 105;
    const STOP = 106;
    
    /**
     * @var array . This is the binary representation of the numeric values associated to the input chars
     */
    private static $valueConvertion = array('11011001100','11001101100','11001100110','10010011000','10010001100','10001001100','10011001000','10011000100','10001100100','11001001000','11001000100','11000100100','10110011100','10011011100','10011001110','10111001100','10011101100','10011100110','11001110010','11001011100','11001001110','11011100100','11001110100','11101101110','11101001100','11100101100','11100100110','11101100100','11100110100','11100110010','11011011000','11011000110','11000110110','10100011000','10001011000','10001000110','10110001000','10001101000','10001100010','11010001000','11000101000','11000100010','10110111000','10110001110','10001101110','10111011000','10111000110','10001110110','11101110110','11010001110','11000101110','11011101000','11011100010','11011101110','11101011000','11101000110','11100010110','11101101000','11101100010','11100011010','11101111010','11001000010','11110001010','10100110000','10100001100','10010110000','10010000110','10000101100','10000100110','10110010000','10110000100','10011010000','10011000010','10000110100','10000110010','11000010010','11001010000','11110111010','11000010100','10001111010','10100111100','10010111100','10010011110','10111100100','10011110100','10011110010','11110100100','11110010100','11110010010','11011011110','11011110110','11110110110','10101111000','10100011110','10001011110','10111101000','10111100010','11110101000','11110100010','10111011110','10111101110','11101011110','11110101110','11010000100','11010010000','11010011100','1100011101011');
    
    /**
     * @var array. The mapping of values to the numeric representation according to the code type
     */
    private static $valueMapping = array(
        self::TYPE_A => array(' ','!','"','#','$','%','&',"'",'(',')','*','+',',','-','.','/','0','1','2','3','4','5','6','7','8','9',':',';','<','=','>','?','@','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','[',"\\",']','^','_',"\x0","\x1","\x2","\x3","\x4","\x5","\x6","\x7","\x8","\xA","\xB","\xC","\xD","\xE","\xF","\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17","\x18","\x1A","\x1B","\x1C","\x1D","\x1E","\x1F",self::FNC_3, self::FNC_2, self::SHIFT_B, self::CODE_C, self::CODE_B, self::FNC_4,self::FNC_1),
        self::TYPE_B => array(' ','!','"','#','$','%','&',"'",'(',')','*','+',',','-','.','/','0','1','2','3','4','5','6','7','8','9',':',';','<','=','>','?','@','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','[',"\\",']','^','_','`','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','{','|','}','~',"\x7F",self::FNC_3, self::FNC_2, self::SHIFT_A, self::CODE_C, self::FNC_4, self::CODE_A,self::FNC_1),
        self::TYPE_C => array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59','60','61','62','63','64','65','66','67','68','69','70','71','72','73','74','75','76','77','78','79','80','81','82','83','84','85','86','87','88','89','90','91','92','93','94','95','96','97','98','99', self::CODE_B, self::CODE_A, self::FNC_1)
    );

    /**
     * @var string;
     */
    private $value;
    
    /**
     * @var bool;
     */
    private $ignoreInvalidChars;

    /**
     * Creates a new instance of this class
     *
     * If ignoreInvalidChars flag is set to false, an Exception will be thrown if an invalid char is found,
     * otherwise, the invalid chars will be ignored
     *
     * @param string $value. The string to generate the barcode
     * @param bool $ignoreInvalidChars. If an invalid chare is found, it is ignored. If 
     */
    public function __construct($value, $ignoreInvalidChars = true)
    {
        $this->value = $value;
        $this->throwExceptionIfInvalid = $ignoreInvalidChars;
    }
    
    /**
     * Get the checsum for the array values
     *
     * @param array $values
     *
     * @return int
     */
    private static function getCheckSum($values)
    {
        $totalValues = count($values);
        $checkSum = $values[0];
        
        for ($i = 1; $i < $totalValues; $i++)
            $checkSum += $values[$i] * $i;
        
        return $checkSum % 103;
    }
    
    /**
     * Converst the numeric values to a binary representation of the bars
     *
     * @param array $numericValues
     *
     * @return string
     */
    private function getBinaryStringFromNumericValueArray($numericValues)
    {
        $result = "";
        foreach($numericValues as $value)
            $result .= self::$valueConvertion[$value];
        
        return $result;
    }
    
    /**
     * Gets the binary string for a Type C Code
     *
     * @return array
     */
    private function getCodeC()
    {
        $numericValues = array();
        $numericValues[] = self::START_C;

        $valueLength = strlen($this->value);

        for($i = 0; $i < $valueLength - 2; $i += 2)
            $numericValues[] = array_search($this->value[$i] . $this->value[$i + 1], self::$valueMapping[self::TYPE_C], true);
        
        $lastValue = substr($this->value, $i, 2);
        if (strlen($lastValue) == 2)
            $numericValues[] = array_search($this->value[$i] . $this->value[$i + 1], self::$valueMapping[self::TYPE_C], true);
        else
        {
            $numericValues[] = array_search(self::CODE_B, self::$valueMapping[self::TYPE_C], true);
            $numericValues[] = array_search($this->value[$i], self::$valueMapping[self::TYPE_B], true);
        }
        
        $numericValues[] = self::getCheckSum($numericValues);
        $numericValues[] = self::STOP;
        
        return self::getBinaryStringFromNumericValueArray($numericValues);    
    }
    
    /**
     * Gets the binary string for a Type B Code
     *
     * @return array
     */
    private function getCodeB()
    {
        $numericValues = array();
        $numericValues[] = self::START_B;

        $valueLength = strlen($this->value);

        for($i = 0; $i < $valueLength; $i++)
        {
            $index = array_search($this->value[$i], self::$valueMapping[self::TYPE_B], true);
            if ($index !== false)
                $numericValues[] = $index;
            else if ($this->ignoreInvalidChars == false)
                throw new Exception("Invalid character '" . $this->value[$i] . "' when creating barcode");
        }
        
        $numericValues[] = self::getCheckSum($numericValues);
        $numericValues[] = self::STOP;
        
        return self::getBinaryStringFromNumericValueArray($numericValues);
    }
    
    /**
     * Returns the most efficient code type. TYPE_C for numeric-only strings
     *
     * @return string
     */
    private function getBestCodeType()
    {
        $valueLength = strlen($this->value);
        $adyacentNumericChars = 0;
        
        for ($i = 0; $i < $valueLength; $i++)
        {
            if (is_numeric($this->value[$i]) == false)
                return self::TYPE_B;
        }
        
        if ($valueLength < 4)
            return self::TYPE_B;
        else
            return self::TYPE_C;
    }
    
    /**
     * Gets the Binary string representing the bar code
     *
     * @return string
     */
    private function getBinaryCode()
    {
        if ($this->getBestCodeType() == self::TYPE_C)
            return $this->getCodeC();
        
        return $this->getCodeB();
    }
    
    /**
     * Returns the barcode as floating html divs
     *
     * @param int barWeight. The width of a size 1 bar
     * @param int height. The height for the barcode
     * 
     * @return string
     */
    public function getBarCodeAsHtml($barWeight = 1, $height = 20)
    {
        $quietZone = self::QUIETZONE_WIDTH * $barWeight;
        $height = max($quietZone, $height);
        $str = $this->getBinaryCode();
        $strLen = strlen($str);
        
        $returnValue = "<style>div.barcode-container{padding:5px {$quietZone}px 5px {$quietZone}px;height:{$height}px;background-color:white;}div.barcode-bar{float:left;height:{$height}px;width:{$barWeight}px;}div.barcode-bar-black{float:left;height:{$height}px;width:{$barWeight}px;background-color:#000000}div.barcode-bar-white{float:left;height:{$height}px;width:{$barWeight}px;background-color:#FFFFFF}</style>";
        $returnValue .= "<div class='barcode-container'>";
        for ($i = 0; $i < $strLen; $i++)
            $returnValue .= "<div class='barcode-bar-".($str[$i] == 1 ? 'black':'white')."'></div>";
        $returnValue .= "</div>";
        
        return $returnValue;
    }
    
    /**
     * Generates an image with the barcode in the specified format
     * and sends it to the browser standard output
     *
     * @param string type. One of the IMAGE_TYPE_* prefixed constants
     * @param int barWeight. The width of a size 1 bar
     * @param int height. The height of the barcode, not the image
     */
    public function getBarCodeAsImage($type, $barWeight = 1, $height = 30)
    {
        $quietZone = self::QUIETZONE_WIDTH * $barWeight;
        $height = max($quietZone, $height);
        $str = $this->getBinaryCode();
        $strLen = strlen($str);
        
        $imageWidth = $quietZone * 2 + $strLen * $barWeight;
        $imageHeight = $height + 10;
        
        $image = imagecreate($imageWidth, $imageHeight);
        $whiteColor = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
        $blackColor = imagecolorallocate($image, 0x00, 0x00, 0x00);
        
        imagefill($image, 0, 0, $whiteColor);
        
        for ($i = 0; $i < $strLen; $i++)
        {
            if ($str[$i] == '0') continue;
            
            $startX = $quietZone + ($i * $barWeight);
            $endX = $startX + $barWeight - 1;
            imagefilledrectangle($image, $startX, 5, $endX, $height + 5, $blackColor);
        }
        
        switch($type)
        {
            case self::IMAGE_TYPE_PNG:
                header('Content-Type: image/png');
                imagepng($image);
                break;
            case self::IMAGE_TYPE_GIF:
                header('Content-Type: image/gif');
                imagegif($image);
                break;
            default:
                header('Content-Type: image/jpeg');
                imagejpeg($image);
                break;
        }
        
        imagedestroy($image);
        exit;
    }
}

class BarCodeGeneratorHttpHandler extends ObjectBase implements IHttpHandler
{
    const REQUEST_KEY_BARCODE_VALUE = "_barcode_value";
    const REQUEST_KEY_IMG_TYPE = "_barcode_img_type";
    const REQUEST_KEY_BAR_WEIGHT = "_barcode_bar_weight";
    const REQUEST_KEY_BARCODE_HEIGHT = "_barcode_height";
    
    const DEFAULT_HEIGHT = 20;
    const DEFAULT_BAR_WEIGHT = 1;
    
    public function handleRequest()
    {
        $queryStringVars = HttpRequest::getInstance()->getQueryStringVars();
        
        if ($queryStringVars->keyExists(self::REQUEST_KEY_BARCODE_VALUE))
        {
            $value = $queryStringVars->getValue(self::REQUEST_KEY_BARCODE_VALUE);
            $type = BarCodeGenerator::IMAGE_TYPE_PNG;
            $barWeight = 1;
            $height = 20;
            
            if ($queryStringVars->keyExists(self::REQUEST_KEY_IMG_TYPE))
                $type = $queryStringVars->getValue(self::REQUEST_KEY_IMG_TYPE);
            
            if ($queryStringVars->keyExists(self::REQUEST_KEY_BAR_WEIGHT))
                $barWeight = $queryStringVars->getValue(self::REQUEST_KEY_BAR_WEIGHT);

            if ($queryStringVars->keyExists(self::REQUEST_KEY_BARCODE_HEIGHT))
                $height = $queryStringVars->getValue(self::REQUEST_KEY_BARCODE_HEIGHT);

            $barcode = new BarCodeGenerator($value);
            $barcode->getBarCodeAsImage($type, $barWeight, $height);
        }
    }
}
?>
<?php
/**
 * @package WebCore
 * @subpackage Model
 * @version 1.0
 * 
 * Provides model validator classes
 *
 * @author Mario Di Vece <mario@unosquare.com>
 * @author Geovanni Perez <geovanni.perez@unosquare.com>
 * @copyright Copyright (c) 2008-2009, Author(s) above
 */

require_once "webcore.model.php";

/**
 * Provides a base implementation on which validators extend.
 *
 * @package WebCore
 * @subpackage Model
 */
abstract class FieldValidatorBase extends SerializableObjectBase implements IValidator
{
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if (is_object($sender) === false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sender');
        
        if (ObjectIntrospector::isA($sender, 'FieldModelBase') == false && ObjectIntrospector::isA($sender, 'DataRepeaterFieldModelBase') == false)
            throw new SystemException(SystemException::EX_INVALIDPARAMETER, 'Parameter = sender');
        
        return true;
    }
}

/**
 * Provides a basic implementation for a field validator.
 * The implementation takes into account the isReadOnly and isRequired fields only
 * Further validation might be needed in a derived class.
 *
 * @package WebCore
 * @subpackage Model
 */
class BasicFieldValidator extends FieldValidatorBase
{
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        parent::validate($sender, $eventArgs);
        
        if ($sender->getIsRequired() === true && $sender->getIsReadOnly() === false)
        {
            if (is_string($sender->getValue()) && trim($sender->getValue()) === '')
            {
                $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_REQUIRED));
                return false;
            }
        }
        
        $sender->setValue(trim($sender->getValue()));
        $sender->setErrorMessage('');
        return true;
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return BasicFieldValidator
     */
    public static function createInstance()
    {
        return new BasicFieldValidator();
    }
}

/**
 * Provides a basic implementation for an regex field validator.
 * The implementation takes into account the isReadOnly and isRequired fields only
 * Further validation might be needed in a derived class.
 *
 * @package WebCore
 * @subpackage Model
 */
class RegExFieldValidator extends BasicFieldValidator
{
    private $regexExpression;
    private $errorMessage;
    
    /**
     * Creates a new instance of this class
     *
     * @param string $regexExpression
     * @param string $errorMessage
     */
    public function __construct($regexExpression, $errorMessage = '')
    {
        $this->regexExpression = $regexExpression;
        
        if ($errorMessage == '')
            $this->errorMessage = Resources::getValue(Resources::SRK_FIELD_REQUIRED);
        else
            $this->errorMessage = $errorMessage;
    }
    
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if (!parent::validate($sender, $eventArgs))
            return false;
        
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        $sender->setValue(trim($sender->getValue()));
        
        if ($sender->getValue() == '' && !$sender->getIsRequired())
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        $valid = preg_match($this->regexExpression, $sender->getValue());
        
        if (!$valid)
        {
            $sender->setErrorMessage($this->errorMessage);
            return false;
        }
        
        $sender->setErrorMessage('');
        return true;
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return RegExFieldValidator
     */
    public static function createInstance()
    {
        return new RegExFieldValidator();
    }
}

/**
 * Provides a basic implementation for an email field validator.
 *
 * @package WebCore
 * @subpackage Model
 */
class EmailFieldValidator extends RegExFieldValidator
{
    protected $checkDnsRecrods;
    
    /**
     * Creates a new instance of this class
     *
     */
    public function __construct()
    {
        $this->checkDnsRecrods = false;
        parent::__construct(MailIdentity::EMAIL_FORMAT_REGEX, Resources::getValue(Resources::SRK_FIELD_BADEMAIL));
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return BasicFieldValidator
     */
    public static function createInstance()
    {
        return new EmailFieldValidator();
    }
    
    /**
     * Determines whether the validator checks for a valid email in the DNS records.
     * @return bool
     */
    public function getCheckDnsRecords()
    {
        return $this->checkDnsRecrods;
    }
    
    /**
     * Determines whether the validator checks for a valid email in the DNS records.
     * @param bool $value
     */
    public function setCheckDnsRecords($value)
    {
        $this->checkDnsRecrods = $value;
    }
    
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        $result = parent::validate($sender, $eventArgs);
        if ($result === true && $this->checkDnsRecrods === true)
        {
            list($username, $domain) = split('@', $email);
            $result = (checkdnsrr($domain, 'MX') != false);
            if (!$result)
            {
                $sender->setErrorMessage($this->errorMessage);
            }
        }
        return $result;
    }
}

/**
 * Provides a basic implementation for a Url field validator.
 * @package WebCore
 * @subpackage Model
 */
class UrlFieldValidator extends RegExFieldValidator
{
    /**
     * Creates a new instance of this class
     *
     */
    public function __construct()
    {
        parent::__construct("(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)", "Invalid web site URL");
    }
}

/**
 * Provides a basic implementation for an email field validator.
 * The implementation takes into account the isReadOnly and isRequired fields only
 * Further validation might be needed in a derived class.
 *
 * @package WebCore
 * @subpackage Model
 */
class DateFieldValidator extends BasicFieldValidator
{
    /**
     * Validates a DateField
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        if (!parent::validate($sender, $eventArgs))
            return false;
        
        $valueString = $sender->getValue();
        $parseResult = date_parse($valueString);
        if ($parseResult !== false)
        {
            if (checkdate($parseResult['month'], $parseResult['day'], $parseResult['year']))
            {
                $sender->setErrorMessage('');
                return true;
            }
        }
        
        $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_BADDATE));
        return false;
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return DateFieldValidator
     */
    public static function createInstance()
    {
        return new DateFieldValidator();
    }
}
/**
 * Provides a basic implementation for a phone number field validator.
 * The implementation takes into account the isReadOnly and isRequired fields only
 * Further validation might be needed in a derived class.
 *
 * @package WebCore
 * @subpackage Model
 */
class PhoneNumberFieldValidator extends BasicFieldValidator
{
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        if (!parent::validate($sender, $eventArgs))
            return false;
        
        $sender->setValue(trim($sender->getValue()));
        if ($sender->getValue() == '' && !$sender->getIsRequired())
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        $valueString = $sender->getValue();
        $valueString = str_replace(' ', '', $valueString);
        $valueString = str_replace(',', '', $valueString);
        $valueString = str_replace('+', '', $valueString);
        $valueString = str_replace('-', '', $valueString);
        $valueString = str_replace('(', '', $valueString);
        $valueString = str_replace(')', '', $valueString);
        $valueString = trim($valueString);
        
        if (!preg_match("([0-9\#\*]+)", $valueString))
        {
            $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_BADPHONENUMBER));
            return false;
        }
        
        $sender->setValue($valueString);
        $sender->setErrorMessage('');
        return true;
    }
    
    
    /**
     * Returns a default instance of this class.
     *
     * @return PhoneNumberFieldValidator
     */
    public static function createInstance()
    {
        return new PhoneNumberFieldValidator();
    }
}

/**
 * Provides an implemention of a Numeric validator
 *
 * @package WebCore
 * @subpackage Model
 */
class NumericFieldValidator extends BasicFieldValidator
{
    protected $minimumValue;
    protected $maximumValue;
    protected $allowDecimals;
    protected $isMoney;
    
    public function __construct()
    {
        $this->minimumValue  = 0;
        $this->maximumValue  = false;
        $this->allowDecimals = false;
        $this->isMoney       = false;
    }
    
    /**
     * Determines the minimum value allowed. If false, the validation is ignored.
     * @return mixed;
     */
    public function getMinimumValue()
    {
        return $this->minimumValue;
    }
    /**
     * Determines the minimum value allowed. If false, the validation is ignored.
     * @param mixed $value;
     */
    public function setMinimumValue($value)
    {
        $this->minimumValue = $value;
    }
    /**
     * Determines the maximum value allowed. If false, the validation is ignored.
     * @return mixed;
     */
    public function getMaximumValue()
    {
        return $this->maximumValue;
    }
    /**
     * Determines the maximum value allowed. If false, the validation is ignored.
     * @param mixed $value;
     */
    public function setMaximumValue($value)
    {
        $this->maximumValue = $value;
    }
    /**
     * Determines whether decimals are allowed.
     * @return bool;
     */
    public function getAllowDecimals()
    {
        return $this->allowDecimals;
    }
    /**
     * Determines whether decimals are allowed.
     * @param bool $value;
     */
    public function setAllowDecimals($value)
    {
        $this->allowDecimals = $value;
    }
    /**
     * Determines whether values must be treated as money
     * @return bool;
     */
    public function getIsMoney()
    {
        return $this->isMoney;
    }
    /**
     * Determines whether values must be treated as money
     * @param bool $value;
     */
    public function setIsMoney($value)
    {
        $this->isMoney = $value;
    }
    
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        if (!parent::validate($sender, $eventArgs))
            return false;
        
        $valueString = $sender->getValue();
        $valueString = str_replace(' ', '', $valueString);
        $valueString = str_replace(',', '', $valueString);
        if ($this->isMoney)
            $valueString = str_replace('$', '', $valueString);
        $valueString = trim($valueString);
        
        if ($valueString == '' && !$sender->getIsRequired())
        {
            $sender->setValue($valueString); // normalize
            $sender->setErrorMessage('');
            return true;
        }
        
        if (!is_numeric($valueString))
        {
            $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_BADNUMBER));
            return false;
        }
        
        $numericValue = floatval($valueString);
        if ($this->isMoney)
            $numericValue = round($numericValue, 2);
        $sender->setValue($numericValue);
        
        if ($this->maximumValue !== false && $numericValue > $this->maximumValue)
        {
            $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_MAXIMUM) . $this->maximumValue);
            return false;
        }
        
        if ($this->minimumValue !== false && $numericValue < $this->minimumValue)
        {
            $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_MINIMUM) . $this->minimumValue);
            return false;
        }
        
        $valueParts = explode('.', $valueString);
        
        if (!$this->allowDecimals && count($valueParts) > 1 && intval($valueParts[1]) !== 0)
        {
            $sender->setErrorMessage(Resources::getValue(Resources::SRK_FIELD_NODECIMALS));
            return false;
        }
        
        $sender->setErrorMessage('');
        return true;
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return NumericFieldValidator
     */
    public static function createInstance()
    {
        return new NumericFieldValidator();
    }
}

/**
 * Provides a basic implementation for an credit card field validator.
 *
 * @package WebCore
 * @subpackage Model
 */
class CreditCardFieldValidator extends BasicFieldValidator
{
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FieldModelBase $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {        
        if ($sender->getIsReadOnly() === true)
        {
            $sender->setErrorMessage('');
            return true;
        }
        
        if (!parent::validate($sender, $eventArgs))
            return false;
        
        $number = $sender->getValue();
        $number = ereg_replace('[^0-9]', '', $number);
        
        if ($number == '')
        {
            $sender->setErrorMessage("Credit Card is invalid");
            return false;
        }
        
        $numberLeft   = substr($number, 0, 4);
        $numberRight  = substr($number, -4);
        $numberLength = strlen($number);
        
        if (($numberLeft >= 3400) && ($numberLeft <= 3499))
        {
            $type         = 'American Express';
            $shouldLength = 15;
        }
        elseif (($numberLeft >= 3700) && ($numberLeft <= 3799))
        {
            $type         = 'American Express';
            $shouldLength = 15;
        }
        elseif (($numberLeft >= 4000) && ($numberLeft <= 4999))
        {
            $type = 'Visa';
            if ($NumberLength > 14)
            {
                $shouldLength = 16;
            }
            elseif ($NumberLength < 14)
            {
                $shouldLength = 13;
            }
            else
            {
                $sender->setErrorMessage("Credit Card is invalid.");
                return false;
            }
        }
        elseif (($numberLeft >= 5100) && ($numberLeft <= 5599))
        {
            $type         = 'MasterCard';
            $shouldLength = 16;
        }
        else
        {
            $sender->setErrorMessage("Credit Card is invalid");
            return false;
        }
        
        if ($numberLength <> $shouldLength)
        {
            $sender->setErrorMessage("Credit Card is invalid");
            return false;
        }
        
        $sender->setErrorMessage('');
        $sender->setType($type);
        
        return true;
    }
}

/**
 * Provides a basic implementation for an file upload field.
 * This validator check if file extension is valid too.
 *
 * @package WebCore
 * @subpackage Model
 */
class FileFieldValidator extends FieldValidatorBase
{
    /**
     * Validates the field taking into account its iRequired and isReadOnly properties.
     *
     * @param FileField $sender
     * @param mixed $eventArgs
     * @return bool
     */
    public function validate(&$sender, $eventArgs)
    {
        if (ObjectIntrospector::isA($sender->getPostedFileValue()->getValue('data'), 'PostedFile'))
        {

            $postedFile = $sender->getPostedFileValue()->getValue('data');
            
            if ($postedFile->getErrorCode() != UPLOAD_ERR_OK)
            {
                $sender->setErrorMessage($postedFile->getErrorMessage());
                return false;
            }
        
            $extensions = $sender->getAllowedExtensions();
            
            if (count($extensions) > 0 && in_array($postedFile->getFileExtension(), $extensions) === false)
            {
                $sender->setErrorMessage(Resources::getValue(Resources::SRK_UPLOAD_ERR_EXTENSION));
                return false;
            }
        }
        
        $sender->setErrorMessage('');
        
        return true;
    }
    
    /**
     * Returns a default instance of this class.
     *
     * @return FileFieldValidator
     */
    public static function createInstance()
    {
        return new FileFieldValidator();
    }
}
?>
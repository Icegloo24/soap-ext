<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;

class BaseType extends AbstractType
{
    
    public static $DATA_TYPES = [
        'boolean', 'bool', 'integer', 'int', 'decimal', 'string', 'duration', 'dateTime', 'date', 'time', 
        'anyType', 'anySimpleType', 'gYearMonth', 'gYear', 'gMonthDay', 'gDay', 'Month', 
        'base64Binary', 'hexBinary', 'anyURI', 'normalizedString', 'token', 'NMTOKEN', 
        'Name', 'language', 'NCName', 'ENTITY', 'IDREF', 'ID', 'NMTokens', 'Entities', 'IDREFS'
    ];
    
    private $type;
    
    public function __construct(string $type)
    {
        $this->type = $type;
    }
    
    public function link(Wsdl $wsdl)
    {}

    public function validate(DOMElement $element, ValidatorInterface $validator): bool
    {
        switch($this->type) {
            case 'string':
                return $this->is_string($element->nodeValue);
            case 'decimal':
                return $this->is_decimal($element->nodeValue);
            case 'int':
            case 'integer':
                return $this->is_integer($element->nodeValue);
            case 'bool':
            case 'boolean':
                return $this->is_boolean($element->nodeValue);
            default:
                return true;
        }
    }
    
    private function is_string($value)
    {
        return is_string($value) && $value != null;
    }
    
    private function is_decimal($value)
    {
        return is_numeric($value);
    }
    
    private function is_integer($value)
    {
        if(is_int($value)) {
            return true;
        }
        if(!is_string($value)) {
            return false;
        }
        if(ctype_digit($value)) {
            return true;
        }
        return false;
    }
    
    private function is_boolean($value)
    {
        return $value === 'false' || $value === 'true' || $value === '0' || $value === '1';
    }

}


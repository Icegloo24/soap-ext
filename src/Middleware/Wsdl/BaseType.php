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
        'base64Binary', 'hexBinary', 'anyUri', 'normalizedString', 'token', 'NMTOKEN', 
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
                if(count($element->nodeValue) > 0 && is_string($element->nodeValue)) {
                    return true;
                }return false;
            case 'decimal':
                if(count($element->nodeValue) > 0 && is_numeric($element->nodeValue)) {
                    return true;
                }return false;
            case 'int':
            case 'integer':
                if(count($element->nodeValue) > 0 && is_int($element->nodeValue)) {
                    return true;
                }return false;
            case 'bool':
            case 'boolean':
                if(count($element->nodeValue) > 0 && is_bool($element->nodeValue)) {
                    return true;
                }return false;
            case 'boolean':
                if(count($element->nodeValue) > 0 && is_bool($element->nodeValue)) {
                    return true;
                }return false;
            default:
                return true;
        }
    }

}


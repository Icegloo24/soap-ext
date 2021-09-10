<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;

class BaseType extends AbstractType
{
    
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
                }
                return false;
            default:
                return false;
        }
    }

}


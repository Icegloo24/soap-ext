<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;

abstract class AbstractType
{
    
    protected $ns;
    protected $name;
    protected $abstract = false;
    
    public function __construct(DOMElement $complex, $ns)
    {
        $this->ns = $ns;
        foreach($complex->attributes as $attr) {
            switch($attr->name) {
                case 'name':
                    $this->name = $attr->value;
                case 'abstract':
                    $this->abstract = $attr->value=='true'?true:false;
                default:
            }
        }
    }
    
    
    public function getName()
    {
        return $this->name;
    }
    public function getNamespace()
    {
        return $this->ns;
    }
    
    public abstract function validate(DOMElement $element, ValidatorInterface $validator): bool;
    
    public abstract function link(Wsdl $wsdl);
    
}


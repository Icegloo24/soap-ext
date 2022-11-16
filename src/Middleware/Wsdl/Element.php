<?php
namespace SoapExt\Middleware\Wsdl;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use DOMElement;
use SoapExt\Middleware\Tools\TypeExtracter;

class Element extends AbstractType
{
    use TypeExtracter;
    
    private $complexType;
    
    public function __construct(DOMElement $complex, $ns)
    {
        parent::__construct($complex, $ns);
        $this->complexType = ['type_ns'=>$this->extractTypeNs($complex), 'type'=>$this->extractTypeNs($complex)];
    }
    
    
    public function link(Wsdl $wsdl)
    {
        $wsdl->appendAccessor($this->getNamespace(), $this->getName(), $this->complexType['type_ns'], $this->complexType['type']);
        $this->complexType = $wsdl->getType($this->complexType['type_ns'], $this->complexType['type']);
    }
    
    public function validate(DOMElement $element, ValidatorInterface $validator): bool
    {
        return $this->complexType->validate($element, $validator);
    }
    
}


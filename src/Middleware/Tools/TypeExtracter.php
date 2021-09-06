<?php
namespace SoapExt\Middleware\Tools;

use DOMAttr;
use DOMElement;

trait TypeExtracter
{
    
    protected function getType(DOMElement $element)
    {
        foreach($element->attributes as $attr) {
            /**@var DOMAttr $attr */
            if($attr->name == 'type') {
                return strpos($attr->nodeValue, ':') != false?explode(':', $attr->nodeValue)[1]:$attr->nodeValue;
            }
        }
    }
    
    protected function getTypeNs(DOMElement $element)
    {
        foreach($element->attributes as $attr) {
            /**@var DOMAttr $attr */
            if($attr->name == 'type') {
                return strpos($attr->nodeValue, ':') != false?$element->lookupNamespaceUri(explode(':', $attr->nodeValue)[0]):$element->lookupNamespaceUri($element->prefix);
            }
        }
    }
    
}


<?php
namespace SoapExt\Middleware\Tools;

use DOMAttr;
use DOMElement;

trait TypeExtracter
{
    
    protected function extractName(DOMElement $element)
    {
        return $element->localName;
    }
    
    protected function extractNs(DOMElement $element)
    {
        return $element->namespaceURI;
    }
    
    protected function extractType(DOMElement $element)
    {
        foreach($element->attributes as $attr) {
            /**@var DOMAttr $attr */
            if($attr->name == 'type') {
                return strpos($attr->nodeValue, ':') != false?explode(':', $attr->nodeValue)[1]:$attr->nodeValue;
            }
        }return null;
    }
    
    protected function extractTypeNs(DOMElement $element)
    {
        foreach($element->attributes as $attr) {
            /**@var DOMAttr $attr */
            if($attr->name == 'type') {
                return strpos($attr->nodeValue, ':') != false?$element->lookupNamespaceUri(explode(':', $attr->nodeValue)[0]):$element->lookupNamespaceUri($element->prefix);
            }
        }return null;
    }
    
}


<?php
namespace SoapExt\Middleware\Interfaces;

use SoapExt\Wsdl;

interface RequestBuilderInterface {
    
    public function buildRequest($arguments, $headers, $wsdlLoader = null): string;
    
}


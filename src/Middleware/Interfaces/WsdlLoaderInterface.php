<?php
namespace SoapExt\Middleware\Interfaces;

use SoapExt\Wsdl;

interface WsdlLoaderInterface {
    
    public function downloadWsdl($wsdl, CurlInterface $curl): Wsdl;
    
    public function loadWsdl($wsdl, CachingInterface $cache): Wsdl;
    
    public function cacheWsdl($wsdl, CachingInterface $cache);
    
    public function isCached($wsdl, CachingInterface $cache): bool;
    
}


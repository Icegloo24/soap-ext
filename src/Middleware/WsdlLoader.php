<?php
namespace SoapExt\Middleware;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\CachingInterface;
use SoapExt\Middleware\Interfaces\CurlInterface;
use SoapExt\Middleware\Interfaces\WsdlLoaderInterface;

class WsdlLoader implements WsdlLoaderInterface {
    
    private $wsdl;
    
    public function __construct() {
    }
    
    
    public function downloadWsdl($wsdl, CurlInterface $curl): Wsdl
    {
        if($curl == null) {
            throw new \SoapFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$wsdl'! Http-Error: No Curl Object available!");
        }
        if($curl->execute($wsdl)) {
            
            $content = $curl->getLastResponseBody();
            $this->wsdl = new Wsdl($content);
            
            foreach($this->wsdl->getNextToInclude() as $ns => $uri) {
                $this->downloadIncluded($ns, $uri, $curl);
            }
            return $this->wsdl;
        }else {
            throw new \SoapFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$wsdl'! ".$curl->getLastError().": ".$curl->getLastErrorMessage());
        }
    }
    
    
    private function downloadIncluded($namespace, $localisation, CurlInterface $curl)
    {
        if($curl->execute($localisation)) {
            
            $content = $curl->getLastResponseBody();
            $this->wsdl->appendNs($namespace, $content);
            
            foreach($this->wsdl->getNextToInclude() as $ns => $uri) {
                $this->downloadIncluded($ns, $uri, $curl);
            }
        }else {
            throw new \SoapFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$localisation'! Http-Error: ".$curl->getLastErrorMessage());
        }
    }
    
    
    public function loadWsdl($wsdl, CachingInterface $cache): Wsdl
    {
        if($cache != null && $this->wsdl == null) { 
            if($cache->hasFile($wsdl)) {
                $this->wsdl = new Wsdl($cache->getContent($wsdl));
                
                foreach($this->wsdl->getNextToInclude() as $ns => $uri) {
                    $this->loadIncluded($ns, $uri, $cache);
                }
            }else {
                throw new \SoapFault("WSDL", "SOAP-ERROR: Parsing WSDL: Couldn't load WSDL '$wsdl' from Cache as the file seems not to be cached.");
            }
        }
        return $this->wsdl;
    }
    
    
    private function loadIncluded($namespace, $localisation, CachingInterface $cache)
    {
        if($cache->hasFile($localisation)) {
            $this->wsdl->appendNs($namespace, $cache->getContent($localisation));
            
            foreach($this->wsdl->getNextToInclude() as $ns => $uri) {
                $this->loadIncluded($ns, $uri, $cache);
            }
        }else {
            throw new \SoapFault("WSDL", "SOAP-ERROR: Parsing WSDL: Couldn't load Schema '$localisation' from Cache as the file seems not to be cached.");
        }
    }
    
    
    public function isCached($wsdl, CachingInterface $cache): bool
    {
        return ($cache != null)?$cache->hasFile($wsdl):false;
    }
    
    
    public function cacheWsdl(CachingInterface $cache)
    {
        
    }

    
}


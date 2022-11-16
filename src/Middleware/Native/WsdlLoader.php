<?php
namespace SoapExt\Middleware\Native;

use SoapExt\Wsdl;
use SoapExt\Middleware\Interfaces\CachingInterface;
use SoapExt\Middleware\Interfaces\CurlInterface;
use SoapExt\Middleware\Interfaces\WsdlLoaderInterface;
use SoapExt\Exceptions\SoapExtFault;

class WsdlLoader implements WsdlLoaderInterface {
    
    private $wsdl;
    
    public function __construct() {
    }
    
    
    public function downloadWsdl($wsdl, CurlInterface $curl): Wsdl
    {
        if($curl == null) {
            throw new SoapExtFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$wsdl'! Http-Error: No Curl Object available!");
        }
        if($curl->execute($wsdl) && $curl->getLastResponseBody() != '') {
            
            $content = $curl->getLastResponseBody();
            $this->wsdl = new Wsdl($content);
            
            foreach($this->wsdl->getToIncludes() as $ns => $uri) {
                $this->downloadIncluded($ns, $curl->resolveUri($wsdl, $uri), $curl);
            }
            $this->wsdl->link();
            return $this->wsdl;
        }else {
            throw new SoapExtFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$wsdl'! ".$curl->getLastError().": ".$curl->getLastErrorMessage());
        }
    }
    
    
    private function downloadIncluded($namespace, $localisation, CurlInterface $curl)
    {
        if($curl->execute($localisation) && $curl->getLastResponseBody() != '') {
            
            $content = $curl->getLastResponseBody();
            
            foreach($this->wsdl->appendNs($namespace, $content) as $ns => $uri) {
                $this->downloadIncluded($ns, $curl->resolveUri($localisation, $uri), $curl);
            }
        }else {
            throw new SoapExtFault("HTTP", "SOAP-ERROR: Parsing WSDL: Couldn't load from '$localisation'! Http-Error: ".$curl->getLastErrorMessage());
        }
    }
    
    
    public function loadWsdl($wsdl, CachingInterface $cache): Wsdl
    {
        if($cache != null && $this->wsdl == null) {
            if($cache->hasFile($wsdl)) {
                $this->wsdl = new Wsdl($cache->getContent($wsdl));
                
                foreach($this->wsdl->getToIncludes() as $ns => $uri) {
                    $this->loadIncluded($ns, $uri, $cache);
                }
                $this->wsdl->link();
            }else {
                $cache->clearCache();
                throw new SoapExtFault("WSDL", "SOAP-ERROR: Parsing WSDL: Couldn't load WSDL '$wsdl' from Cache as the file seems not to be cached.");
            }
        }
        return $this->wsdl;
    }
    
    
    private function loadIncluded($namespace, $localisation, CachingInterface $cache)
    {
        if($cache->hasFile($localisation)) {
            foreach($this->wsdl->appendNs($namespace, $cache->getContent($localisation)) as $ns => $uri) {
                $this->loadIncluded($ns, $uri, $cache);
            }
        }else {
            $cache->clearCache();
            throw new SoapExtFault("WSDL", "SOAP-ERROR: Parsing WSDL: Couldn't load Schema '$localisation' from Cache as the file seems not to be cached.");
        }
    }
    
    
    public function isCached($wsdl, CachingInterface $cache): bool
    {
        return ($cache != null)?$cache->hasFile($wsdl):false;
    }
    
    
    public function cacheWsdl($wsdl, CachingInterface $cache)
    {
        try {
            $WSDL = $this->wsdl->getWsdl()->saveXML();
            $xsds = array();
            foreach($this->wsdl->getIncluded() as $ns1 => $dom) {
                foreach($this->wsdl->getNsMap() as $ns2 => $loc) {
                    if($ns1 == $ns2) {
                        $xsds[$loc] = $dom->saveXML();
                    }
                }
            }
            foreach($this->wsdl->getNsMap() as $ns => $loc) {
                $file = $cache->getFile($loc);
                $array = explode("\\", $file);
                $file = array_pop($array);
                $WSDL = str_replace($loc, $file, $WSDL);
                foreach($xsds as $filename => &$xsd) {
                    $xsd = str_replace($loc, $file, $xsd);
                }
            }
            $cache->putContent($WSDL, $wsdl);
            foreach($xsds as $filename => $xsd) {
                $cache->putContent($xsd, $filename);
            }
        }catch(\Exception $e) {
            $cache->clearCache();
            throw $e;
        }
    }
    
    
}


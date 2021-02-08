<?php
namespace SoapExt;

use SoapExt\Middleware\MiddlewareHandler;
use SoapExt\Middleware\Native\Curl;
use SoapExt\Middleware\Native\WsdlLoader;
use SoapExt\Middleware\Interfaces\CachingInterface;
use SoapExt\Middleware\Interfaces\CurlInterface;
use SoapExt\Middleware\Interfaces\RequestBuilderInterface;
use SoapExt\Middleware\Interfaces\WsdlLoaderInterface;
use SoapExt\Middleware\Native\Cache;
use SoapExt\Middleware\Native\RequestBuilder;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use SoapExt\Middleware\Interfaces\SignatureMakerInterface;
use SoapExt\Middleware\Interfaces\RequestAdjustmentInterface;
use SoapExt\Middleware\Native\RequestAdjustment;
use SoapExt\Exceptions\SoapExtFault;

final class SoapClient {
    
    use MiddlewareHandler;
    
    private $debug = false;
    
    private $wsdl;
    private $location;
    private $soap_version;
    
    private $lastRequest;
    private $lastHttpHeaders;
    private $soapHeaders;
    
    private $lastResponse;
    
    public final function __construct($wsdl, array $options = array(), $middleware = null) 
    {
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->location = isset($options['location'])?$options['location']:null;
        $this->soap_version = isset($options['soap_version'])?$options['soap_version']:SOAP_1_1;
        
        if(!is_array($middleware)) {
            $this->appendCurl(new Curl($options));
            //$this->appendCaching(new Cache());
            $this->appendWsdlLoader(new WsdlLoader());
            $this->appendRequestBuilder(new RequestBuilder());
            $this->appendRequestAdjustment(new RequestAdjustment());
        }else {
            foreach($middleware as $appendable) {
                if($appendable instanceof CurlInterface) {
                    $this->appendCurl($appendable);
                }
                if($appendable instanceof CachingInterface) {
                    $this->appendCaching($appendable);
                }
                if($appendable instanceof RequestBuilderInterface) {
                    $this->appendRequestBuilder($appendable);
                }
                if($appendable instanceof WsdlLoaderInterface) {
                    $this->appendWsdlLoader($appendable);
                }
                if($appendable instanceof ValidatorInterface) {
                    $this->appendValidator($appendable);
                }
                if($appendable instanceof SignatureMakerInterface) {
                    $this->appendSignatureMaker($appendable);
                }
                if($appendable instanceof RequestAdjustmentInterface) {
                    $this->appendRequestAdjustment($appendable);
                }
            }
        }
        
        if($this->wsdlLoader !== null) {
            if(isset($this->cache)) {
                if(!$this->wsdlLoader->isCached($wsdl, $this->cache)) {
                    $this->wsdl = $this->wsdlLoader->downloadWsdl($wsdl, $this->curl);
                    $this->wsdlLoader->cacheWsdl($wsdl, $this->cache);
                }else {
                    $this->wsdl = $this->wsdlLoader->loadWsdl($wsdl, $this->cache);
                }
            }else {
                $this->wsdl = $this->wsdlLoader->downloadWsdl($wsdl, $this->curl);
            }
        }
    }
    
    
    public final function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($request);
        $dom->formatOutput = true;
        
        $this->__setLastRequest($dom->saveXML());
        //Perform Adjustments
        if(isset($this->requestAdjustment)) {
            $this->__setLastRequest($this->requestAdjustment->modifyRequest($this->__getLastRequest()));
        }
        //Perform Validation
        if(isset($this->validator)) {
            $this->validator->validate($this->__getLastRequest(), $this->wsdlLoader);
        }
        //Perform Signing
        if(isset($this->signing)) {
            $this->__setLastRequest($this->signing->sign($this->__getLastRequest()));
        }
        //Preset Header
        $this->__setLastHttpHeaders(($version === SOAP_1_2)?
            'Content-Type: application/soap+xml; charset=utf-8; action="'.$action.'"':
            'Content-Type: text/xml; charset=utf-8; action="'.$action.'"');
        //DEBUG/NOCURL action
        if(!isset($this->curl) || $this->debug) {
            $this->__setLastResponse($this->__getLastRequest());
            return $this->__getLastResponse();
        }
        //Perform Curl
        if($this->curl->execute($location, $this->__getLastRequest(), [$this->__getLastHttpHeaders(), "soapaction: \"$action\""])) {
            $this->__setLastResponse($this->curl->getLastResponseBody());
            $this->__setLastHttpHeaders($this->curl->getLastResponseHeader());
        }else {
            throw new SoapExtFault($this->curl->getLastError(), $this->curl->getLastErrorMessage());
        }
        
        return $this->__getLastResponse();
    }
    
    
    public final function __call($function_name, $arguments)
    {
        if($this->requestBuilder != null) {
            $this->__setLastRequest($this->requestBuilder->buildRequest($arguments, $this->soapHeaders, $this->wsdl));
            return $this->__doRequest($this->__getLastRequest(), $this->__getLocation(), $this->__getOperations($function_name), $this->soap_version);
        }else {
            throw new SoapExtFault("SOAP", "SOAP-ERROR: Couldn't load the request properly. RequestBuilder is not defined.");
        }
    }
    
    
    public final function __getOperations($function_name = null)
    {
        if($this->wsdl != null && $this->wsdl instanceof Wsdl) {
            if($function_name === null) {
                return $this->wsdl->getOperations();
            }
            if(isset($this->wsdl->getOperations()[$function_name])) {
                return $this->wsdl->getOperations()[$function_name];
            }else {
                throw new SoapExtFault("WSDL", "SOAP-ERROR: Unable to call the SoapOperation $function_name. Operation does not exist!");
            }
        }
        if($function_name === null) {
            return [];
        }else {
            return $function_name;
        }
    }
    
    
    public final function __getLocation(): string
    {
        if(isset($this->location)) {
            return $this->location;
        }
        if(isset($this->wsdl)) {
            return $this->wsdl->getUri();
        }
        throw new SoapExtFault("HTTP", "SOAP-ERROR: Unable to obtain Location from WSDL: No WSDL is set: No Location set in Options!");
    }
    
    
    public final function __setLastRequest($value) {
        $this->lastRequest = $value;
    }
    public final function __setLastHttpHeaders($value) {
        $this->lastHttpHeaders = $value;
    }
    public final function __setLastResponse($value) {
        $this->lastResponse = $value;
    }
    public final function __setSoapHeaders($value = null) {
        $this->soapHeaders = $value;
    }
    
    
    public final function __getLastRequest() {
        return $this->lastRequest;
    }
    public final function __getLastHttpHeaders() {
        return $this->lastHttpHeaders;
    }
    public final function __getLastResponse() {
        return $this->lastResponse;
    }
    
}

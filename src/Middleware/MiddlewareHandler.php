<?php
namespace SoapExt\Middleware;

use SoapExt\Middleware\Interfaces\CachingInterface;
use SoapExt\Middleware\Interfaces\SignatureMakerInterface;
use SoapExt\Middleware\Interfaces\WsdlLoaderInterface;
use SoapExt\Middleware\Interfaces\CurlInterface;
use SoapExt\Middleware\Interfaces\RequestBuilderInterface;
use SoapExt\Middleware\Interfaces\ValidatorInterface;
use SoapExt\Middleware\Interfaces\RequestAdjustmentInterface;

trait MiddlewareHandler {
    
    protected $cache;
    protected $curl;
    protected $wsdlLoader;
    protected $requestBuilder;
    protected $validator;
    protected $signing;
    protected $requestAdjustment;
    
    public function appendCaching(CachingInterface $cache) {
        $this->cache = $cache;
    }
    
    public function appendCurl(CurlInterface $curl) {
        $this->curl = $curl;
    }
    
    public function appendWsdlLoader(WsdlLoaderInterface $wsdlLoader) {
        $this->wsdlLoader = $wsdlLoader;
    }
    
    public function appendRequestBuilder(RequestBuilderInterface $requestBuilder) {
        $this->requestBuilder = $requestBuilder;
    }
    
    public function appendValidator(ValidatorInterface $validator) {
        $this->validator = $validator;
    }
    
    public function appendSignatureMaker(SignatureMakerInterface $signing) {
        $this->signing = $signing;
    }
    
    public function appendRequestAdjustment(RequestAdjustmentInterface $requestAdjustment) {
        $this->requestAdjustment = $requestAdjustment;
    }
    
}

